<?php

namespace App\Modules\PublicSite\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Modules\Orders\Models\Order;
use App\Modules\PublicSite\Services\CustomerPortalService;
use App\Support\ErrorCatalog;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class CustomerPortalController extends Controller
{
    public function __construct(
        private readonly CustomerPortalService $portal,
    ) {}

    public function dashboard(Request $request): Response
    {
        $user = $this->customer($request);

        return Inertia::render(
            'customer/dashboard',
            $this->portal->dashboard($user),
        );
    }

    public function orders(Request $request): Response
    {
        $user = $this->customer($request);
        abort_unless($user->can('portal.customer.history') || $user->can('portal.customer.orders'), 403);

        return Inertia::render(
            'customer/orders',
            $this->portal->orders($user),
        );
    }

    public function reorder(Request $request, Order $order): RedirectResponse
    {
        $user = $this->customer($request);
        abort_unless($user->can('portal.customer.orders'), 403);

        $result = $this->portal->reorder($user, $order);

        $message = $result['skipped'] > 0
            ? ErrorCatalog::message(
                'order_reorder_partial',
                'Some items were unavailable and skipped. Available items were added to your cart.',
            )
            : ErrorCatalog::message(
                'order_reordered',
                'Items added to your cart.',
            );

        return redirect()
            ->route('cart')
            ->with([
                'success' => true,
                'message' => $message,
            ]);
    }

    private function customer(Request $request): User
    {
        $user = $request->user();
        abort_unless($user instanceof User, 401);
        abort_unless($user->can('portal.customer.dashboard'), 403);

        return $user;
    }
}
