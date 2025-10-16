<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\UpdateLegalPageRequest;
use App\Models\LegalPage;
use Illuminate\Http\RedirectResponse;
use Inertia\Inertia;
use Inertia\Response;

class LegalPageController extends Controller
{
    public function edit(string $slug): Response
    {
        $page = $this->resolvePage($slug);

        return Inertia::render('admin/legal/Edit', [
            'page' => [
                'id' => $page->id,
                'slug' => $page->slug,
                'title' => $page->title,
                'content' => $page->content ?? '',
                'updated_at' => $page->updated_at?->toIso8601String(),
            ],
            'updateUrl' => route('admin.legal.update', ['slug' => $page->slug]),
        ]);
    }

    public function update(UpdateLegalPageRequest $request, string $slug): RedirectResponse
    {
        $page = $this->resolvePage($slug);

        $page->update([
            'content' => $request->validated()['content'] ?? null,
        ]);

        return back()->with('success', 'Page saved.');
    }

    private function resolvePage(string $slug): LegalPage
    {
        $allowed = $this->allowedPages();

        abort_unless(array_key_exists($slug, $allowed), 404);

        return LegalPage::firstOrCreate(
            ['slug' => $slug],
            ['title' => $allowed[$slug]],
        );
    }

    /**
     * @return array<string, string>
     */
    private function allowedPages(): array
    {
        return [
            LegalPage::TERMS_SLUG => 'Terms & Conditions',
            LegalPage::PRIVACY_SLUG => 'Privacy Policy',
        ];
    }
}
