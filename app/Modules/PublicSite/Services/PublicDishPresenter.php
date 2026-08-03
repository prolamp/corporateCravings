<?php

namespace App\Modules\PublicSite\Services;

use App\Modules\Catalog\Models\Dish;
use App\Support\Enums\VariationType;
use App\Support\ImageService;

class PublicDishPresenter
{
    public function __construct(
        private readonly ImageService $images,
    ) {}

    /**
     * Public menu/home card. Starting price prefers active single serving.
     *
     * @return array<string, mixed>
     */
    public function card(Dish $dish): array
    {
        $starting = $dish->variations
            ->where('type', VariationType::Single)
            ->sortBy('price')
            ->first();

        return [
            'id' => $dish->id,
            'title' => $dish->title,
            'slug' => $dish->slug,
            'short_description' => $dish->short_description,
            'category_id' => $dish->category_id,
            'category_name' => $dish->category?->name,
            'category_slug' => $dish->category?->slug,
            'image_url' => $this->images->url($dish->image),
            'starting_price' => $starting ? (float) $starting->price : null,
            'starting_price_formatted' => $starting
                ? 'Rs. '.number_format((float) $starting->price, 0)
                : null,
            'href' => '/menu/'.$dish->slug,
        ];
    }
}
