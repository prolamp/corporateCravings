<?php

namespace App\Modules\PublicSite\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Modules\PublicSite\Services\BulkOrdersService;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class BulkOrdersController extends Controller
{
    public function __construct(
        private readonly BulkOrdersService $bulkOrders,
    ) {}

    public function __invoke(Request $request): Response
    {
        $category = $request->string('category')->toString() ?: null;

        return Inertia::render('public/bulk-orders', $this->bulkOrders->payload([
            'category' => $category,
        ]));
    }
}
