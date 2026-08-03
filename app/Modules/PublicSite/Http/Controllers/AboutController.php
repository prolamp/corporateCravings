<?php

namespace App\Modules\PublicSite\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Modules\PublicSite\Services\AboutService;
use Inertia\Inertia;
use Inertia\Response;

class AboutController extends Controller
{
    public function __construct(
        private readonly AboutService $about,
    ) {}

    public function __invoke(): Response
    {
        return Inertia::render('public/about', $this->about->payload());
    }
}
