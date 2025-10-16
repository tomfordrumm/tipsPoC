<?php

namespace App\Http\Controllers;

use App\Models\LegalPage;
use Inertia\Inertia;
use Inertia\Response;

class LegalPageController extends Controller
{
    public function terms(): Response
    {
        return $this->renderPage(LegalPage::TERMS_SLUG);
    }

    public function privacy(): Response
    {
        return $this->renderPage(LegalPage::PRIVACY_SLUG);
    }

    private function renderPage(string $slug): Response
    {
        $page = LegalPage::query()->firstOrCreate(
            ['slug' => $slug],
            ['title' => $this->titleForSlug($slug)],
        );

        return Inertia::render('legal/Show', [
            'title' => $page->title,
            'content' => $page->content ?? '',
            'updated_at' => $page->updated_at?->toIso8601String(),
        ]);
    }

    private function titleForSlug(string $slug): string
    {
        return match ($slug) {
            LegalPage::TERMS_SLUG => 'Terms & Conditions',
            LegalPage::PRIVACY_SLUG => 'Privacy Policy',
            default => 'Legal',
        };
    }
}
