<?php

namespace App\Services\Stripe;

use App\Models\Profile;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Log;
use RuntimeException;

class PaymentLinkService
{
    public function __construct(
        private readonly ?string $secret = null,
    ) {
    }

    /**
     * Create a Stripe Payment Link for a tip using a fixed amount.
     */
    public function createForProfile(Profile $profile, int $amountCents, string $currency = 'eur'): string
    {
        $secret = $this->secret ?? config('services.stripe.key');
        if (! $secret) {
            throw new RuntimeException('Stripe secret key is not configured. Set STRIPE_SECRET in your environment.');
        }

        if (! class_exists('Stripe\\StripeClient')) {
            throw new RuntimeException('Stripe SDK not installed. Run: composer require stripe/stripe-php');
        }

        $client = new \Stripe\StripeClient($secret);
        Log::info('Stripe Payment Link: building request', [
            'profile_id' => $profile->id,
            'user_id' => $profile->user_id,
            'slug' => $profile->slug,
            'amount_cents' => $amountCents,
            'currency' => $currency,
        ]);

        $productName = 'Tip for '.$profile->display_name;

        $meta = [
            'user_id' => $profile->user_id,
            'profile_id' => $profile->id,
            'public_slug' => $profile->slug,
        ];

        $linkParams = [
            'metadata' => $meta,
            // Ensure resulting PaymentIntent carries metadata for webhook mapping
            'payment_intent_data' => [
                'metadata' => $meta,
            ],
        ];

        // Always fixed amount
        $linkParams['line_items'] = [[
            'price_data' => [
                'currency' => $currency,
                'product_data' => [
                    'name' => $productName,
                ],
                'unit_amount' => $amountCents,
            ],
            'quantity' => 1,
            'adjustable_quantity' => [
                'enabled' => false,
            ],
        ]];

        // Redirect to our success page after completion
        $successUrl = route('pay.success', ['profile' => $profile->slug], true);
        $linkParams['after_completion'] = [
            'type' => 'redirect',
            'redirect' => [
                'url' => $successUrl,
            ],
        ];

        Log::info('Stripe Payment Link request', Arr::except($linkParams, []));

        $plink = $client->paymentLinks->create($linkParams);
        Log::info('Stripe Payment Link created', [
            'payment_link_id' => $plink->id ?? null,
            'url' => $plink->url ?? null,
        ]);

        return $plink->url;
    }
}
