<?php

namespace App\Http\Controllers\Pay;

use App\Http\Controllers\Controller;
use App\Models\Profile;
use Inertia\Inertia;
use Inertia\Response;

class StatusController extends Controller
{
    public function success(Profile $profile): Response
    {
        \Log::info('Pay success page hit', ['slug' => $profile->slug, 'profile_id' => $profile->id]);
        return Inertia::render('pay/Success', [
            'profile' => [
                'slug' => $profile->slug,
                'display_name' => $profile->display_name,
                'review_url' => $profile->review_url,
            ],
            'returnUrl' => route('tips.public', ['profile' => $profile->slug], absolute: false),
        ]);
    }

    public function cancel(Profile $profile): Response
    {
        \Log::info('Pay cancel page hit', ['slug' => $profile->slug, 'profile_id' => $profile->id]);
        return Inertia::render('pay/Cancel', [
            'profile' => [
                'slug' => $profile->slug,
                'display_name' => $profile->display_name,
            ],
            'returnUrl' => route('tips.public', ['profile' => $profile->slug], absolute: false),
        ]);
    }
}
