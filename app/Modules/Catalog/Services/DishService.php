<?php

namespace App\Modules\Catalog\Services;

use App\Modules\Catalog\Models\Category;
use App\Modules\Catalog\Models\Dish;
use App\Modules\Catalog\Models\DishVariation;
use App\Support\Enums\VariationType;
use App\Support\Enums\VariationUnit;
use App\Support\ImageService;
use App\Support\Money;
use App\Support\StatusRegistry;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use InvalidArgumentException;

class DishService
{
    public function __construct(
        private readonly ImageService $images,
    ) {}

    /**
     * @param  array{
     *     search?: string|null,
     *     status?: string|null,
     *     category_id?: int|string|null,
     * }  $filters
     * @return LengthAwarePaginator<int, Dish>
     */
    public function paginate(array $filters = [], int $perPage = 15): LengthAwarePaginator
    {
        $query = Dish::query()
            ->with(['category', 'variations'])
            ->withCount('variations')
            ->orderBy('sort_order')
            ->orderBy('title');

        if (! empty($filters['search'])) {
            $search = trim((string) $filters['search']);
            $query->where(function ($q) use ($search): void {
                $q->where('title', 'like', "%{$search}%")
                    ->orWhere('slug', 'like', "%{$search}%")
                    ->orWhere('short_description', 'like', "%{$search}%")
                    ->orWhereHas('category', function ($cq) use ($search): void {
                        $cq->where('name', 'like', "%{$search}%");
                    });
            });
        }

        if (! empty($filters['status'])) {
            $query->where('status', $filters['status']);
        }

        if (! empty($filters['category_id'])) {
            $query->where('category_id', (int) $filters['category_id']);
        }

        return $query->paginate($perPage)->withQueryString();
    }

    /**
     * @param  array{
     *     category_id: int,
     *     title: string,
     *     short_description?: string|null,
     *     status: string,
     *     sort_order?: int|null,
     *     image?: UploadedFile|null,
     *     variations?: list<array<string, mixed>>,
     * }  $data
     */
    public function create(array $data): Dish
    {
        return DB::transaction(function () use ($data): Dish {
            $dish = new Dish([
                'category_id' => (int) $data['category_id'],
                'title' => $data['title'],
                'slug' => $this->uniqueSlug($data['title']),
                'short_description' => $data['short_description'] ?? null,
                'status' => $data['status'],
                'sort_order' => (int) ($data['sort_order'] ?? 0),
            ]);

            if (! empty($data['image']) && $data['image'] instanceof UploadedFile) {
                $dish->image = $this->images->store($data['image'], 'dishes');
            }

            $dish->save();
            $this->syncVariations($dish, $data['variations'] ?? []);

            return $dish->load(['category', 'variations'])->loadCount('variations');
        });
    }

    /**
     * @param  array{
     *     category_id: int,
     *     title: string,
     *     short_description?: string|null,
     *     status: string,
     *     sort_order?: int|null,
     *     image?: UploadedFile|null,
     *     remove_image?: bool,
     *     variations?: list<array<string, mixed>>,
     * }  $data
     */
    public function update(Dish $dish, array $data): Dish
    {
        return DB::transaction(function () use ($dish, $data): Dish {
            $dish->category_id = (int) $data['category_id'];
            $dish->title = $data['title'];
            $dish->slug = $this->uniqueSlug($data['title'], $dish->id);
            $dish->short_description = $data['short_description'] ?? null;
            $dish->status = $data['status'];
            $dish->sort_order = (int) ($data['sort_order'] ?? 0);

            if (! empty($data['remove_image'])) {
                $this->images->delete($dish->image);
                $dish->image = null;
            }

            if (! empty($data['image']) && $data['image'] instanceof UploadedFile) {
                $dish->image = $this->images->store(
                    $data['image'],
                    'dishes',
                    $dish->image,
                );
            }

            $dish->save();
            $this->syncVariations($dish, $data['variations'] ?? []);

            return $dish->load(['category', 'variations'])->loadCount('variations');
        });
    }

    public function delete(Dish $dish): void
    {
        if ($dish->packageItems()->exists()) {
            throw new InvalidArgumentException(
                'This dish is used in a package. Remove it from packages before deleting.',
            );
        }

        DB::transaction(function () use ($dish): void {
            $this->images->delete($dish->image);
            $dish->variations()->withTrashed()->forceDelete();
            $dish->delete();
        });
    }

    public function toggleActive(Dish $dish): Dish
    {
        $dish->status = $dish->status === 'active' ? 'inactive' : 'active';
        $dish->save();

        return $dish->load(['category', 'variations'])->loadCount('variations');
    }

