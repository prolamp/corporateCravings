<?php

namespace App\Modules\PublicSite\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Modules\PublicSite\Services\SeoService;
use Illuminate\Http\Response;

class SeoController extends Controller
{
    public function __construct(
        private readonly SeoService $seo,
    ) {}

    public function robots(): Response
    {
        return response($this->seo->robotsTxt(), 200, [
            'Content-Type' => 'text/plain; charset=UTF-8',
        ]);
    }

    public function sitemap(): Response
    {
        return response($this->seo->sitemapXml(), 200, [
            'Content-Type' => 'application/xml; charset=UTF-8',
        ]);
    }
}
