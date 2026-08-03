<?php

namespace App\Modules\PublicSite\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Modules\PublicSite\Http\Requests\PlaceCheckoutOrderRequest;
use App\Modules\PublicSite\Services\CartService;
use App\Modules\PublicSite\Services\CheckoutService;
use App\Support\ErrorCatalog;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class CheckoutController extends Controller
{
    public function __construct(
        private readonly CheckoutService $checkout,
        private readonly CartService $cart,
    ) {}

    public function create(Request $request): Response|RedirectResponse
    {
        if ($this->cart->payload()['empty']) {
            return redirect()
                ->route('cart')
                ->with('error', ErrorCatalog::message('cart_empty'));
        }

        return Inertia::render('public/checkout', $this->checkout->payload($request->user()));
    }

    public function store(PlaceCheckoutOrderRequest $request): RedirectResponse
    {
        if ($this->cart->payload()['empty']) {
            return redirect()
                ->route('cart')
                ->with('error', ErrorCatalog::message('cart_empty'));
        }

        $order = $this->checkout->place([
            ...$request->validated(),
            'user_agent' => $request->userAgent(),
        ], $request->user());

        // Always continue the public checkout flow (never dashboard / portal home).
        return redirect()
            ->route('orders.success', $order->order_number)
            ->with([
                'success' => true,
                'message' => 'Order placed successfully.',
            ]);
    }

    public function success(string $orderNumber): Response
    {
        return Inertia::render(
            'public/order-success',
            $this->checkout->successPayload($orderNumber),
        );
    }
}
