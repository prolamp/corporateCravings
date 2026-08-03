<?php

namespace App\Modules\Cms\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Models\Page;
use App\Modules\Cms\Http\Requests\StorePageRequest;
use App\Modules\Cms\Http\Requests\UpdatePageRequest;
use App\Modules\Cms\Services\PageService;
use App\Support\StatusRegistry;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class PageController extends Controller
{
    public function __construct(
        private readonly PageService $pages,
    ) {}

    public function index(Request $request): Response
    {
        $this->authorize('viewAny', Page::class);

        $filters = [
            'search' => $request->string('search')->trim()->toString() ?: null,
            'status' => $request->string('status')->toString() ?: null,
        ];

        $paginator = $this->pages->paginate($filters);

        return Inertia::render('admin/pages/index', [
            'pages' => [
                'data' => $paginator->getCollection()
                    ->map(fn (Page $page) => $this->pages->toArray($page))
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
            'canManage' => $request->user()?->can('cms.manage') ?? false,
        ]);
    }

    public function create(): Response
    {
        $this->authorize('create', Page::class);

        return Inertia::render('admin/pages/create', [
            'statusOptions' => $this->statusOptions(),
        ]);
    }

    public function store(StorePageRequest $request): RedirectResponse
    {
        $this->authorize('create', Page::class);

        $page = $this->pages->create($request->validated());

        return redirect()
            ->route('admin.pages.show', $page)
            ->with('success', true)
            ->with('message', 'Page created successfully.')
            ->with('code', 'page_created');
    }

    public function show(Page $page): Response
    {
        $this->authorize('view', $page);

        return Inertia::render('admin/pages/show', [
            'page' => $this->pages->toArray($page),
            'canManage' => request()->user()?->can('cms.manage') ?? false,
        ]);
    }

    public function edit(Page $page): Response
    {
        $this->authorize('update', $page);

        return Inertia::render('admin/pages/edit', [
            'page' => $this->pages->toArray($page),
            'statusOptions' => $this->statusOptions(),
        ]);
    }

    public function update(UpdatePageRequest $request, Page $page): RedirectResponse
    {
        $this->authorize('update', $page);

        $this->pages->update($page, $request->validated());

        return redirect()
            ->route('admin.pages.show', $page)
            ->with('success', true)
            ->with('message', 'Page updated successfully.')
            ->with('code', 'page_updated');
    }

    public function destroy(Page $page): RedirectResponse
    {
        $this->authorize('delete', $page);

        $this->pages->delete($page);

        return redirect()
            ->route('admin.pages.index')
            ->with('success', true)
            ->with('message', 'Page deleted successfully.')
            ->with('code', 'page_deleted');
    }

    public function toggleStatus(Page $page): RedirectResponse
    {
        $this->authorize('update', $page);

        $updated = $this->pages->toggleStatus($page);
        $label = $updated->status === 'published' ? 'published' : 'set to draft';

        return back()
            ->with('success', true)
            ->with('message', "Page {$label} successfully.")
            ->with('code', 'page_status_toggled');
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
            StatusRegistry::all('page'),
        );
    }
}
