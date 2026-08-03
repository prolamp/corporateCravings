<?php

namespace App\Modules\Packages\Services;

use App\Modules\Catalog\Models\Dish;
use App\Modules\Packages\Models\Package;
use App\Modules\Packages\Models\PackageItem;
use App\Support\Enums\VariationType;
use App\Support\ImageService;
use App\Support\Money;
use App\Support\StatusRegistry;
use Illuminate\Http\UploadedFile;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use InvalidArgumentException;

class PackageService
{
    public function __construct(
        private readonly ImageService $images,
    ) {}

    /**
     * @param  array{search?: string|null, status?: string|null}  $filters
     * @return LengthAwarePaginator<int, Package>
     */
    public function paginate(array $filters = [], int $perPage = 15): LengthAwarePaginator
    {
        $query = Package::query()
            ->withCount('items')
            ->orderBy('sort_order')
            ->orderBy('title');

        if (! empty($filters['search'])) {
            $search = trim((string) $filters['search']);
            $query->where(function ($q) use ($search): void {
                $q->where('title', 'like', "%{$search}%")
                    ->orWhere('slug', 'like', "%{$search}%")
                    ->orWhere('short_description', 'like', "%{$search}%");
            });
        }

        if (! empty($filters['status'])) {
            $query->where('status', $filters['status']);
        }

        return $query->paginate($perPage)->withQueryString();
    }

    /**
     * @param  array{
     *     title: string,
     *     short_description?: string|null,
     *     status: string,
     *     sort_order?: int|null,
     *     discount_amount?: float|int|string|null,
     *     discount_percent?: float|int|string|null,
     *     image?: UploadedFile|null,
     *     items: list<array{dish_id: int, dish_variation_id?: int|null}>,
     * }  $data
     */
    public function create(array $data): Package
    {
        return DB::transaction(function () use ($data): Package {
            $package = new Package([
                'title' => $data['title'],
                'slug' => $this->uniqueSlug($data['title']),
                'short_description' => $data['short_description'] ?? null,
                'status' => $data['status'],
                'sort_order' => (int) ($data['sort_order'] ?? 0),
                'discount_amount' => Money::of($data['discount_amount'] ?? 0),
                'discount_percent' => Money::of($data['discount_percent'] ?? 0),
                'base_amount_per_person' => Money::of(0),
                'total_amount_per_person' => Money::of(0),
            ]);

            if (! empty($data['image']) && $data['image'] instanceof UploadedFile) {
                $package->image = $this->images->store($data['image'], 'packages');
            }

            $package->save();
            $this->syncItemsAndPricing($package, $data['items'] ?? [], $data);

            return $package->load(['items.dish', 'items.dishVariation'])->loadCount('items');
        });
    }

    /**
     * @param  array{
     *     title: string,
     *     short_description?: string|null,
     *     status: string,
     *     sort_order?: int|null,
     *     discount_amount?: float|int|string|null,
     *     discount_percent?: float|int|string|null,
     *     image?: UploadedFile|null,
     *     remove_image?: bool,
     *     items: list<array{dish_id: int, dish_variation_id?: int|null}>,
     * }  $data
     */
    public function update(Package $package, array $data): Package
    {
        return DB::transaction(function () use ($package, $data): Package {
            $package->title = $data['title'];
            $package->slug = $this->uniqueSlug($data['title'], $package->id);
            $package->short_description = $data['short_description'] ?? null;
            $package->status = $data['status'];
            $package->sort_order = (int) ($data['sort_order'] ?? 0);
            $package->discount_amount = Money::of($data['discount_amount'] ?? 0);
            $package->discount_percent = Money::of($data['discount_percent'] ?? 0);

            if (! empty($data['remove_image'])) {
                $this->images->delete($package->image);
                $package->image = null;
            }

            if (! empty($data['image']) && $data['image'] instanceof UploadedFile) {
                $package->image = $this->images->store(
                    $data['image'],
                    'packages',
                    $package->image,
                );
            }

            $package->save();
            $this->syncItemsAndPricing($package, $data['items'] ?? [], $data);

            return $package->load(['items.dish', 'items.dishVariation'])->loadCount('items');
        });
    }

    public function delete(Package $package): void
    {
        if ($package->contracts()->exists()) {
            throw new InvalidArgumentException(
                'This package has subscriber contracts. End or reassign them before deleting.',
            );
        }

        DB::transaction(function () use ($package): void {
            $this->images->delete($package->image);
            $package->items()->delete();
            $package->delete();
        });
    }

    public function toggleActive(Package $package): Package
    {
        $package->status = $package->status === 'active' ? 'inactive' : 'active';
        $package->save();

        return $package->load(['items.dish', 'items.dishVariation'])->loadCount('items');
    }

