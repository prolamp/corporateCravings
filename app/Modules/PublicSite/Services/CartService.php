<?php

namespace App\Modules\PublicSite\Services;

use App\Modules\Addons\Models\Addon;
use App\Modules\Catalog\Models\DishVariation;
use App\Support\Enums\VariationType;
use App\Support\ErrorCatalog;
use App\Support\ImageService;
use Illuminate\Support\Facades\Session;
use Illuminate\Validation\ValidationException;

class CartService
{
    public const SESSION_KEY = 'cart.items';

    public function __construct(
        private readonly ImageService $images,
    ) {}

    /**
     * Lightweight shared header payload.
     *
     * @return array{count: int, quantity: int}
     */
    public function summary(): array
    {
        $items = $this->rawItems();
        $quantity = (int) collect($items)->sum(fn (array $item) => (int) ($item['quantity'] ?? 0));

        return [
            'count' => count($items),
            'quantity' => $quantity,
        ];
    }

    /**
     * Resolved cart for the cart page (live prices; drops invalid lines).
     *
     * @return array{
     *     items: list<array<string, mixed>>,
     *     items_count: int,
     *     quantity: int,
     *     subtotal: float,
     *     subtotal_formatted: string,
     *     empty: bool
     * }
     */
    public function payload(): array
    {
        $resolved = $this->resolve();
        $subtotal = (float) collect($resolved)->sum('line_total');

        return [
            'items' => $resolved,
            'items_count' => count($resolved),
            'quantity' => (int) collect($resolved)->sum('quantity'),
            'subtotal' => $subtotal,
            'subtotal_formatted' => $this->formatRs($subtotal),
            'empty' => $resolved === [],
        ];
    }

    /**
     * @param  list<int>  $addonIds
     */
    public function add(int $variationId, int $quantity, array $addonIds = []): void
    {
        $quantity = max(1, min(99, $quantity));
        $addonIds = $this->normalizeAddonIds($addonIds);
        $this->assertCartableLine($variationId, $addonIds);

        $key = $this->lineKey($variationId, $addonIds);
        $items = $this->rawItems();

        if (isset($items[$key])) {
            $items[$key]['quantity'] = max(1, min(99, (int) $items[$key]['quantity'] + $quantity));
        } else {
            $items[$key] = [
                'key' => $key,
                'dish_variation_id' => $variationId,
                'addon_ids' => $addonIds,
                'quantity' => $quantity,
            ];
        }

        $this->store($items);
    }

    public function updateQuantity(string $key, int $quantity): void
    {
        $items = $this->rawItems();

        if (! isset($items[$key])) {
            throw ValidationException::withMessages([
                'key' => ErrorCatalog::message('not_found'),
            ]);
        }

        if ($quantity < 1) {
            unset($items[$key]);
        } else {
            $items[$key]['quantity'] = min(99, $quantity);
        }

        $this->store($items);
    }

    public function remove(string $key): void
    {
        $items = $this->rawItems();
        unset($items[$key]);
        $this->store($items);
    }

    public function clear(): void
    {
        Session::forget(self::SESSION_KEY);
    }

