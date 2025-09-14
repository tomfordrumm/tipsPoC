<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\AdminProfileUpdateRequest;
use App\Models\Profile;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class UsersController extends Controller
{
    public function index(Request $request): Response
    {
        $users = User::query()
            ->with('profile')
            ->orderBy('id', 'desc')
            ->paginate(15)
            ->through(function (User $u) {
                return [
                    'id' => $u->id,
                    'name' => $u->name,
                    'email' => $u->email,
                    'is_admin' => (bool) $u->is_admin,
                    'profile' => $u->profile ? [
                        'slug' => $u->profile->slug,
                        'display_name' => $u->profile->display_name,
                    ] : null,
                ];
            });

        return Inertia::render('admin/users/Index', [
            'users' => $users,
        ]);
    }

    public function show(User $user): Response
    {
        $user->load('profile');

        return Inertia::render('admin/users/Show', [
            'user' => [
                'id' => $user->id,
                'name' => $user->name,
                'email' => $user->email,
                'is_admin' => (bool) $user->is_admin,
            ],
            'profile' => $user->profile ? [
                'id' => $user->profile->id,
                'slug' => $user->profile->slug,
                'display_name' => $user->profile->display_name,
                'bio' => $user->profile->bio,
                'review_url' => $user->profile->review_url,
                'quick_amounts' => $user->profile->quick_amounts,
            ] : null,
        ]);
    }

    public function updateProfile(AdminProfileUpdateRequest $request, User $user): RedirectResponse
    {
        $data = $request->validated();
        $profile = $user->profile ?: new Profile(['user_id' => $user->id]);
        $profile->fill($data);
        $profile->user()->associate($user);
        $profile->save();

        return back()->with('status', 'profile-updated');
    }
}

