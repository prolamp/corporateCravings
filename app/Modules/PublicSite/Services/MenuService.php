<?php

namespace App\Modules\PublicSite\Services;

use App\Modules\Catalog\Models\Category;
use App\Modules\Catalog\Models\Dish;
use App\Support\Enums\VariationType;

class MenuService
{
    public function __construct(
        private readonly PublicDishPresenter $dishes,
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
                'dishes as menu_dishes_count' => function ($query): void {
                    $query->active()
                        ->whereHas('variations', function ($variations): void {
                            $variations
                                ->where('status', 'active')
                                ->where('type', VariationType::Single);
                        });
                },
            ])
            ->orderBy('sort_order')
            ->orderBy('name')
            ->get(['id', 'name', 'slug'])
            ->filter(fn (Category $category) => (int) $category->menu_dishes_count > 0)
            ->map(fn (Category $category) => [
                'id' => $category->id,
                'name' => $category->name,
                'slug' => $category->slug,
                'dishes_count' => (int) $category->menu_dishes_count,
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
                    ->where('type', VariationType::Single);
            })
            ->with([
                'category:id,name,slug',
                'variations' => fn ($variations) => $variations
                    ->where('status', 'active')
                    ->where('type', VariationType::Single)
                    ->orderBy('sort_order'),
            ])
            ->orderBy('sort_order')
            ->orderBy('title');

        if ($categorySlug !== null) {
            $query->whereHas('category', fn ($category) => $category->where('slug', $categorySlug));
        }

        $dishes = $query->get()
            ->map(fn (Dish $dish) => $this->dishes->card($dish))
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
}
