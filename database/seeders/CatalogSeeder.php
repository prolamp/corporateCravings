<?php

namespace Database\Seeders;

use App\Modules\Catalog\Models\Category;
use App\Modules\Catalog\Models\Dish;
use App\Modules\Catalog\Models\DishVariation;
use App\Support\Enums\VariationType;
use App\Support\Enums\VariationUnit;
use App\Support\ImageService;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

class CatalogSeeder extends Seeder
{
    /**
     * New food photography from Stitch (docs/stitch/.../screen.png).
     * Falls back to the old uploads tree only when a Stitch asset is missing.
     */
    private const STITCH_BASE = 'docs/stitch';

    private const OLD_BASE = '/var/www/corporate-craving-final/public/uploads/images';

    public function __construct(private readonly ImageService $images) {}

    public function run(): void
    {
        $categories = [
            ['name' => 'Rice', 'slug' => 'rice', 'stitch' => 'authentic_pakistani_chicken_biryani_in_a_traditional_copper_handi_garnished'],
            ['name' => 'Chicken', 'slug' => 'chicken', 'stitch' => 'a_steaming_bowl_of_chicken_karahi_with_fresh_ginger_julienne_and_green_chilies.'],
            ['name' => 'Beef', 'slug' => 'beef', 'stitch' => 'kabuli_pulao_with_large_pieces_of_meat_raisins_and_shredded_carrots.'],
            ['name' => 'Mutton', 'slug' => 'mutton', 'stitch' => 'professional_food_photography_of_afgani_pulao_with_succulent_meat_carrots_and'],
            ['name' => 'Vegetable', 'slug' => 'vegetable', 'stitch' => 'a_platter_of_mixed_vegetable_curry_aloo_gobi_with_fresh_coriander._professional'],
            ['name' => 'Daal', 'slug' => 'daal', 'stitch' => 'professional_food_photography_of_daal_mash_white_lentils_garnished_with_green'],
            ['name' => 'Salad', 'slug' => 'salad', 'stitch' => 'professional_food_photography_of_fresh_green_salad_with_cucumbers_tomatoes_and'],
            ['name' => 'BBQ', 'slug' => 'bbq', 'stitch' => 'a_selection_of_bbq_seekh_kababs_and_malai_boti_on_a_platter_with_mint_chutney.'],
            ['name' => 'Dessert', 'slug' => 'dessert', 'stitch' => 'a_beautiful_plate_of_shahi_zarda_sweet_yellow_rice_garnished_with_nuts_raisins'],
        ];

        $categoryIds = [];
        foreach ($categories as $index => $row) {
            $existing = Category::query()->where('slug', $row['slug'])->first();
            $image = $this->importStitchImage($row['stitch'], 'categories', $existing?->image)
                ?? $this->importOldImage('category/'.$row['slug'].'.webp', 'categories', $existing?->image);

            $attributes = [
                'name' => $row['name'],
                'status' => 'active',
                'sort_order' => $index + 1,
            ];
            if ($image) {
                $attributes['image'] = $image;
            }

            $category = Category::query()->updateOrCreate(
                ['slug' => $row['slug']],
                $attributes,
            );
            $categoryIds[$row['name']] = $category->id;
        }

        foreach ($this->dishes() as $index => $row) {
            $categoryId = $categoryIds[$row['category']] ?? null;
            if (! $categoryId) {
                continue;
            }

            $existing = Dish::query()->where('title', $row['title'])->first();
            $image = $this->importStitchImage($row['stitch'], 'dishes', $existing?->image)
                ?? $this->importOldImage('product/'.$row['legacy'], 'dishes', $existing?->image);

            $attributes = [
                'category_id' => $categoryId,
                'slug' => Str::slug($row['title']),
                'short_description' => Str::limit($row['description'], 480),
                'status' => 'active',
                'sort_order' => $index + 1,
            ];
            if ($image) {
                $attributes['image'] = $image;
            }

            $dish = Dish::query()->updateOrCreate(
                ['title' => $row['title']],
                $attributes,
            );

            $variations = $this->variationsFor($row['title']);
            foreach ($variations as $vIndex => $variation) {
                DishVariation::query()->updateOrCreate(
                    [
                        'dish_id' => $dish->id,
                        'label' => $variation['label'],
                    ],
                    [
                        'quantity' => $variation['quantity'],
                        'unit' => $variation['unit'],
                        'price' => $variation['price'],
                        'type' => $variation['type'],
                        'status' => 'active',
                        'sort_order' => $vIndex + 1,
                    ],
                );
            }
        }
    }

