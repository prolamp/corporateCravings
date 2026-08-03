<?php

namespace App\Modules\PublicSite\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Modules\PublicSite\Services\CmsPageService;
use Inertia\Inertia;
use Inertia\Response;

class CmsPageController extends Controller
{
    public function __construct(
        private readonly CmsPageService $pages,
    ) {}

    public function __invoke(string $slug): Response
    {
        return Inertia::render('public/cms-page', $this->pages->payload($slug));
    }
}
