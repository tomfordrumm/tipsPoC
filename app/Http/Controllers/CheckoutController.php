<?php

namespace App\Http\Controllers;

use App\Http\Requests\Pay\CheckoutRequest;
use App\Models\Profile;
use App\Services\Stripe\PaymentLinkService;
use Inertia\Inertia;
use Illuminate\Support\Facades\Log;

class CheckoutController extends Controller
{
    public function store(CheckoutRequest $request, PaymentLinkService $links)
    {
        Log::info('Checkout requested: entering controller');
        $validated = $request->validated();
        Log::info('Checkout validated payload', [
            'slug' => $validated['slug'] ?? null,
            'amount_cents' => (int) ($validated['amount_cents'] ?? 0),
            'currency' => $validated['currency'] ?? null,
            'ip' => $request->ip(),
        ]);

        $profile = Profile::where('slug', $validated['slug'])->firstOrFail();
        Log::info('Checkout profile resolved', [
            'profile_id' => $profile->id,
            'user_id' => $profile->user_id,
            'slug' => $profile->slug,
        ]);

        $amount = (int) $validated['amount_cents'];
        $currency = 'eur';

        // Always use the provided amount as source of truth (fixed amount)
        $url = $links->createForProfile($profile, $amount, $currency);
        Log::info('Checkout created Payment Link', [
            'redirect_url' => $url,
        ]);

        // Tell Inertia to perform a full client-side redirect to avoid CORS/XHR issues
        return Inertia::location($url);
    }
}
