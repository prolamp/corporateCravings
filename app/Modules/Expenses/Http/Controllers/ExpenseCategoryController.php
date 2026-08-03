<?php

namespace App\Modules\Expenses\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Modules\Expenses\Http\Requests\StoreExpenseCategoryRequest;
use App\Modules\Expenses\Http\Requests\UpdateExpenseCategoryRequest;
use App\Modules\Expenses\Models\ExpenseCategory;
use App\Modules\Expenses\Services\ExpenseCategoryService;
use App\Support\StatusRegistry;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;
use InvalidArgumentException;

class ExpenseCategoryController extends Controller
{
    public function __construct(
        private readonly ExpenseCategoryService $categories,
    ) {}

    public function index(Request $request): Response
    {
        $this->authorize('viewAny', ExpenseCategory::class);

        $filters = [
            'search' => $request->string('search')->trim()->toString() ?: null,
            'status' => $request->string('status')->toString() ?: null,
        ];

        $paginator = $this->categories->paginate($filters);

        return Inertia::render('admin/expense-categories/index', [
            'categories' => [
                'data' => $paginator->getCollection()
                    ->map(fn (ExpenseCategory $category) => $this->categories->toArray($category))
                    ->values()
                    ->all(),
                'meta' => [
                    'current_page' => $paginator->currentPage(),
                    'last_page' => $paginator->lastPage(),
                    'per_page' => $paginator->perPage(),
                    'total' => $paginator->total(),
                    'from' => $paginator->firstItem(),
                    'to' => $paginator->lastItem(),
                ],
            ],
            'filters' => $filters,
            'statusOptions' => $this->statusOptions(),
            'canManage' => $request->user()?->can('expenses.manage') ?? false,
        ]);
    }

    public function create(): Response
    {
        $this->authorize('create', ExpenseCategory::class);

        return Inertia::render('admin/expense-categories/create', [
            'statusOptions' => $this->statusOptions(),
        ]);
    }

    public function store(StoreExpenseCategoryRequest $request): RedirectResponse
    {
        $this->authorize('create', ExpenseCategory::class);

        $category = $this->categories->create($request->validated());

        return redirect()
            ->route('admin.expense-categories.show', $category)
            ->with('success', true)
            ->with('message', 'Expense category created successfully.')
            ->with('code', 'expense_category_created');
    }

    public function show(ExpenseCategory $expenseCategory): Response
    {
        $this->authorize('view', $expenseCategory);

        $expenseCategory->loadCount('expenses');

        return Inertia::render('admin/expense-categories/show', [
            'category' => $this->categories->toArray($expenseCategory),
            'canManage' => request()->user()?->can('expenses.manage') ?? false,
        ]);
    }

    public function edit(ExpenseCategory $expenseCategory): Response
    {
        $this->authorize('update', $expenseCategory);

        $expenseCategory->loadCount('expenses');

        return Inertia::render('admin/expense-categories/edit', [
            'category' => $this->categories->toArray($expenseCategory),
            'statusOptions' => $this->statusOptions(),
        ]);
    }

    public function update(
        UpdateExpenseCategoryRequest $request,
        ExpenseCategory $expenseCategory,
    ): RedirectResponse {
        $this->authorize('update', $expenseCategory);

        $this->categories->update($expenseCategory, $request->validated());

        return redirect()
            ->route('admin.expense-categories.show', $expenseCategory)
            ->with('success', true)
            ->with('message', 'Expense category updated successfully.')
            ->with('code', 'expense_category_updated');
    }

    public function destroy(ExpenseCategory $expenseCategory): RedirectResponse
    {
        $this->authorize('delete', $expenseCategory);

        try {
            $this->categories->delete($expenseCategory);
        } catch (InvalidArgumentException $e) {
            return back()->with('error', $e->getMessage());
        }

        return redirect()
            ->route('admin.expense-categories.index')
            ->with('success', true)
            ->with('message', 'Expense category deleted successfully.')
            ->with('code', 'expense_category_deleted');
    }

    public function toggleStatus(ExpenseCategory $expenseCategory): RedirectResponse
    {
        $this->authorize('update', $expenseCategory);

        $updated = $this->categories->toggleActive($expenseCategory);
        $label = $updated->is_active ? 'activated' : 'deactivated';

        return back()
            ->with('success', true)
            ->with('message', "Expense category {$label} successfully.")
            ->with('code', 'expense_category_status_toggled');
    }

    /**
     * @return list<array{value: string, label: string}>
     */
    private function statusOptions(): array
    {
        return array_values(array_filter(
            array_map(
                fn (array $status) => [
                    'value' => $status['code'],
                    'label' => $status['label'],
                ],
                StatusRegistry::all('generic'),
            ),
            fn (array $option) => in_array($option['value'], ['active', 'inactive'], true),
        ));
    }
}
