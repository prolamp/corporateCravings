<?php

namespace App\Modules\Expenses\Services;

use App\Models\User;
use App\Modules\Expenses\Models\Expense;
use App\Modules\Expenses\Models\ExpenseCategory;
use App\Support\ImageService;
use App\Support\Money;
use App\Support\StatusRegistry;
use Illuminate\Http\UploadedFile;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\DB;
use InvalidArgumentException;

class ExpenseService
{
    public function __construct(
        private readonly ImageService $images,
    ) {}

    /**
     * @param  array{
     *     search?: string|null,
     *     status?: string|null,
     *     expense_category_id?: int|string|null,
     *     date_from?: string|null,
     *     date_to?: string|null,
     * }  $filters
     * @return LengthAwarePaginator<int, Expense>
     */
    public function paginate(array $filters = [], int $perPage = 15): LengthAwarePaginator
    {
        return $this->filteredQuery($filters)
            ->with(['expenseCategory:id,name', 'createdBy:id,name'])
            ->orderByDesc('date')
            ->orderByDesc('id')
            ->paginate($perPage)
            ->withQueryString();
    }

    /**
     * @param  array{
     *     search?: string|null,
     *     status?: string|null,
     *     expense_category_id?: int|string|null,
     *     date_from?: string|null,
     *     date_to?: string|null,
     * }  $filters
     */
    public function filteredTotal(array $filters = []): float
    {
        return (float) $this->filteredQuery($filters)->sum('total');
    }

    /**
     * @param  array{
     *     expense_category_id: int,
     *     title: string,
     *     description?: string|null,
     *     quantity: float|int|string,
     *     unit?: string|null,
     *     unit_price: float|int|string,
     *     date: string,
     *     status: string,
     *     receipt_image?: UploadedFile|null,
     * }  $data
     */
    public function create(array $data, ?User $actor = null): Expense
    {
        return DB::transaction(function () use ($data, $actor): Expense {
            $quantity = (float) $data['quantity'];
            $unitPrice = (float) $data['unit_price'];

            $expense = new Expense([
                'expense_category_id' => (int) $data['expense_category_id'],
                'title' => $data['title'],
                'description' => $data['description'] ?? null,
                'quantity' => Money::of($quantity),
                'unit' => $data['unit'] ?? null,
                'unit_price' => Money::of($unitPrice),
                'total' => Money::of(round($quantity * $unitPrice, 2)),
                'date' => $data['date'],
                'status' => $data['status'],
                'created_by' => $actor?->id,
            ]);

            if (! empty($data['receipt_image']) && $data['receipt_image'] instanceof UploadedFile) {
                $expense->receipt_image = $this->images->store(
                    $data['receipt_image'],
                    'expenses/receipts',
                );
            }

            $expense->save();

            return $expense->load(['expenseCategory', 'createdBy']);
        });
    }

    /**
     * @param  array{
     *     expense_category_id: int,
     *     title: string,
     *     description?: string|null,
     *     quantity: float|int|string,
     *     unit?: string|null,
     *     unit_price: float|int|string,
     *     date: string,
     *     status: string,
     *     receipt_image?: UploadedFile|null,
     *     remove_receipt_image?: bool,
     * }  $data
     */
    public function update(Expense $expense, array $data): Expense
    {
        return DB::transaction(function () use ($expense, $data): Expense {
            $quantity = (float) $data['quantity'];
            $unitPrice = (float) $data['unit_price'];

            $expense->fill([
                'expense_category_id' => (int) $data['expense_category_id'],
                'title' => $data['title'],
                'description' => $data['description'] ?? null,
                'quantity' => Money::of($quantity),
                'unit' => $data['unit'] ?? null,
                'unit_price' => Money::of($unitPrice),
                'total' => Money::of(round($quantity * $unitPrice, 2)),
                'date' => $data['date'],
                'status' => $data['status'],
            ]);

            if (! empty($data['remove_receipt_image'])) {
                $this->images->delete($expense->receipt_image);
                $expense->receipt_image = null;
            }

            if (! empty($data['receipt_image']) && $data['receipt_image'] instanceof UploadedFile) {
                $expense->receipt_image = $this->images->store(
                    $data['receipt_image'],
                    'expenses/receipts',
                    $expense->receipt_image,
                );
            }

            $expense->save();

            return $expense->load(['expenseCategory', 'createdBy']);
        });
    }

