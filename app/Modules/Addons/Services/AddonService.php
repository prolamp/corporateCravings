<?php

namespace App\Modules\Addons\Services;

use App\Modules\Addons\Models\Addon;
use App\Modules\Catalog\Models\Dish;
use App\Support\Enums\AddonType;
use App\Support\ImageService;
use App\Support\Money;
use App\Support\StatusRegistry;
use Illuminate\Http\UploadedFile;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class AddonService
{
    public function __construct(
        private readonly ImageService $images,
    ) {}

    /**
     * @param  array{
     *     search?: string|null,
     *     status?: string|null,
     *     type?: string|null,
     *     scope?: string|null,
     * }  $filters
     * @return LengthAwarePaginator<int, Addon>
     */
    public function paginate(array $filters = [], int $perPage = 15): LengthAwarePaginator
    {
        $query = Addon::query()
            ->withCount('dishes')
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

        if (! empty($filters['type'])) {
            $query->where('type', $filters['type']);
        }

        if (($filters['scope'] ?? null) === 'global') {
            $query->where('is_global', true);
        } elseif (($filters['scope'] ?? null) === 'linked') {
            $query->where('is_global', false);
        }

        return $query->paginate($perPage)->withQueryString();
    }

    /**
     * @param  array{
     *     title: string,
     *     short_description?: string|null,
     *     type: string,
     *     price: float|int|string,
     *     is_global: bool,
     *     status: string,
     *     sort_order?: int|null,
     *     image?: UploadedFile|null,
     *     dish_ids?: list<int>|null,
     * }  $data
     */
    public function create(array $data): Addon
    {
        return DB::transaction(function () use ($data): Addon {
            $addon = new Addon([
                'title' => $data['title'],
                'slug' => $this->uniqueSlug($data['title']),
                'short_description' => $data['short_description'] ?? null,
                'type' => $data['type'],
                'price' => (float) $data['price'],
                'is_global' => (bool) $data['is_global'],
                'status' => $data['status'],
                'sort_order' => (int) ($data['sort_order'] ?? 0),
            ]);

            if (! empty($data['image']) && $data['image'] instanceof UploadedFile) {
                $addon->image = $this->images->store($data['image'], 'addons');
            }

            $addon->save();
            $this->syncDishes($addon, $data);

            return $addon->load('dishes')->loadCount('dishes');
        });
    }

    /**
     * @param  array{
     *     title: string,
     *     short_description?: string|null,
     *     type: string,
     *     price: float|int|string,
     *     is_global: bool,
     *     status: string,
     *     sort_order?: int|null,
     *     image?: UploadedFile|null,
     *     remove_image?: bool,
     *     dish_ids?: list<int>|null,
     * }  $data
     */
    public function update(Addon $addon, array $data): Addon
    {
        return DB::transaction(function () use ($addon, $data): Addon {
            $addon->title = $data['title'];
            $addon->slug = $this->uniqueSlug($data['title'], $addon->id);
            $addon->short_description = $data['short_description'] ?? null;
            $addon->type = $data['type'];
            $addon->price = (float) $data['price'];
            $addon->is_global = (bool) $data['is_global'];
            $addon->status = $data['status'];
            $addon->sort_order = (int) ($data['sort_order'] ?? 0);

            if (! empty($data['remove_image'])) {
                $this->images->delete($addon->image);
                $addon->image = null;
            }

            if (! empty($data['image']) && $data['image'] instanceof UploadedFile) {
                $addon->image = $this->images->store(
                    $data['image'],
                    'addons',
                    $addon->image,
                );
            }

            $addon->save();
            $this->syncDishes($addon, $data);

            return $addon->load('dishes')->loadCount('dishes');
        });
    }

    public function delete(Addon $addon): void
    {
        DB::transaction(function () use ($addon): void {
            $this->images->delete($addon->image);
            $addon->dishes()->detach();
            $addon->delete();
        });
    }

    public function toggleActive(Addon $addon): Addon
    {
        $addon->status = $addon->status === 'active' ? 'inactive' : 'active';
        $addon->save();

        return $addon->load('dishes')->loadCount('dishes');
    }

    /**
     * @param  array{is_global: bool, dish_ids?: list<int>|null}  $data
     */
    protected function syncDishes(Addon $addon, array $data): void
    {
        if ($addon->is_global) {
            $addon->dishes()->detach();

            return;
        }

        $ids = collect($data['dish_ids'] ?? [])
            ->map(fn ($id) => (int) $id)
            ->filter(fn (int $id) => $id > 0)
            ->unique()
            ->values()
            ->all();

        $addon->dishes()->sync($ids);
    }

    /**
     * @return array<string, mixed>
     */
    public function toArray(Addon $addon): array
    {
        $status = StatusRegistry::get('generic', $addon->status);
        $type = AddonType::tryFrom((string) $addon->type) ?? AddonType::Extra;

        return [
            'id' => $addon->id,
            'title' => $addon->title,
            'slug' => $addon->slug,
            'short_description' => $addon->short_description,
            'type' => $type->value,
            'type_label' => $type->label(),
            'price' => (float) $addon->price,
            'price_formatted' => Money::format((float) $addon->price),
            'image' => $addon->image,
            'image_url' => $this->images->url($addon->image),
            'is_global' => (bool) $addon->is_global,
            'status' => $status['code'],
            'status_label' => $status['label'],
            'status_badge' => $status['badge'],
            'sort_order' => (int) $addon->sort_order,
            'dishes_count' => (int) ($addon->dishes_count ?? $addon->dishes()->count()),
            'dish_ids' => $addon->relationLoaded('dishes')
                ? $addon->dishes->pluck('id')->map(fn ($id) => (int) $id)->values()->all()
                : $addon->dishes()->pluck('dishes.id')->map(fn ($id) => (int) $id)->values()->all(),
            'dish_names' => $addon->relationLoaded('dishes')
                ? $addon->dishes->pluck('title')->values()->all()
                : [],
            'created_at' => $addon->created_at?->toIso8601String(),
            'updated_at' => $addon->updated_at?->toIso8601String(),
        ];
    }

    /**
     * @return list<array{value: int, label: string}>
     */
    public function dishOptions(): array
    {
        return Dish::query()
            ->orderBy('sort_order')
            ->orderBy('title')
            ->get(['id', 'title'])
            ->map(fn (Dish $dish) => [
                'value' => $dish->id,
                'label' => $dish->title,
            ])
            ->values()
            ->all();
    }

    /**
     * @return list<array{value: string, label: string}>
     */
    public function typeOptions(): array
    {
        return array_map(
            fn (AddonType $type) => [
                'value' => $type->value,
                'label' => $type->label(),
            ],
            AddonType::cases(),
        );
    }

    protected function uniqueSlug(string $title, ?int $ignoreId = null): string
    {
        $base = Str::slug($title) ?: 'addon';
        $slug = $base;
        $i = 2;

        while (
            Addon::query()
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
