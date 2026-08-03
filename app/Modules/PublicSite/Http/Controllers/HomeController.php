<?php

namespace App\Modules\PublicSite\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Modules\PublicSite\Services\HomeService;
use Inertia\Inertia;
use Inertia\Response;

class HomeController extends Controller
{
    public function __construct(
        private readonly HomeService $home,
    ) {}

    public function __invoke(): Response
    {
        return Inertia::render('public/home', $this->home->payload());
    }
}
