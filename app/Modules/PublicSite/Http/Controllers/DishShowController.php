<?php

namespace App\Modules\PublicSite\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Modules\PublicSite\Services\DishDetailService;
use Inertia\Inertia;
use Inertia\Response;

class DishShowController extends Controller
{
    public function __construct(
        private readonly DishDetailService $dishes,
    ) {}

    public function __invoke(string $slug): Response
    {
        return Inertia::render('public/menu/show', $this->dishes->payload($slug));
    }
}
