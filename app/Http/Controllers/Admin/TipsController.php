<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Tip;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class TipsController extends Controller
{
    public function index(Request $request): Response
    {
        $tips = Tip::query()
            ->with('user')
            ->orderByDesc('created_at')
            ->paginate(20)
            ->through(function (Tip $t) {
                return [
                    'id' => $t->id,
                    'user' => $t->user ? ['id' => $t->user->id, 'name' => $t->user->name] : null,
                    'amount_cents' => (int) $t->amount_cents,
                    'currency' => $t->currency,
                    'status' => $t->status,
                    'paid_at' => $t->paid_at?->toISOString(),
                    'created_at' => $t->created_at?->toISOString(),
                ];
            });

        return Inertia::render('admin/tips/Index', [
            'tips' => $tips,
        ]);
    }
}

