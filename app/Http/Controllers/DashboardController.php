<?php

namespace App\Http\Controllers;

use App\Models\PayoutRequest;
use App\Models\Tip;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class DashboardController extends Controller
{
    public function __invoke(Request $request): Response
    {
        $user = $request->user();

        $isAdmin = (bool) ($user->is_admin ?? false);

        $range = $request->string('range', 'all')->lower()->value();
        $rangeOptions = $this->rangeOptions();
        if (! collect($rangeOptions)->pluck('value')->contains($range)) {
            $range = 'all';
        }

        [$rangeStart, $rangeEnd] = $this->resolveRange($range);

        $succeeded = Tip::query()
            ->where('user_id', $user->id)
            ->where('status', 'succeeded');

        $totalCents = (int) $succeeded->clone()->sum('amount_cents');
        $count = (int) $succeeded->clone()->count();

        // Calculate available balance = succeeded tips - outstanding payouts (requested/paid)
        $payoutsCents = (int) \App\Models\PayoutRequest::query()
            ->where('user_id', $user->id)
            ->whereIn('status', ['requested', 'paid'])
            ->sum('amount_cents');
        $availableCents = max(0, $totalCents - $payoutsCents);
        $payoutsCount = (int) \App\Models\PayoutRequest::query()
            ->where('user_id', $user->id)
            ->count();

        $transactions = collect();
        if (! $isAdmin) {
            $tipTransactions = Tip::query()
                ->where('user_id', $user->id)
                ->when($rangeStart && $rangeEnd, function ($query) use ($rangeStart, $rangeEnd) {
                    $query->whereBetween('created_at', [$rangeStart, $rangeEnd]);
                })
                ->orderByDesc('created_at')
                ->limit(100)
                ->get(['id', 'amount_cents', 'currency', 'status', 'created_at', 'paid_at'])
                ->map(function (Tip $tip) {
                    return [
                        'id' => 'tip-'.$tip->id,
                        'type' => 'tip',
                        'amount_cents' => (int) $tip->amount_cents,
                        'currency' => $tip->currency,
                        'status' => $tip->status,
                        'occurred_at' => optional($tip->paid_at ?? $tip->created_at)->toISOString(),
                    ];
                });

            $payoutTransactions = PayoutRequest::query()
                ->where('user_id', $user->id)
                ->when($rangeStart && $rangeEnd, function ($query) use ($rangeStart, $rangeEnd) {
                    $query->whereBetween('requested_at', [$rangeStart, $rangeEnd]);
                })
                ->orderByDesc('requested_at')
                ->limit(100)
                ->get(['id', 'amount_cents', 'status', 'requested_at'])
                ->map(function (PayoutRequest $payout) {
                    return [
                        'id' => 'payout-'.$payout->id,
                        'type' => 'payout',
                        'amount_cents' => $payout->amount_cents,
                        'currency' => 'eur',
                        'status' => $payout->status,
                        'occurred_at' => optional($payout->requested_at)->toISOString(),
                    ];
                });

            $transactions = $tipTransactions
                ->concat($payoutTransactions)
                ->filter(fn ($item) => $item['occurred_at'] !== null)
                ->sortByDesc('occurred_at')
                ->values()
                ->take(50)
                ->all();
        }

        $adminStats = null;
        if ($isAdmin) {
            $adminStats = [
                'users_count' => (int) \App\Models\User::query()->count(),
                'active_payouts_count' => (int) \App\Models\PayoutRequest::query()->where('status', 'requested')->count(),
            ];
        }

        return Inertia::render('Dashboard', [
            'filters' => [
                'range' => $range,
            ],
            'rangeOptions' => $rangeOptions,
            'totals' => [
                'total_cents' => $totalCents,
                'count' => $count,
                'currency' => 'eur',
            ],
            'balance' => [
                'available_cents' => $availableCents,
                'currency' => 'eur',
                'payouts_count' => $payoutsCount,
            ],
            'transactions' => $transactions,
            'admin' => [
                'is_admin' => $isAdmin,
                'stats' => $adminStats,
            ],
        ]);
    }

    /**
     * @return array{0: Carbon|null, 1: Carbon|null}
     */
    private function resolveRange(string $range): array
    {
        $now = Carbon::now();

        return match ($range) {
            'past-week' => [
                $now->copy()->subDays(7)->startOfDay(),
                $now->copy(),
            ],
            'last-week' => [
                $now->copy()->subWeek()->startOfWeek(),
                $now->copy()->subWeek()->endOfWeek(),
            ],
            'last-month' => [
                $now->copy()->subMonth()->startOfMonth(),
                $now->copy()->subMonth()->endOfMonth(),
            ],
            default => [null, null],
        };
    }

    private function rangeOptions(): array
    {
        return [
            ['value' => 'all', 'label' => 'All time'],
            ['value' => 'past-week', 'label' => 'Past 7 days'],
            ['value' => 'last-week', 'label' => 'Last week'],
            ['value' => 'last-month', 'label' => 'Last month'],
        ];
    }
}
