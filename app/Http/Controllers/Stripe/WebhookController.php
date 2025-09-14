<?php

namespace App\Http\Controllers\Stripe;

use App\Http\Controllers\Controller;
use App\Mail\TipReceivedAdminMail;
use App\Mail\TipReceivedMail;
use App\Models\Profile;
use App\Models\StripeEvent;
use App\Models\Tip;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Str;
use Symfony\Component\HttpFoundation\Response;

class WebhookController extends Controller
{
    public function handle(Request $request): Response
    {
        Log::info('Stripe webhook: endpoint hit', [
            'ip' => $request->ip(),
            'length' => strlen((string) $request->getContent()),
        ]);
        $payload = $request->getContent();
        $sigHeader = $request->header('Stripe-Signature', '');
        $secret = config('services.stripe.webhook_secret');

        if (! $secret) {
            Log::warning('Stripe webhook secret not configured.');
            return response('Webhook secret not configured', 500);
        }

        try {
            $event = \Stripe\Webhook::constructEvent(
                $payload,
                $sigHeader,
                $secret
            );
        } catch (\UnexpectedValueException $e) {
            // Invalid payload
            Log::warning('Stripe webhook invalid payload', ['error' => $e->getMessage()]);
            return response('Invalid payload', 400);
        } catch (\Stripe\Exception\SignatureVerificationException $e) {
            // Invalid signature
            Log::warning('Stripe webhook signature verification failed', ['error' => $e->getMessage()]);
            return response('Invalid signature', 400);
        }

        $type = $event->type;
        $object = $event->data->object;

        // Persist the event for audit/debug
        $eventRecord = StripeEvent::query()->firstOrCreate(
            ['event_id' => $event->id],
            [
                'type' => (string) $type,
                'signature' => $sigHeader,
                'payload' => json_decode($payload, true) ?? [],
                'processing_status' => 'received',
            ]
        );

        Log::info('Stripe webhook received', [
            'event_id' => $event->id,
            'type' => $type,
        ]);

        try {
            switch ($type) {
                case 'checkout.session.completed':
                    $this->handleCheckoutCompleted($object, $event, $eventRecord);
                    break;
                case 'payment_intent.succeeded':
                    $this->handlePaymentIntentSucceeded($object, $event, $eventRecord);
                    break;
                default:
                    // Ignore other events in PoC
                    $eventRecord->processing_status = 'ignored';
                    $eventRecord->save();
                    break;
            }
        } catch (\Throwable $e) {
            Log::error('Stripe webhook handler error', ['error' => $e->getMessage()]);
            $eventRecord->processing_status = 'error';
            $eventRecord->error = $e->getMessage();
            $eventRecord->save();
            return response('error', 500);
        }

        return response('ok', 200);
    }

    protected function handleCheckoutCompleted(object $session, object $event, StripeEvent $eventRecord): void
    {
        $sessionArr = method_exists($session, 'toArray') ? $session->toArray() : (array) $session;

        $checkoutSessionId = Arr::get($sessionArr, 'id');
        $paymentIntentId = Arr::get($sessionArr, 'payment_intent');
        $amountCents = (int) Arr::get($sessionArr, 'amount_total');
        $currency = Str::lower((string) Arr::get($sessionArr, 'currency', 'eur'));
        $metadata = (array) Arr::get($sessionArr, 'metadata', []);

        Log::info('Parsed checkout.session.completed', compact('checkoutSessionId', 'paymentIntentId', 'amountCents', 'currency', 'metadata'));
        try {
            if (class_exists(\App\Models\StripeEvent::class)) {
                $eventRecord->checkout_session_id = $checkoutSessionId;
                $eventRecord->payment_intent_id = $paymentIntentId;
                $eventRecord->save();
            }
        } catch (\Throwable $e) {
            Log::warning('Failed updating StripeEvent record', ['error' => $e->getMessage()]);
        }

        $userId = $this->resolveUserIdFromMetadata($metadata);
        if (! $userId && isset($metadata['profile_id'])) {
            $profile = Profile::find($metadata['profile_id']);
            if ($profile) {
                $userId = $profile->user_id;
            }
        }

        if (! $userId) {
            Log::warning('Stripe webhook checkout.session.completed without user_id metadata');
            try {
                if (class_exists(\App\Models\StripeEvent::class)) {
                    $eventRecord->processing_status = 'skipped_no_user';
                    $eventRecord->save();
                }
            } catch (\Throwable $e) {
                Log::warning('Failed updating StripeEvent record', ['error' => $e->getMessage()]);
            }
            return;
        }

        if ($paymentIntentId) {
            $tip = Tip::firstOrNew(['payment_intent_id' => $paymentIntentId]);
        } else {
            $tip = Tip::firstOrNew(['checkout_session_id' => $checkoutSessionId]);
        }

        $tip->fill([
            'user_id' => $userId,
            'checkout_session_id' => $checkoutSessionId,
            'amount_cents' => $amountCents > 0 ? $amountCents : ($tip->amount_cents ?? 0),
            'currency' => $currency,
            'status' => 'succeeded',
            'paid_at' => now(),
        ]);

        // Merge metadata
        $existingMeta = is_array($tip->metadata) ? $tip->metadata : [];
        $tip->metadata = array_replace_recursive($existingMeta, [
            'session_metadata' => $metadata,
            'last_event' => [
                'id' => $event->id,
                'type' => $event->type,
            ],
        ]);

        $tip->save();
        Log::info('Tip saved from checkout.session.completed', ['tip_id' => $tip->id]);
        // Update event record
        try {
            if (class_exists(\App\Models\StripeEvent::class)) {
                $eventRecord->processing_status = 'processed';
                $eventRecord->processed_at = now();
                $eventRecord->tip_id = $tip->id;
                $eventRecord->save();
            }
        } catch (\Throwable $e) {
            Log::warning('Failed updating StripeEvent record', ['error' => $e->getMessage()]);
        }

        // Notify on first transition to succeeded
        if ($tip->wasChanged('status') && $tip->status === 'succeeded') {
            $this->notifyTip($tip);
        }
    }

