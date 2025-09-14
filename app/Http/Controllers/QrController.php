<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class QrController extends Controller
{
    public function show(Request $request): Response
    {
        $user = $request->user();
        $profile = $user->profile ?: $user->profile()->create([
            'slug' => \App\Models\Profile::generateUniqueSlug($user->name ?: ('user-'.$user->id)),
            'display_name' => $user->name,
            'quick_amounts' => [500, 1000, 2000, 5000],
        ]);

        $url = route('tips.public', ['profile' => $profile->slug]);

        return Inertia::render('qr/Index', [
            'slug' => $profile->slug,
            'url' => $url,
        ]);
    }
}