    /**
     * @param  list<array{
     *     id?: int|null,
     *     label: string,
     *     quantity: float|int|string,
     *     unit: string,
     *     price: float|int|string,
     *     type: string,
     *     status?: string,
     *     sort_order?: int|null,
     * }>  $rows
     */
    public function syncVariations(Dish $dish, array $rows): void
    {
        $keepIds = [];

        foreach (array_values($rows) as $index => $row) {
            $payload = [
                'label' => trim((string) $row['label']),
                'quantity' => (float) $row['quantity'],
                'unit' => (string) $row['unit'],
                'price' => (float) $row['price'],
                'type' => (string) $row['type'],
                'status' => (string) ($row['status'] ?? 'active'),
                'sort_order' => (int) ($row['sort_order'] ?? $index + 1),
            ];

            $id = isset($row['id']) ? (int) $row['id'] : null;
            $variation = null;

            if ($id) {
                $variation = $dish->variations()->withTrashed()->whereKey($id)->first();
            }

            if (! $variation) {
                $variation = $dish->variations()
                    ->withTrashed()
                    ->where('label', $payload['label'])
                    ->first();
            }

            if ($variation) {
                if ($variation->trashed()) {
                    $variation->restore();
                }
                $variation->fill($payload)->save();
            } else {
                $variation = $dish->variations()->create($payload);
            }

            $keepIds[] = $variation->id;
        }

        $orphans = $dish->variations();
        if ($keepIds !== []) {
            $orphans->whereKeyNot($keepIds);
        }
        $orphans->get()->each(fn (DishVariation $variation) => $variation->forceDelete());
    }

    /**
     * @return array<string, mixed>
     */
    public function toArray(Dish $dish): array
    {
        $status = StatusRegistry::get('generic', $dish->status);
        $starting = $dish->variations
            ->where('type', VariationType::Single)
            ->where('status', 'active')
            ->sortBy('price')
            ->first()
            ?? $dish->variations->sortBy('price')->first();

        return [
            'id' => $dish->id,
            'category_id' => $dish->category_id,
            'category_name' => $dish->category?->name,
            'title' => $dish->title,
            'slug' => $dish->slug,
            'short_description' => $dish->short_description,
            'image' => $dish->image,
            'image_url' => $this->images->url($dish->image),
            'status' => $status['code'],
            'status_label' => $status['label'],
            'status_badge' => $status['badge'],
            'sort_order' => (int) $dish->sort_order,
            'variations_count' => (int) ($dish->variations_count ?? $dish->variations->count()),
            'starting_price' => $starting ? (float) $starting->price : null,
            'starting_price_formatted' => $starting
                ? Money::format((float) $starting->price)
                : null,
            'variations' => $dish->variations
                ->map(fn (DishVariation $variation) => $this->variationToArray($variation))
                ->values()
                ->all(),
            'created_at' => $dish->created_at?->toIso8601String(),
            'updated_at' => $dish->updated_at?->toIso8601String(),
        ];
    }

    /**
     * @return array<string, mixed>
     */
    public function variationToArray(DishVariation $variation): array
    {
        $status = StatusRegistry::get('generic', $variation->status);
        $type = $variation->type instanceof VariationType
            ? $variation->type
            : VariationType::from((string) $variation->type);

        return [
            'id' => $variation->id,
            'label' => $variation->label,
            'quantity' => (float) $variation->quantity,
            'unit' => (string) $variation->unit,
            'price' => (float) $variation->price,
            'price_formatted' => Money::format((float) $variation->price),
            'type' => $type->value,
            'type_label' => $type->label(),
            'cartable' => $type->isCartable(),
            'status' => $status['code'],
            'status_label' => $status['label'],
            'status_badge' => $status['badge'],
            'sort_order' => (int) $variation->sort_order,
        ];
    }

    /**
     * @return list<array{value: int, label: string}>
     */
    public function categoryOptions(): array
    {
        return Category::query()
            ->orderBy('sort_order')
            ->orderBy('name')
            ->get(['id', 'name'])
            ->map(fn (Category $category) => [
                'value' => $category->id,
                'label' => $category->name,
            ])
            ->values()
            ->all();
    }

    /**
     * @return list<array{value: string, label: string}>
     */
    public function unitOptions(): array
    {
        return array_map(
            fn (VariationUnit $unit) => [
                'value' => $unit->value,
                'label' => match ($unit) {
                    VariationUnit::Serving => 'Serving',
                    VariationUnit::Kg => 'KG',
                    VariationUnit::Persons => 'Persons',
                },
            ],
            VariationUnit::cases(),
        );
    }

    /**
     * @return list<array{value: string, label: string}>
     */
    public function typeOptions(): array
    {
        return array_map(
            fn (VariationType $type) => [
                'value' => $type->value,
                'label' => $type->label(),
            ],
            VariationType::cases(),
        );
    }

    protected function uniqueSlug(string $title, ?int $ignoreId = null): string
    {
        $base = Str::slug($title) ?: 'dish';
        $slug = $base;
        $i = 2;

        while (
            Dish::query()
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
