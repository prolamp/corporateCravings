<?php

namespace App\Modules\Cms\Services;

use App\Models\Page;
use App\Support\StatusRegistry;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class PageService
{
    /**
     * @param  array{search?: string|null, status?: string|null}  $filters
     * @return LengthAwarePaginator<int, Page>
     */
    public function paginate(array $filters = [], int $perPage = 15): LengthAwarePaginator
    {
        $query = Page::query()->orderBy('title');

        if (! empty($filters['search'])) {
            $search = trim((string) $filters['search']);
            $query->where(function ($builder) use ($search): void {
                $builder
                    ->where('title', 'like', "%{$search}%")
                    ->orWhere('slug', 'like', "%{$search}%")
                    ->orWhere('meta_title', 'like', "%{$search}%");
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
     *     slug?: string|null,
     *     body?: string|null,
     *     meta_title?: string|null,
     *     meta_description?: string|null,
     *     status: string,
     * }  $data
     */
    public function create(array $data): Page
    {
        return DB::transaction(function () use ($data): Page {
            return Page::query()->create([
                'title' => $data['title'],
                'slug' => $this->uniqueSlug($data['slug'] ?? $data['title']),
                'body' => $data['body'] ?? null,
                'meta_title' => $data['meta_title'] ?? null,
                'meta_description' => $data['meta_description'] ?? null,
                'status' => $data['status'],
            ]);
        });
    }

    /**
     * @param  array{
     *     title: string,
     *     slug?: string|null,
     *     body?: string|null,
     *     meta_title?: string|null,
     *     meta_description?: string|null,
     *     status: string,
     * }  $data
     */
    public function update(Page $page, array $data): Page
    {
        return DB::transaction(function () use ($page, $data): Page {
            $page->fill([
                'title' => $data['title'],
                'slug' => $this->uniqueSlug($data['slug'] ?? $data['title'], $page->id),
                'body' => $data['body'] ?? null,
                'meta_title' => $data['meta_title'] ?? null,
                'meta_description' => $data['meta_description'] ?? null,
                'status' => $data['status'],
            ])->save();

            return $page->fresh();
        });
    }

    public function delete(Page $page): void
    {
        $page->delete();
    }

    public function toggleStatus(Page $page): Page
    {
        $page->status = $page->status === 'published' ? 'draft' : 'published';
        $page->save();

        return $page->fresh();
    }

    /**
     * @return array<string, mixed>
     */
    public function toArray(Page $page): array
    {
        $status = StatusRegistry::get('page', (string) $page->status);

        return [
            'id' => $page->id,
            'title' => $page->title,
            'slug' => $page->slug,
            'body' => $page->body,
            'meta_title' => $page->meta_title,
            'meta_description' => $page->meta_description,
            'status' => $status['code'],
            'status_label' => $status['label'],
            'status_badge' => $status['badge'],
            'created_at' => $page->created_at?->toIso8601String(),
            'updated_at' => $page->updated_at?->toIso8601String(),
            'updated_at_formatted' => $page->updated_at?->format('d M Y H:i'),
        ];
    }

    protected function uniqueSlug(string $value, ?int $ignoreId = null): string
    {
        $base = Str::slug($value) ?: 'page';
        $slug = $base;
        $i = 2;

        while (
            Page::query()
                ->when($ignoreId, fn ($q) => $q->where('id', '!=', $ignoreId))
                ->where('slug', $slug)
                ->exists()
        ) {
            $slug = $base.'-'.$i;
            $i++;
        }

        return $slug;
    }
}