    public function updateStatus(Expense $expense, string $status): Expense
    {
        $allowed = StatusRegistry::codes('expense');
        if (! in_array($status, $allowed, true)) {
            throw new InvalidArgumentException('Invalid expense status.');
        }

        if ($expense->status === $status) {
            throw new InvalidArgumentException('Expense is already in that status.');
        }

        $expense->status = $status;
        $expense->save();

        return $expense->load(['expenseCategory', 'createdBy']);
    }

    public function delete(Expense $expense): void
    {
        $this->images->delete($expense->receipt_image);
        $expense->delete();
    }

    /**
     * @return array<string, mixed>
     */
    public function toArray(Expense $expense): array
    {
        $expense->loadMissing(['expenseCategory:id,name', 'createdBy:id,name']);
        $status = StatusRegistry::get('expense', (string) $expense->status);

        return [
            'id' => $expense->id,
            'expense_category_id' => $expense->expense_category_id,
            'category_name' => $expense->expenseCategory?->name,
            'title' => $expense->title,
            'description' => $expense->description,
            'quantity' => (float) $expense->quantity,
            'unit' => $expense->unit,
            'unit_price' => (float) $expense->unit_price,
            'unit_price_formatted' => Money::format((float) $expense->unit_price),
            'total' => (float) $expense->total,
            'total_formatted' => Money::format((float) $expense->total),
            'date' => $expense->date?->toDateString(),
            'date_formatted' => $expense->date?->format('d M Y'),
            'status' => $status['code'],
            'status_label' => $status['label'],
            'status_badge' => $status['badge'],
            'created_by' => $expense->createdBy?->name,
            'receipt_image' => $expense->receipt_image,
            'receipt_image_url' => $this->images->url($expense->receipt_image),
            'created_at' => $expense->created_at?->toIso8601String(),
            'updated_at' => $expense->updated_at?->toIso8601String(),
        ];
    }

    /**
     * @return list<array{value: int, label: string}>
     */
    public function categoryOptions(bool $activeOnly = true): array
    {
        $query = ExpenseCategory::query()->orderBy('name');

        if ($activeOnly) {
            $query->where('is_active', true);
        }

        return $query
            ->get(['id', 'name'])
            ->map(fn (ExpenseCategory $category) => [
                'value' => $category->id,
                'label' => $category->name,
            ])
            ->values()
            ->all();
    }

    /**
     * @param  array{
     *     search?: string|null,
     *     status?: string|null,
     *     expense_category_id?: int|string|null,
     *     date_from?: string|null,
     *     date_to?: string|null,
     * }  $filters
     * @return \Illuminate\Database\Eloquent\Builder<Expense>
     */
    private function filteredQuery(array $filters)
    {
        $query = Expense::query();

        if (! empty($filters['search'])) {
            $search = trim((string) $filters['search']);
            $query->where(function ($builder) use ($search): void {
                $builder
                    ->where('title', 'like', "%{$search}%")
                    ->orWhere('description', 'like', "%{$search}%")
                    ->orWhere('unit', 'like', "%{$search}%");
            });
        }

        if (! empty($filters['status'])) {
            $query->where('status', $filters['status']);
        }

        if (! empty($filters['expense_category_id'])) {
            $query->where('expense_category_id', (int) $filters['expense_category_id']);
        }

        if (! empty($filters['date_from'])) {
            $query->whereDate('date', '>=', $filters['date_from']);
        }

        if (! empty($filters['date_to'])) {
            $query->whereDate('date', '<=', $filters['date_to']);
        }

        return $query;
    }
}
