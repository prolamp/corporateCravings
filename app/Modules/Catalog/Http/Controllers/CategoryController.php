<?php

namespace App\Modules\Catalog\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Modules\Catalog\Http\Requests\StoreCategoryRequest;
use App\Modules\Catalog\Http\Requests\UpdateCategoryRequest;
use App\Modules\Catalog\Models\Category;
use App\Modules\Catalog\Services\CategoryService;
use App\Support\StatusRegistry;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;
use InvalidArgumentException;

class CategoryController extends Controller
{
    public function __construct(
        private readonly CategoryService $categories,
    ) {}

    public function index(Request $request): Response
    {
        $this->authorize('viewAny', Category::class);

        $filters = [
            'search' => $request->string('search')->trim()->toString() ?: null,
            'status' => $request->string('status')->toString() ?: null,
        ];

        $paginator = $this->categories->paginate($filters);

        return Inertia::render('admin/categories/index', [
            'categories' => [
                'data' => $paginator->getCollection()
                    ->map(fn (Category $category) => $this->categories->toArray($category))
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
            'canManage' => $request->user()?->can('categories.manage') ?? false,
        ]);
    }

    public function create(Request $request): Response
    {
        $this->authorize('create', Category::class);

        return Inertia::render('admin/categories/create', [
            'statusOptions' => $this->statusOptions(),
        ]);
    }

    public function store(StoreCategoryRequest $request): RedirectResponse
    {
        $this->authorize('create', Category::class);

        $category = $this->categories->create($request->validated());

        return redirect()
            ->route('admin.categories.show', $category)
            ->with('success', true)
            ->with('message', 'Category created successfully.')
            ->with('code', 'category_created');
    }

    public function show(Category $category): Response
    {
        $this->authorize('view', $category);

        return Inertia::render('admin/categories/show', [
            'category' => $this->categories->toArray($category->loadCount('dishes')),
            'canManage' => request()->user()?->can('categories.manage') ?? false,
        ]);
    }

    public function edit(Category $category): Response
    {
        $this->authorize('update', $category);

        return Inertia::render('admin/categories/edit', [
            'category' => $this->categories->toArray($category->loadCount('dishes')),
            'statusOptions' => $this->statusOptions(),
        ]);
    }

    public function update(UpdateCategoryRequest $request, Category $category): RedirectResponse
    {
        $this->authorize('update', $category);

        $this->categories->update($category, $request->validated());

        return redirect()
            ->route('admin.categories.show', $category)
            ->with('success', true)
            ->with('message', 'Category updated successfully.')
            ->with('code', 'category_updated');
    }

    public function destroy(Category $category): RedirectResponse
    {
        $this->authorize('delete', $category);

        try {
            $this->categories->delete($category);
        } catch (InvalidArgumentException $e) {
            return back()->with('error', $e->getMessage());
        }

        return redirect()
            ->route('admin.categories.index')
            ->with('success', true)
            ->with('message', 'Category deleted successfully.')
            ->with('code', 'category_deleted');
    }

    public function toggleStatus(Category $category): RedirectResponse
    {
        $this->authorize('update', $category);

        $updated = $this->categories->toggleActive($category);
        $label = $updated->status === 'active' ? 'activated' : 'deactivated';

        return back()
            ->with('success', true)
            ->with('message', "Category {$label} successfully.")
            ->with('code', 'category_status_toggled');
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