    private function importStitchImage(string $folder, string $directory, ?string $oldPath = null): ?string
    {
        $source = base_path(self::STITCH_BASE.'/'.$folder.'/screen.png');

        if (! is_file($source)) {
            $this->command?->warn("Missing Stitch image: {$folder}/screen.png");

            return null;
        }

        try {
            return $this->images->storeFromPath($source, $directory, $oldPath);
        } catch (\Throwable $e) {
            $this->command?->warn("Stitch image import failed ({$folder}): ".$e->getMessage());

            return null;
        }
    }

    private function importOldImage(string $relative, string $directory, ?string $oldPath = null): ?string
    {
        $source = self::OLD_BASE.'/'.$relative;
        if (! is_file($source)) {
            $base = preg_replace('/\.(webp|png|jpg|jpeg)$/i', '', $source);
            foreach (['.webp', '.png', '.jpg', '.jpeg'] as $ext) {
                if (is_file($base.$ext)) {
                    $source = $base.$ext;
                    break;
                }
            }
        }

        if (! is_file($source)) {
            $this->command?->warn("Missing fallback image: {$relative}");

            return null;
        }

        try {
            return $this->images->storeFromPath($source, $directory, $oldPath);
        } catch (\Throwable $e) {
            $this->command?->warn("Fallback image import failed ({$relative}): ".$e->getMessage());

            return null;
        }
    }

