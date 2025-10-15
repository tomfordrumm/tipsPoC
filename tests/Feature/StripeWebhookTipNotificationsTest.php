<?php

namespace Tests\Feature;

use App\Http\Controllers\Stripe\WebhookController;
use App\Mail\TipReceivedAdminMail;
use App\Mail\TipReceivedMail;
use App\Models\StripeEvent;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Mail;
use ReflectionMethod;
use Tests\TestCase;

class StripeWebhookTipNotificationsTest extends TestCase
{
    use RefreshDatabase;

    public function test_payment_intent_succeeded_sends_tip_notifications_once(): void
    {
        Mail::fake();

        $user = User::factory()->create();
        $admin = User::factory()->create();
        $admin->forceFill(['is_admin' => true])->save();

        $controller = new WebhookController();
        $paymentIntentData = [
            'id' => 'pi_test_123',
            'amount_received' => 2500,
            'currency' => 'eur',
            'metadata' => ['user_id' => $user->id],
        ];
        $paymentIntent = new class ($paymentIntentData)
        {
            public function __construct(private array $data)
            {
            }

            public function toArray(): array
            {
                return $this->data;
            }
        };
        $eventPayload = (object) [
            'id' => 'evt_test_123',
            'type' => 'payment_intent.succeeded',
        ];
        $eventRecord = StripeEvent::create([
            'event_id' => $eventPayload->id,
            'type' => $eventPayload->type,
            'signature' => 'sig_test',
            'payload' => [],
            'processing_status' => 'received',
        ]);

        $method = new ReflectionMethod(WebhookController::class, 'handlePaymentIntentSucceeded');
        $method->setAccessible(true);
        $method->invoke($controller, $paymentIntent, $eventPayload, $eventRecord);

        Mail::assertSent(TipReceivedMail::class, function (TipReceivedMail $mail) use ($user) {
            return $mail->hasTo($user->email);
        });
        Mail::assertSent(TipReceivedAdminMail::class, function (TipReceivedAdminMail $mail) use ($admin) {
            return $mail->hasTo($admin->email);
        });
        Mail::assertSent(TipReceivedMail::class, 1);
        Mail::assertSent(TipReceivedAdminMail::class, 1);

        $secondEventPayload = (object) [
            'id' => 'evt_test_456',
            'type' => 'payment_intent.succeeded',
        ];
        $secondEventRecord = StripeEvent::create([
            'event_id' => $secondEventPayload->id,
            'type' => $secondEventPayload->type,
            'signature' => 'sig_test_2',
            'payload' => [],
            'processing_status' => 'received',
        ]);
        $method->invoke($controller, $paymentIntent, $secondEventPayload, $secondEventRecord);

        Mail::assertSent(TipReceivedMail::class, 1);
        Mail::assertSent(TipReceivedAdminMail::class, 1);
    }

    public function test_checkout_session_completed_sends_tip_notifications_once(): void
    {
        Mail::fake();

        $user = User::factory()->create();
        $admin = User::factory()->create();
        $admin->forceFill(['is_admin' => true])->save();

        $controller = new WebhookController();
        $sessionData = [
            'id' => 'cs_test_123',
            'payment_intent' => 'pi_test_checkout',
            'amount_total' => 1800,
            'currency' => 'eur',
            'metadata' => ['user_id' => $user->id],
        ];
        $session = new class ($sessionData)
        {
            public function __construct(private array $data)
            {
            }

            public function toArray(): array
            {
                return $this->data;
            }
        };
        $eventPayload = (object) [
            'id' => 'evt_checkout_123',
            'type' => 'checkout.session.completed',
        ];
        $eventRecord = StripeEvent::create([
            'event_id' => $eventPayload->id,
            'type' => $eventPayload->type,
            'signature' => 'sig_checkout',
            'payload' => [],
            'processing_status' => 'received',
        ]);

        $method = new ReflectionMethod(WebhookController::class, 'handleCheckoutCompleted');
        $method->setAccessible(true);
        $method->invoke($controller, $session, $eventPayload, $eventRecord);

        Mail::assertSent(TipReceivedMail::class, function (TipReceivedMail $mail) use ($user) {
            return $mail->hasTo($user->email);
        });
        Mail::assertSent(TipReceivedAdminMail::class, function (TipReceivedAdminMail $mail) use ($admin) {
            return $mail->hasTo($admin->email);
        });
        Mail::assertSent(TipReceivedMail::class, 1);
        Mail::assertSent(TipReceivedAdminMail::class, 1);

        $secondEventPayload = (object) [
            'id' => 'evt_checkout_456',
            'type' => 'checkout.session.completed',
        ];
        $secondEventRecord = StripeEvent::create([
            'event_id' => $secondEventPayload->id,
            'type' => $secondEventPayload->type,
            'signature' => 'sig_checkout_2',
            'payload' => [],
            'processing_status' => 'received',
        ]);
        $method->invoke($controller, $session, $secondEventPayload, $secondEventRecord);

        Mail::assertSent(TipReceivedMail::class, 1);
        Mail::assertSent(TipReceivedAdminMail::class, 1);
    }
}

