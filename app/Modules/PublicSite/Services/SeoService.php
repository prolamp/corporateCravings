<?php

namespace App\Modules\PublicSite\Services;

use App\Models\Page;
use App\Modules\Catalog\Models\Dish;
use App\Modules\Cms\Services\SiteSettingsService;
use App\Modules\Packages\Models\Package;
use App\Modules\PublicSite\Services\CmsPageService;
use Carbon\CarbonInterface;
use Illuminate\Support\Carbon;

class SeoService
{
    public function __construct(
        private readonly SiteSettingsService $settings,
    ) {}

    /**
     * Shared defaults for public Inertia pages.
     *
     * @return array{
     *     site_name: string,
     *     title_suffix: string,
     *     default_description: string,
     *     default_image: string|null,
     *     locale: string,
     *     app_url: string,
     *     organization: array<string, mixed>
     * }
     */
    public function sharedDefaults(): array
    {
        $site = $this->settings->publicPayload();
        $brand = $site['brand']['name'] ?: 'Corporate Cravings';

        return [
            'site_name' => $brand,
            'title_suffix' => ' | '.$brand,
            'default_description' => 'Fresh Pakistani meals for Islamabad offices and events—daily lunches, corporate packages, and bulk orders from '.$brand.'.',
            'default_image' => $this->absoluteUrl('/images/brand/logo.svg')
                ?? $this->absoluteUrl($this->designImageUrl('home', 'hero')),
            'locale' => 'en_PK',
            'app_url' => rtrim((string) config('app.url'), '/'),
            'organization' => $this->organizationSchema($site),
        ];
    }

    public function robotsTxt(): string
    {
        $sitemap = url('/sitemap.xml');

        $lines = [
            'User-agent: *',
            'Allow: /',
            'Disallow: /admin',
            'Disallow: /customer',
            'Disallow: /subscriber',
            'Disallow: /cart',
            'Disallow: /checkout',
            'Disallow: /orders/',
            'Disallow: /dashboard',
            'Disallow: /login',
            'Disallow: /register',
            'Disallow: /forgot-password',
            'Disallow: /reset-password',
            'Disallow: /settings',
            'Disallow: /email/',
            'Disallow: /user/',
            'Disallow: /two-factor-challenge',
            '',
            'Sitemap: '.$sitemap,
            '',
        ];

        return implode("\n", $lines);
    }

    public function sitemapXml(): string
    {
        $urls = $this->sitemapEntries();

        $xml = '<?xml version="1.0" encoding="UTF-8"?>'."\n";
        $xml .= '<urlset xmlns="http://www.sitemaps.org/schemas/sitemap/0.9">'."\n";

        foreach ($urls as $entry) {
            $xml .= "  <url>\n";
            $xml .= '    <loc>'.e($entry['loc'])."</loc>\n";
            if (! empty($entry['lastmod'])) {
                $xml .= '    <lastmod>'.e($entry['lastmod'])."</lastmod>\n";
            }
            if (! empty($entry['changefreq'])) {
                $xml .= '    <changefreq>'.e($entry['changefreq'])."</changefreq>\n";
            }
            if (isset($entry['priority'])) {
                $xml .= '    <priority>'.e(number_format((float) $entry['priority'], 1))."</priority>\n";
            }
            $xml .= "  </url>\n";
        }

        $xml .= '</urlset>';

        return $xml;
    }