    /**
     * @return list<array{title: string, category: string, stitch: string, legacy: string, description: string}>
     */
    private function dishes(): array
    {
        return [
            ['title' => 'Chicken Biryani', 'category' => 'Rice', 'stitch' => 'authentic_pakistani_chicken_biryani_in_a_traditional_copper_handi_garnished', 'legacy' => 'chicken_biryani.webp', 'description' => 'Aromatic basmati rice cooked with tender chicken pieces, fragrant spices, and herbs.'],
            ['title' => 'Chicken Pulao', 'category' => 'Rice', 'stitch' => 'professional_food_photography_of_chicken_pulao_pakistani_style_with_aromatic', 'legacy' => 'chicken_pulao.webp', 'description' => 'Fragrant basmati rice cooked with tender chicken pieces, aromatic spices, and saffron.'],
            ['title' => 'Vegetable Pulao', 'category' => 'Rice', 'stitch' => 'professional_food_photography_of_vegetable_pulao_with_colorful_carrots_peas_and', 'legacy' => 'vegetable_pulao.webp', 'description' => 'Fragrant basmati rice cooked with mixed vegetables, aromatic spices, and saffron.'],
            ['title' => 'Chana Pulao', 'category' => 'Rice', 'stitch' => 'professional_food_photography_of_chana_pulao_chickpea_rice_garnished_with_fried', 'legacy' => 'chana_pulao.webp', 'description' => 'Fragrant basmati rice cooked with tender chickpeas, aromatic spices, and saffron.'],
            ['title' => 'Chicken Manchurian', 'category' => 'Rice', 'stitch' => 'professional_food_photography_of_chicken_manchurian_in_a_rich_red_gravy_served', 'legacy' => 'chicken_manchurian.webp', 'description' => 'Savory chicken manchurian style rice dish with aromatic spices.'],
            ['title' => 'Daal Chawal', 'category' => 'Rice', 'stitch' => 'traditional_daal_chawal_presentation_a_bowl_of_yellow_lentil_curry_next_to_a', 'legacy' => 'daal_chawal.webp', 'description' => 'Comforting rice served with seasoned lentils.'],
            ['title' => 'Afgani Pulao', 'category' => 'Rice', 'stitch' => 'professional_food_photography_of_afgani_pulao_with_succulent_meat_carrots_and', 'legacy' => 'afghani_pulao.webp', 'description' => 'Fragrant Afghani-style pulao with aromatic spices.'],
            ['title' => 'Kabuli Pulao', 'category' => 'Rice', 'stitch' => 'kabuli_pulao_with_large_pieces_of_meat_raisins_and_shredded_carrots.', 'legacy' => 'kabuli_pulao.webp', 'description' => 'Classic Kabuli pulao with rich flavors and garnish.'],
            ['title' => 'Chicken Karahi', 'category' => 'Chicken', 'stitch' => 'a_steaming_bowl_of_chicken_karahi_with_fresh_ginger_julienne_and_green_chilies.', 'legacy' => 'chicken_karahi.webp', 'description' => 'Spicy chicken karahi cooked with tomatoes, green chilies, and fresh herbs.'],
            ['title' => 'Chicken Qorma', 'category' => 'Chicken', 'stitch' => 'professional_food_photography_of_chicken_qorma_with_a_rich_nut_based_gravy_and', 'legacy' => 'chicken_qorma.webp', 'description' => 'Rich chicken qorma with traditional spices.'],
            ['title' => 'Matter Qeema', 'category' => 'Chicken', 'stitch' => 'professional_food_photography_of_matar_qeema_minced_meat_with_peas_in_a', 'legacy' => 'matter_qeema.webp', 'description' => 'Minced meat cooked with green peas and spices.'],
            ['title' => 'Mixed Vegetable', 'category' => 'Vegetable', 'stitch' => 'a_platter_of_mixed_vegetable_curry_aloo_gobi_with_fresh_coriander._professional', 'legacy' => 'mixed_vegetable.webp', 'description' => 'Fresh seasonal vegetables cooked with garlic, ginger, and spices.'],
            ['title' => 'Aloo Gobi', 'category' => 'Vegetable', 'stitch' => 'professional_food_photography_of_aloo_gobi_potato_and_cauliflower_with_turmeric', 'legacy' => 'aloo_ghobi.webp', 'description' => 'Potato and cauliflower cooked with traditional spices.'],
            ['title' => 'Aloo Bhujia', 'category' => 'Vegetable', 'stitch' => 'professional_food_photography_of_aloo_bhujia_spiced_potato_strips_served_in_a', 'legacy' => 'aloo_bhujia.webp', 'description' => 'Spiced potato bhujia, a comforting side dish.'],
            ['title' => 'Aloo Anda', 'category' => 'Vegetable', 'stitch' => 'professional_food_photography_of_aloo_anda_potato_and_egg_curry_with_boiled', 'legacy' => 'aloo_anda.webp', 'description' => 'Potatoes and eggs cooked in a savory masala.'],
            ['title' => 'Daal Kadu', 'category' => 'Daal', 'stitch' => 'professional_food_photography_of_daal_kadu_lentils_with_bottle_gourd._simple', 'legacy' => 'daal_kado.webp', 'description' => 'Lentils cooked with bottle gourd and spices.'],
            ['title' => 'Daal Chana', 'category' => 'Daal', 'stitch' => 'professional_food_photography_of_daal_chana_split_chickpeas_with_a_spicy_tadka', 'legacy' => 'daal_chana.webp', 'description' => 'Chickpea lentils cooked with traditional tempering.'],
            ['title' => 'Daal Mash', 'category' => 'Daal', 'stitch' => 'professional_food_photography_of_daal_mash_white_lentils_garnished_with_green', 'legacy' => 'daal_mash.webp', 'description' => 'White lentils cooked with aromatic spices.'],
            ['title' => 'Daal Mixed', 'category' => 'Daal', 'stitch' => 'professional_food_photography_of_daal_mash_white_lentils_garnished_with_green', 'legacy' => 'daal_mixed.webp', 'description' => 'Mixed lentils cooked with traditional spices.'],
            ['title' => 'Lahori Chana', 'category' => 'Daal', 'stitch' => 'professional_food_photography_of_lahori_chana_chickpea_curry_in_a_rich_dark', 'legacy' => 'lahori_chana.webp', 'description' => 'Lahori-style chickpeas with bold spices.'],
            ['title' => 'Lobia', 'category' => 'Daal', 'stitch' => 'professional_food_photography_of_lobia_black_eyed_pea_curry_in_a_tomato_based', 'legacy' => 'lobia.webp', 'description' => 'Black-eyed beans cooked with traditional masala.'],
            ['title' => 'Russian Salad', 'category' => 'Salad', 'stitch' => 'a_vibrant_russian_salad_with_creamy_dressing_peas_and_carrots_in_a_glass_bowl.', 'legacy' => 'russian_salad.webp', 'description' => 'Creamy Russian salad with fresh vegetables.'],
            ['title' => 'Fresh Salad', 'category' => 'Salad', 'stitch' => 'professional_food_photography_of_fresh_green_salad_with_cucumbers_tomatoes_and', 'legacy' => 'fresh_salad.webp', 'description' => 'Fresh garden salad with seasonal vegetables.'],
            ['title' => 'Special Salad', 'category' => 'Salad', 'stitch' => 'professional_food_photography_of_fresh_green_salad_with_cucumbers_tomatoes_and', 'legacy' => 'special_salad.webp', 'description' => 'House special salad with premium toppings.'],
            ['title' => 'Kachumer Salad', 'category' => 'Salad', 'stitch' => 'professional_food_photography_of_kachumer_salad_finely_chopped_tomatoes_onions', 'legacy' => 'kachmer_sulad.webp', 'description' => 'Classic kachumber salad with onion, tomato, and cucumber.'],
            ['title' => 'Kheer', 'category' => 'Dessert', 'stitch' => 'professional_food_photography_of_pakistani_kheer_rice_pudding_in_traditional', 'legacy' => 'kheer.webp', 'description' => 'Traditional rice pudding dessert.'],
            ['title' => 'Custard', 'category' => 'Dessert', 'stitch' => 'professional_food_photography_of_fruit_custard_with_layers_of_jelly_and_cake', 'legacy' => 'custard.webp', 'description' => 'Creamy custard dessert.'],
        ];
    }

