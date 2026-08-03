<?php

namespace App\Modules\Packages\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Modules\Packages\Http\Requests\StorePackageRequest;
use App\Modules\Packages\Http\Requests\UpdatePackageRequest;
use App\Modules\Packages\Models\Package;
use App\Modules\Packages\Services\PackageService;
use App\Support\StatusRegistry;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;
use InvalidArgumentException;

class PackageController extends Controller
{
    public function __construct(
        private readonly PackageService $packages,
    ) {}

    public function index(Request $request): Response
    {
        $this->authorize('viewAny', Package::class);

        $filters = [
            'search' => $request->string('search')->trim()->toString() ?: null,
            'status' => $request->string('status')->toString() ?: null,
        ];

        $paginator = $this->packages->paginate($filters);

        return Inertia::render('admin/packages/index', [
            'packages' => [
                'data' => $paginator->getCollection()
                    ->map(fn (Package $package) => $this->packages->toArray($package))
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
            'canManage' => $request->user()?->can('packages.manage') ?? false,
        ]);
    }

    public function create(): Response
    {
        $this->authorize('create', Package::class);

        return Inertia::render('admin/packages/create', [
            'statusOptions' => $this->statusOptions(),
            'dishOptions' => $this->packages->dishOptions(),
        ]);
    }

    public function store(StorePackageRequest $request): RedirectResponse
    {
        $this->authorize('create', Package::class);

        try {
            $package = $this->packages->create($request->validated());
        } catch (InvalidArgumentException $e) {
            return back()->withInput()->with('error', $e->getMessage());
        }

        return redirect()
            ->route('admin.packages.show', $package)
            ->with('success', true)
            ->with('message', 'Package created successfully.')
            ->with('code', 'package_created');
    }

    public function show(Package $package): Response
    {
        $this->authorize('view', $package);

        $package->load(['items.dish', 'items.dishVariation'])->loadCount('items');

        return Inertia::render('admin/packages/show', [
            'package' => $this->packages->toArray($package),
            'canManage' => request()->user()?->can('packages.manage') ?? false,
        ]);
    }

    public function edit(Package $package): Response
    {
        $this->authorize('update', $package);

        $package->load(['items.dish', 'items.dishVariation'])->loadCount('items');

        return Inertia::render('admin/packages/edit', [
            'package' => $this->packages->toArray($package),
            'statusOptions' => $this->statusOptions(),
            'dishOptions' => $this->packages->dishOptions(),
        ]);
    }

    public function update(UpdatePackageRequest $request, Package $package): RedirectResponse
    {
        $this->authorize('update', $package);

        try {
            $this->packages->update($package, $request->validated());
        } catch (InvalidArgumentException $e) {
            return back()->withInput()->with('error', $e->getMessage());
        }

        return redirect()
            ->route('admin.packages.show', $package)
            ->with('success', true)
            ->with('message', 'Package updated successfully.')
            ->with('code', 'package_updated');
    }

    public function destroy(Package $package): RedirectResponse
    {
        $this->authorize('delete', $package);

        try {
            $this->packages->delete($package);
        } catch (InvalidArgumentException $e) {
            return back()->with('error', $e->getMessage());
        }

        return redirect()
            ->route('admin.packages.index')
            ->with('success', true)
            ->with('message', 'Package deleted successfully.')
            ->with('code', 'package_deleted');
    }

    public function toggleStatus(Package $package): RedirectResponse
    {
        $this->authorize('update', $package);

        $updated = $this->packages->toggleActive($package);
        $label = $updated->status === 'active' ? 'activated' : 'deactivated';

        return back()
            ->with('success', true)
            ->with('message', "Package {$label} successfully.")
            ->with('code', 'package_status_toggled');
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
