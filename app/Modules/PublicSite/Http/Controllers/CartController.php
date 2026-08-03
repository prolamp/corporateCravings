<?php

namespace App\Modules\PublicSite\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Modules\PublicSite\Http\Requests\RemoveCartItemRequest;
use App\Modules\PublicSite\Http\Requests\StoreCartItemRequest;
use App\Modules\PublicSite\Http\Requests\UpdateCartItemRequest;
use App\Modules\PublicSite\Services\CartService;
use Illuminate\Http\RedirectResponse;
use Inertia\Inertia;
use Inertia\Response;

class CartController extends Controller
{
    public function __construct(
        private readonly CartService $cart,
    ) {}

    public function index(): Response
    {
        return Inertia::render('public/cart', $this->cart->payload());
    }

    public function store(StoreCartItemRequest $request): RedirectResponse
    {
        $this->cart->add(
            (int) $request->validated('dish_variation_id'),
            (int) ($request->validated('quantity') ?? 1),
            $request->validated('addon_ids') ?? [],
        );

        return back()->with([
            'success' => true,
            'message' => 'Added to cart.',
        ]);
    }

    public function update(UpdateCartItemRequest $request): RedirectResponse
    {
        $this->cart->updateQuantity(
            (string) $request->validated('key'),
            (int) $request->validated('quantity'),
        );

        return redirect()->route('cart')->with([
            'success' => true,
            'message' => 'Cart updated.',
        ]);
    }

    public function destroy(RemoveCartItemRequest $request): RedirectResponse
    {
        $this->cart->remove((string) $request->validated('key'));

        return redirect()->route('cart')->with([
            'success' => true,
            'message' => 'Item removed.',
        ]);
    }

    public function clear(): RedirectResponse
    {
        $this->cart->clear();

        return redirect()->route('cart')->with([
            'success' => true,
            'message' => 'Cart cleared.',
        ]);
    }
}
