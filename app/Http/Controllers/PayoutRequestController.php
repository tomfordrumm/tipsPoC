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

        $payout = PayoutRequest::create([
            'user_id' => $user->id,
            'amount_cents' => $request->validated('amount_cents'),
            'status' => 'pending',
            'requested_at' => now(),
        ]);

        $adminEmails = User::query()->where('is_admin', true)->pluck('email')->filter()->all();
        if (! empty($adminEmails)) {
            Mail::to($adminEmails)->send(new PayoutRequestAdminMail($payout));
        }

        return back()->with('status', 'payout-requested');
    }
}

