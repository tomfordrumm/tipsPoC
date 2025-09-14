<?php

namespace Database\Factories;

use App\Models\Profile;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

/**
 * @extends Factory<\App\Models\Profile>
 */
class ProfileFactory extends Factory
{
    protected $model = Profile::class;

    public function definition(): array
    {
        $displayName = fake()->name();
        return [
            'user_id' => User::factory(),
            'slug' => Str::slug($displayName . '-' . fake()->unique()->numberBetween(1000, 9999)),
            'display_name' => $displayName,
            'bio' => fake()->optional()->sentence(12),
            'avatar_path' => null,
            'review_url' => fake()->optional()->url(),
            // store money as cents in quick amounts
            'quick_amounts' => [500, 1000, 2000],
        ];
    }
}

