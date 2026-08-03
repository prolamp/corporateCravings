<?php

namespace App\Modules\PublicSite\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Modules\PublicSite\Services\MenuService;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class MenuController extends Controller
{
    public function __construct(
        private readonly MenuService $menu,
    ) {}

    public function __invoke(Request $request): Response
    {
        $category = $request->string('category')->toString() ?: null;

        return Inertia::render('public/menu', $this->menu->payload([
            'category' => $category,
        ]));
    }
}
