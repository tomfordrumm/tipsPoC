<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\PayoutRequestUpdateRequest;
use App\Models\PayoutRequest;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class PayoutRequestsController extends Controller
{
    public function index(Request $request): Response
    {
        $payouts = PayoutRequest::query()
            ->with('user')
            ->orderByDesc('created_at')
            ->paginate(20)
            ->through(function (PayoutRequest $p) {
                return [
                    'id' => $p->id,
                    'user' => $p->user ? ['id' => $p->user->id, 'name' => $p->user->name] : null,
                    'amount_cents' => $p->amount_cents,
                    'status' => $p->status,
                    'requested_at' => $p->requested_at?->toISOString(),
                    'processed_at' => $p->processed_at?->toISOString(),
                    'admin_note' => $p->admin_note,
                ];
            });

        return Inertia::render('admin/payouts/Index', [
            'payouts' => $payouts,
        ]);
    }

    public function update(PayoutRequestUpdateRequest $request, PayoutRequest $payoutRequest): RedirectResponse
    {
        $data = $request->validated();
        $payoutRequest->status = $data['status'];
        $payoutRequest->admin_note = $data['admin_note'] ?? null;
        $payoutRequest->processed_at = in_array($data['status'], ['paid', 'rejected'], true) ? now() : null;
        $payoutRequest->save();

        return back()->with('status', 'payout-status-updated');
    }
}