    /**
     * @param  list<array{dish_id: int|string, dish_variation_id?: int|string|null}>  $rows
     * @param  array{discount_amount?: mixed, discount_percent?: mixed}  $data
     */
    public function syncItemsAndPricing(Package $package, array $rows, array $data): void
    {
        $seenDishIds = [];
        $keepIds = [];
        $unitPrices = [];

        foreach (array_values($rows) as $index => $row) {
            $dishId = (int) $row['dish_id'];
            if ($dishId <= 0 || isset($seenDishIds[$dishId])) {
                continue;
            }
            $seenDishIds[$dishId] = true;

            $dish = Dish::query()->with('singleVariations')->find($dishId);
            if (! $dish) {
                throw new InvalidArgumentException("Dish #{$dishId} was not found.");
            }

            $variationId = isset($row['dish_variation_id']) ? (int) $row['dish_variation_id'] : null;
            $variation = null;

            if ($variationId) {
                $variation = $dish->singleVariations->firstWhere('id', $variationId);
            }

            if (! $variation) {
                $variation = $dish->singleVariations
                    ->where('status', 'active')
                    ->sortBy('sort_order')
                    ->first()
                    ?? $dish->singleVariations->sortBy('sort_order')->first();
            }

            if (! $variation || $variation->type !== VariationType::Single) {
                throw new InvalidArgumentException(
                    "{$dish->title} needs an active single-serving variation for package pricing.",
                );
            }

            $unitPrice = Money::of($variation->price);
            $unitPrices[] = $unitPrice;

            $item = $package->items()->where('dish_id', $dishId)->first();
            $payload = [
                'dish_id' => $dishId,
                'dish_variation_id' => $variation->id,
                'unit_price' => $unitPrice,
                'sort_order' => $index + 1,
            ];

            if ($item) {
                $item->fill($payload)->save();
            } else {
                $item = $package->items()->create($payload);
            }

            $keepIds[] = $item->id;
        }

        if ($keepIds === []) {
            throw new InvalidArgumentException('A package needs at least one dish.');
        }

        $package->items()
            ->whereKeyNot($keepIds)
            ->delete();

        $sum = Money::add(...$unitPrices);
        $count = count($unitPrices);
        $base = Money::of((float) $sum / $count);
        $discountAmount = Money::of($data['discount_amount'] ?? $package->discount_amount ?? 0);
        $discountPercent = Money::of($data['discount_percent'] ?? $package->discount_percent ?? 0);
        $percentOff = Money::percentOf($base, $discountPercent);
        $discountTotal = Money::add($discountAmount, $percentOff);
        $total = Money::of(max(0, (float) Money::subtract($base, $discountTotal)));

        $package->forceFill([
            'base_amount_per_person' => $base,
            'discount_amount' => $discountAmount,
            'discount_percent' => $discountPercent,
            'total_amount_per_person' => $total,
        ])->save();
    }

    /**
     * @return array<string, mixed>
     */
    public function toArray(Package $package): array
    {
        $status = StatusRegistry::get('generic', $package->status);
        $items = $package->relationLoaded('items')
            ? $package->items
            : $package->items()->with(['dish', 'dishVariation'])->get();

        return [
            'id' => $package->id,
            'title' => $package->title,
            'slug' => $package->slug,
            'short_description' => $package->short_description,
            'image' => $package->image,
            'image_url' => $this->images->url($package->image),
            'base_amount_per_person' => (float) $package->base_amount_per_person,
            'base_amount_per_person_formatted' => Money::format((float) $package->base_amount_per_person),
            'discount_amount' => (float) $package->discount_amount,
            'discount_percent' => (float) $package->discount_percent,
            'total_amount_per_person' => (float) $package->total_amount_per_person,
            'total_amount_per_person_formatted' => Money::format((float) $package->total_amount_per_person),
            'status' => $status['code'],
            'status_label' => $status['label'],
            'status_badge' => $status['badge'],
            'sort_order' => (int) $package->sort_order,
            'items_count' => (int) ($package->items_count ?? $items->count()),
            'items' => $items->map(fn (PackageItem $item) => [
                'id' => $item->id,
                'dish_id' => $item->dish_id,
                'dish_title' => $item->dish?->title,
                'dish_variation_id' => $item->dish_variation_id,
                'unit_price' => (float) $item->unit_price,
                'unit_price_formatted' => Money::format((float) $item->unit_price),
                'sort_order' => (int) $item->sort_order,
            ])->values()->all(),
            'created_at' => $package->created_at?->toIso8601String(),
            'updated_at' => $package->updated_at?->toIso8601String(),
        ];
    }

    /**
     * Dishes with an active single variation for package builders.
     *
     * @return list<array{value: int, label: string, unit_price: float, unit_price_formatted: string, dish_variation_id: int}>
     */
    public function dishOptions(): array
    {
        return Dish::query()
            ->with(['singleVariations' => fn ($q) => $q->where('status', 'active')->orderBy('sort_order')])
            ->orderBy('sort_order')
            ->orderBy('title')
            ->get()
            ->map(function (Dish $dish) {
                $variation = $dish->singleVariations->first();
                if (! $variation) {
                    return null;
                }

                return [
                    'value' => $dish->id,
                    'label' => $dish->title,
                    'unit_price' => (float) $variation->price,
                    'unit_price_formatted' => Money::format((float) $variation->price),
                    'dish_variation_id' => $variation->id,
                ];
            })
            ->filter()
            ->values()
            ->all();
    }

    protected function uniqueSlug(string $title, ?int $ignoreId = null): string
    {
        $base = Str::slug($title) ?: 'package';
        $slug = $base;
        $i = 2;

        while (
            Package::query()
                ->when($ignoreId, fn ($q) => $q->whereKeyNot($ignoreId))
                ->where('slug', $slug)
                ->withTrashed()
                ->exists()
        ) {
            $slug = "{$base}-{$i}";
            $i++;
        }

        return $slug;
    }
}
