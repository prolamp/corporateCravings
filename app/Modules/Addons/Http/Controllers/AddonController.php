<?php

namespace App\Modules\Addons\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Modules\Addons\Http\Requests\StoreAddonRequest;
use App\Modules\Addons\Http\Requests\UpdateAddonRequest;
use App\Modules\Addons\Models\Addon;
use App\Modules\Addons\Services\AddonService;
use App\Support\StatusRegistry;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class AddonController extends Controller
{
    public function __construct(
        private readonly AddonService $addons,
    ) {}

    public function index(Request $request): Response
    {
        $this->authorize('viewAny', Addon::class);

        $filters = [
            'search' => $request->string('search')->trim()->toString() ?: null,
            'status' => $request->string('status')->toString() ?: null,
            'type' => $request->string('type')->toString() ?: null,
            'scope' => $request->string('scope')->toString() ?: null,
        ];

        $paginator = $this->addons->paginate($filters);

        return Inertia::render('admin/addons/index', [
            'addons' => [
                'data' => $paginator->getCollection()
                    ->map(fn (Addon $addon) => $this->addons->toArray($addon))
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
            'typeOptions' => $this->addons->typeOptions(),
            'statusOptions' => $this->statusOptions(),
            'canManage' => $request->user()?->can('addons.manage') ?? false,
        ]);
    }

    public function create(): Response
    {
        $this->authorize('create', Addon::class);

        return Inertia::render('admin/addons/create', [
            'typeOptions' => $this->addons->typeOptions(),
            'statusOptions' => $this->statusOptions(),
            'dishOptions' => $this->addons->dishOptions(),
        ]);
    }

    public function store(StoreAddonRequest $request): RedirectResponse
    {
        $this->authorize('create', Addon::class);

        $addon = $this->addons->create($request->validated());

        return redirect()
            ->route('admin.addons.show', $addon)
            ->with('success', true)
            ->with('message', 'Addon created successfully.')
            ->with('code', 'addon_created');
    }

    public function show(Addon $addon): Response
    {
        $this->authorize('view', $addon);

        $addon->load('dishes')->loadCount('dishes');

        return Inertia::render('admin/addons/show', [
            'addon' => $this->addons->toArray($addon),
            'canManage' => request()->user()?->can('addons.manage') ?? false,
        ]);
    }

    public function edit(Addon $addon): Response
    {
        $this->authorize('update', $addon);

        $addon->load('dishes')->loadCount('dishes');

        return Inertia::render('admin/addons/edit', [
            'addon' => $this->addons->toArray($addon),
            'typeOptions' => $this->addons->typeOptions(),
            'statusOptions' => $this->statusOptions(),
            'dishOptions' => $this->addons->dishOptions(),
        ]);
    }

    public function update(UpdateAddonRequest $request, Addon $addon): RedirectResponse
    {
        $this->authorize('update', $addon);

        $this->addons->update($addon, $request->validated());

        return redirect()
            ->route('admin.addons.show', $addon)
            ->with('success', true)
            ->with('message', 'Addon updated successfully.')
            ->with('code', 'addon_updated');
    }

    public function destroy(Addon $addon): RedirectResponse
    {
        $this->authorize('delete', $addon);

        $this->addons->delete($addon);

        return redirect()
            ->route('admin.addons.index')
            ->with('success', true)
            ->with('message', 'Addon deleted successfully.')
            ->with('code', 'addon_deleted');
    }

    public function toggleStatus(Addon $addon): RedirectResponse
    {
        $this->authorize('update', $addon);

        $updated = $this->addons->toggleActive($addon);
        $label = $updated->status === 'active' ? 'activated' : 'deactivated';

        return back()
            ->with('success', true)
            ->with('message', "Addon {$label} successfully.")
            ->with('code', 'addon_status_toggled');
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
