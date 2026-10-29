<?php

namespace App\Modules\PublicSite\Services;

use App\Modules\Catalog\Models\Dish;
use App\Modules\Packages\Models\Package;

class HomeService
{
    public function __construct(
        private readonly PublicDishPresenter $dishes,
    ) {}

    /**
     * @return array{
     *     featured_dishes: list<array<string, mixed>>,
     *     package_from_formatted: string|null,
     *     hero_image_url: string,
     *     trust_image_url: string
     * }
     */
    public function payload(): array
    {
        $dishes = Dish::query()
            ->active()
            ->with([
                'category:id,name,slug',
                'variations' => fn ($query) => $query
                    ->where('status', 'active')
                    ->orderBy('sort_order'),
            ])
            ->orderBy('sort_order')
            ->orderBy('title')
            ->limit(4)
            ->get();

        $featured = $dishes->map(fn (Dish $dish) => $this->dishes->card($dish))->values()->all();

        $packageFrom = Package::query()
            ->visibleOnWebsite()
            ->orderBy('total_amount_per_person')
            ->value('total_amount_per_person');

        return [
            'featured_dishes' => $featured,
            'package_from_formatted' => $packageFrom !== null
                ? 'Rs. '.number_format((float) $packageFrom, 0)
                : null,
            // Stitch home design assets (not catalog dish photos).
            'hero_image_url' => $this->designImageUrl('hero'),
            'trust_image_url' => $this->designImageUrl('trust'),
        ];
    }

    /**
     * Prefer WebP from public/images/home; fall back to PNG/JPG.
     */
    private function designImageUrl(string $name): string
    {
        foreach (['webp', 'png', 'jpg'] as $ext) {
            $relative = "images/home/{$name}.{$ext}";
            if (is_file(public_path($relative))) {
                return asset($relative);
            }
        }

        return asset("images/home/{$name}.webp");
    }
}
