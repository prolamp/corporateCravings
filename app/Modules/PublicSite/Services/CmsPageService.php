<?php

namespace App\Modules\PublicSite\Services;

use App\Models\Page;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

class CmsPageService
{
    /** @var list<string> */
    public const LEGAL_SLUGS = [
        'privacy-policy',
        'terms',
        'delivery-policy',
        'cancellation-refund',
    ];

    /**
     * @return array{
     *     title: string,
     *     slug: string,
     *     body_html: string,
     *     meta_title: string|null,
     *     meta_description: string|null,
     *     updated_at_formatted: string|null,
     * }
     */
    public function payload(string $slug): array
    {
        if (! in_array($slug, self::LEGAL_SLUGS, true)) {
            throw new NotFoundHttpException;
        }

        $page = Page::query()
            ->where('slug', $slug)
            ->where('status', 'published')
            ->first();

        if (! $page) {
            throw new NotFoundHttpException;
        }

        return [
            'title' => $page->title,
            'slug' => $page->slug,
            'body_html' => $this->sanitizeBody((string) ($page->body ?? '')),
            'meta_title' => $page->meta_title,
            'meta_description' => $page->meta_description,
            'updated_at_formatted' => $page->updated_at
                ? $page->updated_at->timezone(config('app.timezone'))->format('d M Y')
                : null,
        ];
    }

    private function sanitizeBody(string $body): string
    {
        $allowed = '<p><br><br/><strong><b><em><i><u><ul><ol><li><h2><h3><h4><a><blockquote><hr>';

        return trim(strip_tags($body, $allowed));
    }
}
