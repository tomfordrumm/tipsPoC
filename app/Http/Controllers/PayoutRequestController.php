<?php

namespace App\Http\Controllers;

use App\Http\Requests\Payout\StorePayoutRequest;
use App\Mail\PayoutRequestAdminMail;
use App\Models\PayoutRequest;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Mail;

class PayoutRequestController extends Controller
{
    public function store(StorePayoutRequest $request): RedirectResponse
    {
        $user = $request->user();

        // Calculate available balance: succeeded tips - outstanding payouts (requested/paid)
        $succeededTotal = (int) \App\Models\Tip::query()
            ->where('user_id', $user->id)
            ->where('status', 'succeeded')
            ->sum('amount_cents');
        $reserved = (int) PayoutRequest::query()
            ->where('user_id', $user->id)
            ->whereIn('status', ['requested', 'paid'])
            ->sum('amount_cents');
        $available = max(0, $succeededTotal - $reserved);

        $requested = $request->validated('amount_cents');
        if (is_null($requested)) {
            // "All" request -> take full available
            $requested = $available;
        }

        if ($available <= 0) {
            return back()->withErrors(['amount_cents' => 'No available balance to withdraw.'])->withInput();
        }

        if ($requested < 1 || $requested > $available) {
            return back()->withErrors(['amount_cents' => 'Amount must be between 1 and your available balance.'])->withInput();
        }

        $payout = PayoutRequest::create([
            'user_id' => $user->id,
            'amount_cents' => (int) $requested,
            'status' => 'requested',
            'requested_at' => now(),
        ]);

        $adminEmails = User::query()->where('is_admin', true)->pluck('email')->filter()->all();
        if (! empty($adminEmails)) {
            Mail::to($adminEmails)->send(new PayoutRequestAdminMail($payout));
        }

        return back()->with('status', 'payout-requested');
    }
}
