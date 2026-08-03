<?php

namespace App\Modules\Catalog\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Modules\Catalog\Http\Requests\StoreDishRequest;
use App\Modules\Catalog\Http\Requests\UpdateDishRequest;
use App\Modules\Catalog\Models\Dish;
use App\Modules\Catalog\Services\DishService;
use App\Support\StatusRegistry;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;
use InvalidArgumentException;

class DishController extends Controller
{
    public function __construct(
        private readonly DishService $dishes,
    ) {}

    public function index(Request $request): Response
    {
        $this->authorize('viewAny', Dish::class);

        $filters = [
            'search' => $request->string('search')->trim()->toString() ?: null,
            'status' => $request->string('status')->toString() ?: null,
            'category_id' => $request->integer('category_id') ?: null,
        ];

        $paginator = $this->dishes->paginate($filters);

        return Inertia::render('admin/dishes/index', [
            'dishes' => [
                'data' => $paginator->getCollection()
                    ->map(fn (Dish $dish) => $this->dishes->toArray($dish))
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
            'categoryOptions' => $this->dishes->categoryOptions(),
            'statusOptions' => $this->statusOptions(),
            'canManage' => $request->user()?->can('dishes.manage') ?? false,
        ]);
    }

    public function create(): Response
    {
        $this->authorize('create', Dish::class);

        return Inertia::render('admin/dishes/create', [
            'categoryOptions' => $this->dishes->categoryOptions(),
            'statusOptions' => $this->statusOptions(),
            'unitOptions' => $this->dishes->unitOptions(),
            'typeOptions' => $this->dishes->typeOptions(),
        ]);
    }

    public function store(StoreDishRequest $request): RedirectResponse
    {
        $this->authorize('create', Dish::class);

        $dish = $this->dishes->create($request->validated());

        return redirect()
            ->route('admin.dishes.show', $dish)
            ->with('success', true)
            ->with('message', 'Dish created successfully.')
            ->with('code', 'dish_created');
    }

    public function show(Dish $dish): Response
    {
        $this->authorize('view', $dish);

        $dish->load(['category', 'variations'])->loadCount('variations');

        return Inertia::render('admin/dishes/show', [
            'dish' => $this->dishes->toArray($dish),
            'canManage' => request()->user()?->can('dishes.manage') ?? false,
        ]);
    }

    public function edit(Dish $dish): Response
    {
        $this->authorize('update', $dish);

        $dish->load(['category', 'variations'])->loadCount('variations');

        return Inertia::render('admin/dishes/edit', [
            'dish' => $this->dishes->toArray($dish),
            'categoryOptions' => $this->dishes->categoryOptions(),
            'statusOptions' => $this->statusOptions(),
            'unitOptions' => $this->dishes->unitOptions(),
            'typeOptions' => $this->dishes->typeOptions(),
        ]);
    }

    public function update(UpdateDishRequest $request, Dish $dish): RedirectResponse
    {
        $this->authorize('update', $dish);

        $this->dishes->update($dish, $request->validated());

        return redirect()
            ->route('admin.dishes.show', $dish)
            ->with('success', true)
            ->with('message', 'Dish updated successfully.')
            ->with('code', 'dish_updated');
    }

    public function destroy(Dish $dish): RedirectResponse
    {
        $this->authorize('delete', $dish);

        try {
            $this->dishes->delete($dish);
        } catch (InvalidArgumentException $e) {
            return back()->with('error', $e->getMessage());
        }

        return redirect()
            ->route('admin.dishes.index')
            ->with('success', true)
            ->with('message', 'Dish deleted successfully.')
            ->with('code', 'dish_deleted');
    }

    public function toggleStatus(Dish $dish): RedirectResponse
    {
        $this->authorize('update', $dish);

        $updated = $this->dishes->toggleActive($dish);
        $label = $updated->status === 'active' ? 'activated' : 'deactivated';

        return back()
            ->with('success', true)
            ->with('message', "Dish {$label} successfully.")
            ->with('code', 'dish_status_toggled');
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
            StatusRegistry::all('generic'),
        );
    }
}