    protected function handlePaymentIntentSucceeded(object $pi, object $event, StripeEvent $eventRecord): void
    {
        $piArr = $pi->toArray();

        $paymentIntentId = Arr::get($piArr, 'id');
        $amountCents = (int) (Arr::get($piArr, 'amount_received', Arr::get($piArr, 'amount', 0)));
        $currency = Str::lower((string) Arr::get($piArr, 'currency', 'eur'));
        $metadata = (array) Arr::get($piArr, 'metadata', []);
        Log::info('Parsed payment_intent.succeeded', compact('paymentIntentId', 'amountCents', 'currency', 'metadata'));
        // best-effort update of event record
        try {
            if (class_exists(\App\Models\StripeEvent::class)) {
                $eventRecord->payment_intent_id = $paymentIntentId;
                $eventRecord->save();
            }
        } catch (\Throwable $e) {
            Log::warning('Failed updating StripeEvent record', ['error' => $e->getMessage()]);
        }

        $userId = $this->resolveUserIdFromMetadata($metadata);
        if (! $userId && isset($metadata['profile_id'])) {
            $profile = Profile::find($metadata['profile_id']);
            if ($profile) {
                $userId = $profile->user_id;
            }
        }

        if (! $userId) {
            Log::warning('Stripe webhook payment_intent.succeeded without user_id metadata');
            try {
                if (class_exists(\App\Models\StripeEvent::class)) {
                    $eventRecord->processing_status = 'skipped_no_user';
                    $eventRecord->save();
                }
            } catch (\Throwable $e) {
                Log::warning('Failed updating StripeEvent record', ['error' => $e->getMessage()]);
            }
            return;
        }

        $tip = Tip::updateOrCreate(
            ['payment_intent_id' => $paymentIntentId],
            [
                'user_id' => $userId,
                'amount_cents' => $amountCents,
                'currency' => $currency,
                'status' => 'succeeded',
                'paid_at' => now(),
            ]
        );

        // Merge metadata
        $existingMeta = is_array($tip->metadata) ? $tip->metadata : [];
        $tip->metadata = array_replace_recursive($existingMeta, [
            'payment_intent_metadata' => $metadata,
            'last_event' => [
                'id' => $event->id,
                'type' => $event->type,
            ],
        ]);
        $tip->save();
        Log::info('Tip saved from payment_intent.succeeded', ['tip_id' => $tip->id]);
        try {
            if (class_exists(\App\Models\StripeEvent::class)) {
                $eventRecord->processing_status = 'processed';
                $eventRecord->processed_at = now();
                $eventRecord->tip_id = $tip->id;
                $eventRecord->save();
            }
        } catch (\Throwable $e) {
            Log::warning('Failed updating StripeEvent record', ['error' => $e->getMessage()]);
        }

        if ($tip->wasChanged('status') && $tip->status === 'succeeded') {
            $this->notifyTip($tip);
        }
    }

    protected function resolveUserIdFromMetadata(array $metadata): ?int
    {
        $userId = Arr::get($metadata, 'user_id');
        if ($userId !== null) {
            return (int) $userId;
        }
        return null;
    }

    protected function notifyTip(Tip $tip): void
    {
        try {
            // To waiter
            if ($tip->user && $tip->user->email) {
                Mail::to($tip->user->email)->send(new TipReceivedMail($tip));
            }
            // To admin(s)
            $adminEmails = User::query()->where('is_admin', true)->pluck('email')->filter()->all();
            if (! empty($adminEmails)) {
                Mail::to($adminEmails)->send(new TipReceivedAdminMail($tip));
            }
        } catch (\Throwable $e) {
            Log::error('Failed to send tip emails', ['error' => $e->getMessage()]);
        }
    }
}