    /**
     * @return list<array{loc: string, lastmod?: string, changefreq?: string, priority?: float}>
     */
    public function sitemapEntries(): array
    {
        $entries = [];

        $static = [
            ['path' => '/', 'changefreq' => 'daily', 'priority' => 1.0],
            ['path' => '/menu', 'changefreq' => 'daily', 'priority' => 0.9],
            ['path' => '/packages', 'changefreq' => 'weekly', 'priority' => 0.9],
            ['path' => '/bulk-orders', 'changefreq' => 'weekly', 'priority' => 0.8],
            ['path' => '/about', 'changefreq' => 'monthly', 'priority' => 0.7],
            ['path' => '/contact', 'changefreq' => 'monthly', 'priority' => 0.7],
            ['path' => '/faq', 'changefreq' => 'monthly', 'priority' => 0.6],
        ];

        foreach ($static as $page) {
            $entries[] = [
                'loc' => url($page['path']),
                'changefreq' => $page['changefreq'],
                'priority' => $page['priority'],
            ];
        }

        foreach (CmsPageService::LEGAL_SLUGS as $slug) {
            $page = Page::query()
                ->where('slug', $slug)
                ->where('status', 'published')
                ->first(['slug', 'updated_at']);

            if (! $page) {
                continue;
            }

            $entries[] = [
                'loc' => url('/'.$page->slug),
                'lastmod' => $this->formatLastmod($page->updated_at),
                'changefreq' => 'yearly',
                'priority' => 0.3,
            ];
        }

        $dishes = Dish::query()
            ->active()
            ->orderBy('slug')
            ->get(['slug', 'updated_at']);

        foreach ($dishes as $dish) {
            $entries[] = [
                'loc' => url('/menu/'.$dish->slug),
                'lastmod' => $this->formatLastmod($dish->updated_at),
                'changefreq' => 'weekly',
                'priority' => 0.8,
            ];
        }

        $packages = Package::query()
            ->active()
            ->orderBy('slug')
            ->get(['slug', 'updated_at']);

        foreach ($packages as $package) {
            $entries[] = [
                'loc' => url('/packages/'.$package->slug),
                'lastmod' => $this->formatLastmod($package->updated_at),
                'changefreq' => 'weekly',
                'priority' => 0.8,
            ];
        }

        return $entries;
    }

    public function absoluteUrl(?string $pathOrUrl): ?string
    {
        if ($pathOrUrl === null || trim($pathOrUrl) === '') {
            return null;
        }

        $value = trim($pathOrUrl);
        if (str_starts_with($value, 'http://') || str_starts_with($value, 'https://')) {
            return $value;
        }

        return url($value);
    }

    /**
     * @param  array{
     *     brand: array{name: string, phone: string, whatsapp: string, email: string, address: string, hours: string},
     *     whatsapp_url: string|null,
     *     analytics?: array{ga_measurement_id: string|null}
     * }  $site
     * @return array<string, mixed>
     */
    public function organizationSchema(array $site): array
    {
        $brand = $site['brand'];
        $schema = [
            '@context' => 'https://schema.org',
            '@type' => ['Organization', 'FoodEstablishment'],
            'name' => $brand['name'] ?: 'Corporate Cravings',
            'url' => url('/'),
            'image' => $this->absoluteUrl('/images/brand/logo.svg')
                ?? $this->absoluteUrl($this->designImageUrl('home', 'hero')),
            'servesCuisine' => 'Pakistani',
            'address' => [
                '@type' => 'PostalAddress',
                'streetAddress' => $brand['address'] ?: 'I-9 Islamabad, Pakistan',
                'addressLocality' => 'Islamabad',
                'addressCountry' => 'PK',
            ],
        ];

        if ($brand['phone'] !== '') {
            $schema['telephone'] = $brand['phone'];
        }
        if ($brand['email'] !== '') {
            $schema['email'] = $brand['email'];
        }
        if (! empty($site['whatsapp_url'])) {
            $schema['sameAs'] = [$site['whatsapp_url']];
        }
        if ($brand['hours'] !== '') {
            $schema['openingHours'] = $brand['hours'];
        }

        return $schema;
    }

    /**
     * @param  list<array{question: string, answer: string}>  $items
     * @return array<string, mixed>
     */
    public function faqPageSchema(array $items): array
    {
        return [
            '@context' => 'https://schema.org',
            '@type' => 'FAQPage',
            'mainEntity' => array_map(fn (array $item): array => [
                '@type' => 'Question',
                'name' => $item['question'],
                'acceptedAnswer' => [
                    '@type' => 'Answer',
                    'text' => $item['answer'],
                ],
            ], $items),
        ];
    }

    private function designImageUrl(string $folder, string $name): string
    {
        foreach (['webp', 'png', 'jpg'] as $ext) {
            $relative = "images/{$folder}/{$name}.{$ext}";
            if (is_file(public_path($relative))) {
                return '/'.$relative;
            }
        }

        return "/images/{$folder}/{$name}.webp";
    }

    private function formatLastmod(CarbonInterface|Carbon|string|null $value): ?string
    {
        if ($value === null) {
            return null;
        }

        $date = $value instanceof CarbonInterface
            ? $value
            : Carbon::parse($value);

        return $date->timezone('UTC')->toAtomString();
    }
}
