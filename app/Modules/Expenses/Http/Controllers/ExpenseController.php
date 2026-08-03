<?php

namespace App\Modules\Expenses\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Modules\Expenses\Http\Requests\StoreExpenseRequest;
use App\Modules\Expenses\Http\Requests\UpdateExpenseRequest;
use App\Modules\Expenses\Http\Requests\UpdateExpenseStatusRequest;
use App\Modules\Expenses\Models\Expense;
use App\Modules\Expenses\Services\ExpenseService;
use App\Support\Money;
use App\Support\StatusRegistry;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;
use InvalidArgumentException;

class ExpenseController extends Controller
{
    public function __construct(
        private readonly ExpenseService $expenses,
    ) {}

    public function index(Request $request): Response
    {
        $this->authorize('viewAny', Expense::class);

        $filters = [
            'search' => $request->string('search')->trim()->toString() ?: null,
            'status' => $request->string('status')->toString() ?: null,
            'expense_category_id' => $request->integer('expense_category_id') ?: null,
            'date_from' => $request->string('date_from')->toString() ?: null,
            'date_to' => $request->string('date_to')->toString() ?: null,
        ];

        $paginator = $this->expenses->paginate($filters);
        $actor = $request->user();
        $canViewTotals = $actor?->can('reports.earnings') ?? false;
        $filteredTotal = $canViewTotals ? $this->expenses->filteredTotal($filters) : null;

        return Inertia::render('admin/expenses/index', [
            'expenses' => [
                'data' => $paginator->getCollection()
                    ->map(fn (Expense $expense) => $this->expenses->toArray($expense))
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
            'categoryOptions' => $this->expenses->categoryOptions(activeOnly: false),
            'canCreate' => $actor?->can('expenses.create') ?? false,
            'canManage' => $actor?->can('expenses.manage') ?? false,
            'canViewTotals' => $canViewTotals,
            'totals' => $canViewTotals
                ? [
                    'filtered' => $filteredTotal,
                    'filtered_formatted' => Money::format((float) $filteredTotal),
                ]
                : null,
        ]);
    }

    public function create(): Response
    {
        $this->authorize('create', Expense::class);

        return Inertia::render('admin/expenses/create', [
            'statusOptions' => $this->statusOptions(),
            'categoryOptions' => $this->expenses->categoryOptions(),
        ]);
    }

    public function store(StoreExpenseRequest $request): RedirectResponse
    {
        $this->authorize('create', Expense::class);

        $expense = $this->expenses->create($request->validated(), $request->user());

        return redirect()
            ->route('admin.expenses.show', $expense)
            ->with('success', true)
            ->with('message', 'Expense created successfully.')
            ->with('code', 'expense_created');
    }

    public function show(Expense $expense): Response
    {
        $this->authorize('view', $expense);

        return Inertia::render('admin/expenses/show', [
            'expense' => $this->expenses->toArray($expense),
            'statusOptions' => $this->statusOptions(),
            'canManage' => request()->user()?->can('expenses.manage') ?? false,
        ]);
    }

    public function edit(Expense $expense): Response
    {
        $this->authorize('update', $expense);

        return Inertia::render('admin/expenses/edit', [
            'statusOptions' => $this->statusOptions(),
            'categoryOptions' => $this->expenses->categoryOptions(activeOnly: false),
            'expense' => $this->expenses->toArray($expense),
        ]);
    }

    public function update(UpdateExpenseRequest $request, Expense $expense): RedirectResponse
    {
        $this->authorize('update', $expense);

        $this->expenses->update($expense, $request->validated());

        return redirect()
            ->route('admin.expenses.show', $expense)
            ->with('success', true)
            ->with('message', 'Expense updated successfully.')
            ->with('code', 'expense_updated');
    }

    public function destroy(Expense $expense): RedirectResponse
    {
        $this->authorize('delete', $expense);

        $this->expenses->delete($expense);

        return redirect()
            ->route('admin.expenses.index')
            ->with('success', true)
            ->with('message', 'Expense deleted successfully.')
            ->with('code', 'expense_deleted');
    }

    public function updateStatus(UpdateExpenseStatusRequest $request, Expense $expense): RedirectResponse
    {
        $this->authorize('updateStatus', $expense);

        try {
            $this->expenses->updateStatus($expense, $request->validated('status'));
        } catch (InvalidArgumentException $e) {
            return back()->with('error', $e->getMessage());
        }

        return back()
            ->with('success', true)
            ->with('message', 'Expense status updated.')
            ->with('code', 'expense_status_updated');
    }

    /**
     * @return list<array{value: string, label: string}>
     */
    private function statusOptions(): array
    {
        return array_map(
            fn (array $status) => [
                'value' => $status['code'],
                'label' => $status['label'],
            ],
            StatusRegistry::all('expense'),
        );
    }
}
