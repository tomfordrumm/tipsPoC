<?php

namespace Tests\Feature;

use App\Models\Profile;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class PublicProfileQuickAmountsTest extends TestCase
{
    use RefreshDatabase;

    private function makeUserWithProfile(): User
    {
        $user = User::factory()->create();

        Profile::factory()->for($user)->create([
            'slug' => 'user-'.$user->id,
            'quick_amounts' => [500, 1000, 2000, 3000],
        ]);

        return $user->fresh('profile');
    }

    public function test_quick_amounts_must_be_at_least_one_euro(): void
    {
        $user = $this->makeUserWithProfile();

        $response = $this
            ->from(route('public-profile.edit'))
            ->actingAs($user)
            ->patch(route('public-profile.update'), [
                'display_name' => 'Example',
                'slug' => $user->profile->slug,
                'bio' => null,
                'review_url' => null,
                'quick_amounts' => [50, 100, 200, 300],
            ]);

        $response->assertRedirect(route('public-profile.edit'));
        $response->assertSessionHasErrors(['quick_amounts.0']);
    }

    public function test_quick_amounts_must_be_unique(): void
    {
        $user = $this->makeUserWithProfile();

        $response = $this
            ->from(route('public-profile.edit'))
            ->actingAs($user)
            ->patch(route('public-profile.update'), [
                'display_name' => 'Example',
                'slug' => $user->profile->slug,
                'bio' => null,
                'review_url' => null,
                'quick_amounts' => [100, 100, 200, 300],
            ]);

        $response->assertRedirect(route('public-profile.edit'));
        $response->assertSessionHasErrors(['quick_amounts']);
    }

    public function test_quick_amounts_update_with_valid_values(): void
    {
        $user = $this->makeUserWithProfile();

        $response = $this
            ->from(route('public-profile.edit'))
            ->actingAs($user)
            ->patch(route('public-profile.update'), [
                'display_name' => 'Example',
                'slug' => $user->profile->slug,
                'bio' => null,
                'review_url' => null,
                'quick_amounts' => [100, 200, 300, 400],
            ]);

        $response->assertRedirect(route('public-profile.edit'));
        $response->assertSessionDoesntHaveErrors();

        $this->assertSame([100, 200, 300, 400], $user->fresh()->profile->quick_amounts);
    }
}
