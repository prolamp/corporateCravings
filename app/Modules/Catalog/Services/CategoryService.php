<?php

namespace App\Modules\Catalog\Services;

use App\Modules\Catalog\Models\Category;
use App\Support\ImageService;
use App\Support\StatusRegistry;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use InvalidArgumentException;

class CategoryService
{
    public function __construct(
        private readonly ImageService $images,
    ) {}

    /**
     * @param  array{search?: string|null, status?: string|null}  $filters
     * @return LengthAwarePaginator<int, Category>
     */
    public function paginate(array $filters = [], int $perPage = 15): LengthAwarePaginator
    {
        $query = Category::query()
            ->withCount('dishes')
            ->orderBy('sort_order')
            ->orderBy('name');

        if (! empty($filters['search'])) {
            $search = trim((string) $filters['search']);
            $query->where(function ($q) use ($search): void {
                $q->where('name', 'like', "%{$search}%")
                    ->orWhere('slug', 'like', "%{$search}%");
            });
        }

        if (! empty($filters['status'])) {
            $query->where('status', $filters['status']);
        }

        return $query->paginate($perPage)->withQueryString();
    }

    /**
     * @param  array{
     *     name: string,
     *     status: string,
     *     sort_order?: int|null,
     *     image?: UploadedFile|null,
     * }  $data
     */
    public function create(array $data): Category
    {
        return DB::transaction(function () use ($data): Category {
            $category = new Category([
                'name' => $data['name'],
                'slug' => $this->uniqueSlug($data['name']),
                'status' => $data['status'],
                'sort_order' => (int) ($data['sort_order'] ?? 0),
            ]);

            if (! empty($data['image']) && $data['image'] instanceof UploadedFile) {
                $category->image = $this->images->store($data['image'], 'categories');
            }

            $category->save();

            return $category->loadCount('dishes');
        });
    }

    /**
     * @param  array{
     *     name: string,
     *     status: string,
     *     sort_order?: int|null,
     *     image?: UploadedFile|null,
     *     remove_image?: bool,
     * }  $data
     */
    public function update(Category $category, array $data): Category
    {
        return DB::transaction(function () use ($category, $data): Category {
            $category->name = $data['name'];
            $category->slug = $this->uniqueSlug($data['name'], $category->id);
            $category->status = $data['status'];
            $category->sort_order = (int) ($data['sort_order'] ?? 0);

            if (! empty($data['remove_image'])) {
                $this->images->delete($category->image);
                $category->image = null;
            }

            if (! empty($data['image']) && $data['image'] instanceof UploadedFile) {
                $category->image = $this->images->store(
                    $data['image'],
                    'categories',
                    $category->image,
                );
            }

            $category->save();

            return $category->loadCount('dishes');
        });
    }

    public function delete(Category $category): void
    {
        if ($category->dishes()->exists()) {
            throw new InvalidArgumentException(
                'This category has dishes. Move or delete them before removing the category.',
            );
        }

        DB::transaction(function () use ($category): void {
            $this->images->delete($category->image);
            $category->delete();
        });
    }

    public function toggleActive(Category $category): Category
    {
        $category->status = $category->status === 'active' ? 'inactive' : 'active';
        $category->save();

        return $category->loadCount('dishes');
    }

    /**
     * @return array<string, mixed>
     */
    public function toArray(Category $category): array
    {
        $status = StatusRegistry::get('generic', $category->status);

        return [
            'id' => $category->id,
            'name' => $category->name,
            'slug' => $category->slug,
            'image' => $category->image,
            'image_url' => $this->images->url($category->image),
            'status' => $status['code'],
            'status_label' => $status['label'],
            'status_badge' => $status['badge'],
            'sort_order' => (int) $category->sort_order,
            'dishes_count' => (int) ($category->dishes_count ?? $category->dishes()->count()),
            'created_at' => $category->created_at?->toIso8601String(),
            'updated_at' => $category->updated_at?->toIso8601String(),
        ];
    }

    protected function uniqueSlug(string $name, ?int $ignoreId = null): string
    {
        $base = Str::slug($name) ?: 'category';
        $slug = $base;
        $i = 2;

        while (
            Category::query()
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
