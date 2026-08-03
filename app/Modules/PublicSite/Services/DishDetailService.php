<?php

namespace App\Modules\PublicSite\Services;

use App\Modules\Addons\Models\Addon;
use App\Modules\Catalog\Models\Dish;
use App\Modules\Catalog\Models\DishVariation;
use App\Support\Enums\VariationType;
use App\Support\ImageService;

class DishDetailService
{
    public function __construct(
        private readonly ImageService $images,
    ) {}

    /**
     * @return array<string, mixed>
     */
    public function payload(string $slug): array
    {
        $dish = Dish::query()
            ->active()
            ->where('slug', $slug)
            ->with([
                'category:id,name,slug',
                'variations' => fn ($query) => $query
                    ->where('status', 'active')
                    ->orderBy('sort_order')
                    ->orderBy('price'),
                'addons' => fn ($query) => $query
                    ->where('addons.status', 'active')
                    ->orderBy('addons.sort_order')
                    ->orderBy('addons.title'),
            ])
            ->firstOrFail();

        $singles = $dish->variations
            ->where('type', VariationType::Single)
            ->values();

        $bulk = $dish->variations
            ->where('type', VariationType::Bulk)
            ->values();

        $linkedAddons = $dish->addons;
        $globalAddons = Addon::query()
            ->where('status', 'active')
            ->where('is_global', true)
            ->orderBy('sort_order')
            ->orderBy('title')
            ->get();

        $addons = $linkedAddons
            ->concat($globalAddons)
            ->unique('id')
            ->sortBy([
                ['sort_order', 'asc'],
                ['title', 'asc'],
            ])
            ->values();

        $starting = $singles->sortBy('price')->first();

        return [
            'dish' => [
                'id' => $dish->id,
                'title' => $dish->title,
                'slug' => $dish->slug,
                'short_description' => $dish->short_description,
                'category_name' => $dish->category?->name,
                'category_slug' => $dish->category?->slug,
                'image_url' => $this->images->url($dish->image),
                'starting_price' => $starting ? (float) $starting->price : null,
                'starting_price_formatted' => $starting
                    ? $this->formatRs((float) $starting->price)
                    : null,
            ],
            'single_variations' => $singles->map(fn (DishVariation $v) => $this->variation($v, true))->all(),
            'bulk_variations' => $bulk->map(fn (DishVariation $v) => $this->variation($v, false))->all(),
            'addons' => $addons->map(fn (Addon $addon) => [
                'id' => $addon->id,
                'title' => $addon->title,
                'type' => (string) $addon->type,
                'price' => (float) $addon->price,
                'price_formatted' => $this->formatRs((float) $addon->price),
            ])->all(),
        ];
    }

    /**
     * @return array<string, mixed>
     */
    private function variation(DishVariation $variation, bool $cartable): array
    {
        $type = $variation->type instanceof VariationType
            ? $variation->type
            : VariationType::from((string) $variation->type);

        return [
            'id' => $variation->id,
            'label' => $variation->label,
            'quantity' => (float) $variation->quantity,
            'unit' => (string) $variation->unit,
            'price' => (float) $variation->price,
            'price_formatted' => $this->formatRs((float) $variation->price),
            'type' => $type->value,
            'cartable' => $cartable && $type === VariationType::Single,
        ];
    }

    private function formatRs(float $amount): string
    {
        return 'Rs. '.number_format($amount, 0);
    }
}
