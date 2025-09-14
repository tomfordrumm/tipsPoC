<?php

namespace App\Http\Controllers;

use App\Models\Tip;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class DashboardController extends Controller
{
    public function __invoke(Request $request): Response
    {
        $user = $request->user();

        $isAdmin = (bool) ($user->is_admin ?? false);

        $succeeded = Tip::query()
            ->where('user_id', $user->id)
            ->where('status', 'succeeded');

        $totalCents = (int) $succeeded->clone()->sum('amount_cents');
        $count = (int) $succeeded->clone()->count();

        $transactions = [];
        if (! $isAdmin) {
            $transactions = Tip::query()
                ->where('user_id', $user->id)
                ->orderByDesc('created_at')
                ->limit(50)
                ->get(['id', 'amount_cents', 'currency', 'status', 'created_at', 'paid_at'])
                ->map(function (Tip $tip) {
                    return [
                        'id' => $tip->id,
                        'amount_cents' => (int) $tip->amount_cents,
                        'currency' => $tip->currency,
                        'status' => $tip->status,
                        'created_at' => $tip->created_at?->toISOString(),
                        'paid_at' => $tip->paid_at?->toISOString(),
                    ];
                });
        }

        $adminStats = null;
        if ($isAdmin) {
            $adminStats = [
                'users_count' => (int) \App\Models\User::query()->count(),
                'active_payouts_count' => (int) \App\Models\PayoutRequest::query()->where('status', 'pending')->count(),
            ];
        }

        return Inertia::render('Dashboard', [
            'totals' => [
                'total_cents' => $totalCents,
                'count' => $count,
                'currency' => 'eur',
            ],
            'transactions' => $transactions,
            'admin' => [
                'is_admin' => $isAdmin,
                'stats' => $adminStats,
            ],
        ]);
    }
}
