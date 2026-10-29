<?php

namespace App\Modules\PublicSite\Services;

use App\Modules\Packages\Models\Package;
use App\Modules\Packages\Models\PackageItem;
use App\Support\ImageService;

class PackagesService
{
    public function __construct(
        private readonly ImageService $images,
    ) {}

    /**
     * @return array{
     *     packages: list<array<string, mixed>>,
     *     packages_count: int,
     *     package_from_formatted: string|null
     * }
     */
    public function listing(): array
    {
        $packages = Package::query()
            ->visibleOnWebsite()
            ->withCount('items')
            ->orderBy('sort_order')
            ->orderBy('title')
            ->get()
            ->map(fn (Package $package) => $this->card($package))
            ->values()
            ->all();

        $from = collect($packages)
            ->pluck('price_per_person')
            ->filter(fn ($amount) => $amount !== null)
            ->min();

        return [
            'packages' => $packages,
            'packages_count' => count($packages),
            'package_from_formatted' => $from !== null
                ? $this->formatRs((float) $from)
                : null,
        ];
    }

    /**
     * @return array<string, mixed>
     */
    public function detail(string $slug): array
    {
        $package = Package::query()
            ->visibleOnWebsite()
            ->where('slug', $slug)
            ->with([
                'items' => fn ($query) => $query
                    ->with(['dish:id,title,slug,status', 'dishVariation:id,label'])
                    ->orderBy('sort_order'),
            ])
            ->firstOrFail();

        $items = $package->items
            ->filter(fn (PackageItem $item) => $item->dish !== null && $item->dish->status === 'active')
            ->values();

        return [
            'package' => $this->card($package) + [
                'base_amount_per_person' => (float) $package->base_amount_per_person,
                'base_amount_per_person_formatted' => $this->formatRs((float) $package->base_amount_per_person),
                'has_discount' => $this->hasDiscount($package),
                'items' => $items->map(fn (PackageItem $item) => [
                    'id' => $item->id,
                    'dish_title' => $item->dish?->title,
                    'variation_label' => $item->dishVariation?->label,
                ])->all(),
                'items_count' => $items->count(),
            ],
        ];
    }

    /**
     * @return array<string, mixed>
     */
    private function card(Package $package): array
    {
        $price = (float) $package->total_amount_per_person;

        return [
            'id' => $package->id,
            'title' => $package->title,
            'slug' => $package->slug,
            'short_description' => $package->short_description,
            'image_url' => $this->images->url($package->image),
            'price_per_person' => $price,
            'price_per_person_formatted' => $this->formatRs($price),
            'items_count' => (int) ($package->items_count ?? $package->items()->count()),
            'href' => route('packages.show', $package->slug, absolute: false),
        ];
    }

    private function hasDiscount(Package $package): bool
    {
        $base = (float) $package->base_amount_per_person;
        $total = (float) $package->total_amount_per_person;

        return $base > $total + 0.009;
    }

    private function formatRs(float $amount): string
    {
        return 'Rs. '.number_format($amount, 0);
    }
}
