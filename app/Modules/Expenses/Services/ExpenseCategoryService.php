<?php

namespace App\Modules\Expenses\Services;

use App\Modules\Expenses\Models\ExpenseCategory;
use App\Support\StatusRegistry;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\DB;
use InvalidArgumentException;

class ExpenseCategoryService
{
    /**
     * @param  array{search?: string|null, status?: string|null}  $filters
     * @return LengthAwarePaginator<int, ExpenseCategory>
     */
    public function paginate(array $filters = [], int $perPage = 15): LengthAwarePaginator
    {
        $query = ExpenseCategory::query()
            ->withCount('expenses')
            ->orderBy('name');

        if (! empty($filters['search'])) {
            $search = trim((string) $filters['search']);
            $query->where(function ($builder) use ($search): void {
                $builder
                    ->where('name', 'like', "%{$search}%")
                    ->orWhere('description', 'like', "%{$search}%");
            });
        }

        if (($filters['status'] ?? null) === 'active') {
            $query->where('is_active', true);
        } elseif (($filters['status'] ?? null) === 'inactive') {
            $query->where('is_active', false);
        }

        return $query->paginate($perPage)->withQueryString();
    }

    /**
     * @param  array{
     *     name: string,
     *     description?: string|null,
     *     is_active: bool,
     * }  $data
     */
    public function create(array $data): ExpenseCategory
    {
        return DB::transaction(function () use ($data): ExpenseCategory {
            $category = ExpenseCategory::query()->create([
                'name' => $data['name'],
                'description' => $data['description'] ?? null,
                'is_active' => (bool) $data['is_active'],
            ]);

            return $category->loadCount('expenses');
        });
    }

    /**
     * @param  array{
     *     name: string,
     *     description?: string|null,
     *     is_active: bool,
     * }  $data
     */
    public function update(ExpenseCategory $category, array $data): ExpenseCategory
    {
        return DB::transaction(function () use ($category, $data): ExpenseCategory {
            $category->fill([
                'name' => $data['name'],
                'description' => $data['description'] ?? null,
                'is_active' => (bool) $data['is_active'],
            ])->save();

            return $category->loadCount('expenses');
        });
    }

    public function delete(ExpenseCategory $category): void
    {
        if ($category->expenses()->exists()) {
            throw new InvalidArgumentException(
                'This category has expenses. Deactivate it instead of deleting.',
            );
        }

        $category->delete();
    }

    public function toggleActive(ExpenseCategory $category): ExpenseCategory
    {
        $category->is_active = ! $category->is_active;
        $category->save();

        return $category->loadCount('expenses');
    }

    /**
     * @return array<string, mixed>
     */
    public function toArray(ExpenseCategory $category): array
    {
        $statusCode = $category->is_active ? 'active' : 'inactive';
        $status = StatusRegistry::get('generic', $statusCode);

        return [
            'id' => $category->id,
            'name' => $category->name,
            'description' => $category->description,
            'is_active' => (bool) $category->is_active,
            'status' => $status['code'],
            'status_label' => $status['label'],
            'status_badge' => $status['badge'],
            'expenses_count' => (int) ($category->expenses_count ?? $category->expenses()->count()),
            'created_at' => $category->created_at?->toIso8601String(),
            'updated_at' => $category->updated_at?->toIso8601String(),
        ];
    }
}
