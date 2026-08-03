<?php

namespace App\Modules\PublicSite\Services;

use App\Modules\Catalog\Models\Category;
use App\Modules\Catalog\Models\Dish;
use App\Modules\Catalog\Models\DishVariation;
use App\Support\Enums\VariationType;
use App\Support\ImageService;

class BulkOrdersService
{
    public function __construct(
        private readonly ImageService $images,
    ) {}

    /**
     * @param  array{category?: string|null}  $filters
     * @return array{
     *     filters: array{category: string|null},
     *     categories: list<array{id: int, name: string, slug: string, dishes_count: int}>,
     *     dishes: list<array<string, mixed>>,
     *     dishes_count: int
     * }
     */
    public function payload(array $filters = []): array
    {
        $categorySlug = filled($filters['category'] ?? null)
            ? (string) $filters['category']
            : null;

        $categories = Category::query()
            ->active()
            ->withCount([
                'dishes as bulk_dishes_count' => function ($query): void {
                    $query->active()
                        ->whereHas('variations', function ($variations): void {
                            $variations
                                ->where('status', 'active')
                                ->where('type', VariationType::Bulk);
                        });
                },
            ])
            ->orderBy('sort_order')
            ->orderBy('name')
            ->get(['id', 'name', 'slug'])
            ->filter(fn (Category $category) => (int) $category->bulk_dishes_count > 0)
            ->map(fn (Category $category) => [
                'id' => $category->id,
                'name' => $category->name,
                'slug' => $category->slug,
                'dishes_count' => (int) $category->bulk_dishes_count,
            ])
            ->values()
            ->all();

        $validSlugs = collect($categories)->pluck('slug')->all();
        if ($categorySlug !== null && ! in_array($categorySlug, $validSlugs, true)) {
            $categorySlug = null;
        }

        $query = Dish::query()
            ->active()
            ->whereHas('variations', function ($variations): void {
                $variations
                    ->where('status', 'active')
                    ->where('type', VariationType::Bulk);
            })
            ->with([
                'category:id,name,slug',
                'variations' => fn ($variations) => $variations
                    ->where('status', 'active')
                    ->where('type', VariationType::Bulk)
                    ->orderBy('sort_order')
                    ->orderBy('price'),
            ])
            ->orderBy('sort_order')
            ->orderBy('title');

        if ($categorySlug !== null) {
            $query->whereHas('category', fn ($category) => $category->where('slug', $categorySlug));
        }

        $dishes = $query->get()
            ->map(fn (Dish $dish) => $this->card($dish))
            ->values()
            ->all();

        return [
            'filters' => [
                'category' => $categorySlug,
            ],
            'categories' => $categories,
            'dishes' => $dishes,
            'dishes_count' => count($dishes),
        ];
    }

    /**
     * @return array<string, mixed>
     */
    private function card(Dish $dish): array
    {
        $tiers = $dish->variations
            ->map(fn (DishVariation $variation) => [
                'id' => $variation->id,
                'label' => $variation->label,
                'quantity' => (float) $variation->quantity,
                'unit' => (string) $variation->unit,
                'price' => (float) $variation->price,
                'price_formatted' => $this->formatRs((float) $variation->price),
            ])
            ->values()
            ->all();

        $starting = collect($tiers)->sortBy('price')->first();

        return [
            'id' => $dish->id,
            'title' => $dish->title,
            'slug' => $dish->slug,
            'short_description' => $dish->short_description,
            'category_name' => $dish->category?->name,
            'category_slug' => $dish->category?->slug,
            'image_url' => $this->images->url($dish->image),
            'starting_price' => $starting['price'] ?? null,
            'starting_price_formatted' => $starting['price_formatted'] ?? null,
            'tiers' => $tiers,
            'tiers_count' => count($tiers),
            'href' => '/menu/'.$dish->slug,
            'cartable' => false,
        ];
    }

    private function formatRs(float $amount): string
    {
        return 'Rs. '.number_format($amount, 0);
    }
}