    /**
     * Every dish is priced as one single serving plus three bulk kg tiers,
     * so only the four amounts differ per dish: [serving, 5kg, 8kg, 10kg].
     *
     * @return list<array{label: string, quantity: float, unit: string, price: float, type: string}>
     */
    private function variationsFor(string $title): array
    {
        $tiers = [
            'Chicken Biryani' => [600, 12000, 15500, 18000],
            'Chicken Pulao' => [550, 11000, 15500, 17000],
            'Vegetable Pulao' => [375, 7500, 9500, 11000],
            'Chana Pulao' => [400, 7500, 9500, 11000],
            'Chicken Manchurian' => [500, 10000, 15000, 18000],
            'Daal Chawal' => [400, 8000, 11000, 13000],
            'Kabuli Pulao' => [750, 15000, 22000, 25000],
            'Chicken Karahi' => [400, 7000, 11200, 14000],
            'Chicken Qorma' => [375, 6500, 10400, 13000],
            'Matter Qeema' => [425, 9000, 13000, 15000],
            'Mixed Vegetable' => [300, 5000, 8000, 10000],
        ];

        [$serving, $fiveKg, $eightKg, $tenKg] = $tiers[$title] ?? [350, 4500, 7000, 8500];

        return [
            $this->single($serving),
            $this->bulk(5, $fiveKg),
            $this->bulk(8, $eightKg),
            $this->bulk(10, $tenKg),
        ];
    }

    /**
     * @return array{label: string, quantity: float, unit: string, price: float, type: string}
     */
    private function single(float $price): array
    {
        return [
            'label' => '1 Serving',
            'quantity' => 1,
            'unit' => VariationUnit::Serving->value,
            'price' => $price,
            'type' => VariationType::Single->value,
        ];
    }

    /**
     * @return array{label: string, quantity: float, unit: string, price: float, type: string}
     */
    private function bulk(float $kg, float $price): array
    {
        return [
            'label' => (int) $kg.' KG',
            'quantity' => $kg,
            'unit' => VariationUnit::Kg->value,
            'price' => $price,
            'type' => VariationType::Bulk->value,
        ];
    }
}
