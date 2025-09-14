<?php

namespace App\Http\Controllers\Public;

use App\Http\Controllers\Controller;
use App\Models\Profile;
use Inertia\Inertia;
use Inertia\Response;

class TippingController extends Controller
{
    public function show(Profile $profile): Response
    {
        return Inertia::render('public/Tipping', [
            'profile' => [
                'slug' => $profile->slug,
                'display_name' => $profile->display_name,
                'bio' => $profile->bio,
                'avatar_url' => $profile->avatar_path ? asset('storage/'.$profile->avatar_path) : null,
                'quick_amounts' => $profile->quick_amounts ?: [500, 1000, 2000, 5000],
            ],
            'currency' => 'eur',
            'minAmountCents' => 100,
            'maxAmountCents' => 500000,
        ]);
    }
}
