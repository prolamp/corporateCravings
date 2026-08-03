<?php

namespace App\Modules\PublicSite\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Modules\PublicSite\Services\PackagesService;
use Inertia\Inertia;
use Inertia\Response;

class PackagesController extends Controller
{
    public function __construct(
        private readonly PackagesService $packages,
    ) {}

    public function __invoke(): Response
    {
        return Inertia::render('public/packages', $this->packages->listing());
    }
}
