<?php

namespace App\Modules\PublicSite\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Modules\PublicSite\Services\PackagesService;
use Inertia\Inertia;
use Inertia\Response;

class PackageShowController extends Controller
{
    public function __construct(
        private readonly PackagesService $packages,
    ) {}

    public function __invoke(string $slug): Response
    {
        return Inertia::render('public/packages/show', $this->packages->detail($slug));
    }
}