    /**
     * @return list<array<string, mixed>>
     */
    private function resolve(): array
    {
        $raw = $this->rawItems();
        if ($raw === []) {
            return [];
        }

        $variationIds = collect($raw)->pluck('dish_variation_id')->unique()->values()->all();
        $variations = DishVariation::query()
            ->with(['dish.category:id,name,slug'])
            ->whereIn('id', $variationIds)
            ->get()
            ->keyBy('id');

        $allAddonIds = collect($raw)->flatMap(fn (array $item) => $item['addon_ids'] ?? [])->unique()->values()->all();
        $addons = $allAddonIds === []
            ? collect()
            : Addon::query()->whereIn('id', $allAddonIds)->get()->keyBy('id');

        $resolved = [];
        $kept = [];

        foreach ($raw as $key => $item) {
            $variationId = (int) ($item['dish_variation_id'] ?? 0);
            $quantity = max(1, min(99, (int) ($item['quantity'] ?? 1)));
            $addonIds = $this->normalizeAddonIds($item['addon_ids'] ?? []);

            $variation = $variations->get($variationId);
            if (! $variation instanceof DishVariation
                || ! $variation->isCartable()
                || $variation->type !== VariationType::Single
                || $variation->dish === null
                || $variation->dish->status !== 'active'
            ) {
                continue;
            }

            $lineAddons = [];
            $addonsValid = true;
            foreach ($addonIds as $addonId) {
                $addon = $addons->get($addonId);
                if (! $addon instanceof Addon || $addon->status !== 'active') {
                    $addonsValid = false;
                    break;
                }
                if (! $this->addonAllowedForDish($addon, (int) $variation->dish_id)) {
                    $addonsValid = false;
                    break;
                }
                $lineAddons[] = $addon;
            }

            if (! $addonsValid) {
                continue;
            }

            $unitPrice = (float) $variation->price;
            $addonsTotal = (float) collect($lineAddons)->sum(fn (Addon $addon) => (float) $addon->price);
            $unitWithAddons = $unitPrice + $addonsTotal;
            $lineTotal = $unitWithAddons * $quantity;
            $canonicalKey = $this->lineKey($variationId, $addonIds);

            $resolved[] = [
                'key' => $canonicalKey,
                'dish_id' => $variation->dish_id,
                'dish_title' => $variation->dish->title,
                'dish_slug' => $variation->dish->slug,
                'dish_href' => '/menu/'.$variation->dish->slug,
                'image_url' => $this->images->url($variation->dish->image),
                'variation_id' => $variation->id,
                'variation_label' => $variation->label,
                'unit_price' => $unitPrice,
                'unit_price_formatted' => $this->formatRs($unitPrice),
                'unit_total' => $unitWithAddons,
                'addons' => collect($lineAddons)->map(fn (Addon $addon) => [
                    'id' => $addon->id,
                    'title' => $addon->title,
                    'price' => (float) $addon->price,
                    'price_formatted' => $this->formatRs((float) $addon->price),
                ])->values()->all(),
                'addons_total' => $addonsTotal,
                'quantity' => $quantity,
                'line_total' => $lineTotal,
                'line_total_formatted' => $this->formatRs($lineTotal),
            ];

            $kept[$canonicalKey] = [
                'key' => $canonicalKey,
                'dish_variation_id' => $variationId,
                'addon_ids' => $addonIds,
                'quantity' => $quantity,
            ];
        }

        if (array_keys($kept) !== array_keys($raw) || count($kept) !== count($raw)) {
            $this->store($kept);
        }

        return $resolved;
    }

    /**
     * @param  list<int>  $addonIds
     */
    private function assertCartableLine(int $variationId, array $addonIds): void
    {
        $variation = DishVariation::query()
            ->with('dish')
            ->find($variationId);

        if (! $variation instanceof DishVariation
            || $variation->dish === null
            || $variation->dish->status !== 'active'
        ) {
            throw ValidationException::withMessages([
                'dish_variation_id' => ErrorCatalog::message('dish_unavailable'),
            ]);
        }

        if (! $variation->isCartable() || $variation->type !== VariationType::Single) {
            throw ValidationException::withMessages([
                'dish_variation_id' => ErrorCatalog::message('variation_unavailable'),
            ]);
        }

        if ($addonIds === []) {
            return;
        }

        $addons = Addon::query()->whereIn('id', $addonIds)->get()->keyBy('id');

        foreach ($addonIds as $addonId) {
            $addon = $addons->get($addonId);
            if (! $addon instanceof Addon || $addon->status !== 'active') {
                throw ValidationException::withMessages([
                    'addon_ids' => ErrorCatalog::message('addon_unavailable'),
                ]);
            }
            if (! $this->addonAllowedForDish($addon, (int) $variation->dish_id)) {
                throw ValidationException::withMessages([
                    'addon_ids' => ErrorCatalog::message('addon_unavailable'),
                ]);
            }
        }
    }

    private function addonAllowedForDish(Addon $addon, int $dishId): bool
    {
        if ($addon->is_global) {
            return true;
        }

        return $addon->dishes()->where('dishes.id', $dishId)->exists();
    }

    /**
     * @param  list<int|string>  $addonIds
     * @return list<int>
     */
    private function normalizeAddonIds(array $addonIds): array
    {
        return collect($addonIds)
            ->map(fn ($id) => (int) $id)
            ->filter(fn (int $id) => $id > 0)
            ->unique()
            ->sort()
            ->values()
            ->all();
    }

    /**
     * @param  list<int>  $addonIds
     */
    public function lineKey(int $variationId, array $addonIds): string
    {
        $addonIds = $this->normalizeAddonIds($addonIds);

        return $variationId.'_'.($addonIds === [] ? '0' : implode('-', $addonIds));
    }

    /**
     * @return array<string, array{key: string, dish_variation_id: int, addon_ids: list<int>, quantity: int}>
     */
    private function rawItems(): array
    {
        $items = Session::get(self::SESSION_KEY, []);

        return is_array($items) ? $items : [];
    }

    /**
     * @param  array<string, array{key: string, dish_variation_id: int, addon_ids: list<int>, quantity: int}>  $items
     */
    private function store(array $items): void
    {
        Session::put(self::SESSION_KEY, $items);
    }

    private function formatRs(float $amount): string
    {
        return 'Rs. '.number_format($amount, 0);
    }
}
