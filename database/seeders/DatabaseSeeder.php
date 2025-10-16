<?php

namespace Database\Seeders;

use App\Models\User;
use App\Models\LegalPage;
// use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        // User::factory(10)->create();

        User::factory()->create([
            'name' => 'Test User',
            'email' => 'test@example.com',
        ]);

        $legalPages = [
            [
                'slug' => LegalPage::TERMS_SLUG,
                'title' => 'Terms & Conditions',
            ],
            [
                'slug' => LegalPage::PRIVACY_SLUG,
                'title' => 'Privacy Policy',
            ],
        ];

        foreach ($legalPages as $page) {
            $existing = LegalPage::query()->firstWhere('slug', $page['slug']);

            LegalPage::updateOrCreate(
                ['slug' => $page['slug']],
                [
                    'title' => $page['title'],
                    'content' => $existing?->content,
                ],
            );
        }
    }
}
