<?php

namespace App\Modules\PublicSite\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Modules\PublicSite\Services\TrackOrderService;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class TrackOrderController extends Controller
{
    public function __construct(
        private readonly TrackOrderService $trackOrder,
    ) {}

    public function __invoke(Request $request): Response
    {
        $orderNumber = $request->string('order')->toString();

        return Inertia::render(
            'public/track-order',
            $this->trackOrder->payload($orderNumber !== '' ? $orderNumber : null),
        );
    }
}
