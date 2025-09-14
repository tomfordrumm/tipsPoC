<?php

namespace App\Http\Controllers;

use App\Http\Requests\Profile\UpdateProfileRequest;
use App\Models\Profile;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Storage;
use Inertia\Inertia;
use Inertia\Response;

class ProfileController extends Controller
{
    public function edit(): Response
    {
        $user = auth()->user();

        $profile = $user->profile ?: $user->profile()->create([
            'slug' => \App\Models\Profile::generateUniqueSlug($user->name ?: ('user-'.$user->id)),
            'display_name' => $user->name,
            'quick_amounts' => [500, 1000, 2000, 5000],
        ]);

        return Inertia::render('profile/Edit', [
            'profile' => [
                'slug' => $profile->slug,
                'display_name' => $profile->display_name,
                'bio' => $profile->bio,
                'review_url' => $profile->review_url,
                'quick_amounts' => $profile->quick_amounts ?: [null, null, null, null],
                'avatar_path' => $profile->avatar_path,
                'avatar_url' => $profile->avatar_path ? asset('storage/'.$profile->avatar_path) : null,
            ],
        ]);
    }

    public function update(UpdateProfileRequest $request): RedirectResponse
    {
        $user = $request->user();
        /** @var Profile $profile */
        $profile = $user->profile ?: new Profile(['user_id' => $user->id]);

        $data = $request->safe()->only(['slug', 'display_name', 'bio', 'review_url', 'quick_amounts']);

        if ($request->hasFile('avatar')) {
            if ($profile->avatar_path) {
                Storage::disk('public')->delete($profile->avatar_path);
            }

            $path = $request->file('avatar')->store('avatars/'.$user->id, 'public');
            $data['avatar_path'] = $path;
        }

        $profile->fill($data);
        $profile->user()->associate($user);
        $profile->save();

        return back()->with('status', 'profile-updated');
    }
}
