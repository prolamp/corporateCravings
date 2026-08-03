<?php

namespace App\Modules\Delivery\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Modules\Delivery\Http\Requests\StoreDeliveryZoneRequest;
use App\Modules\Delivery\Http\Requests\UpdateDeliveryZoneRequest;
use App\Modules\Delivery\Models\DeliveryZone;
use App\Modules\Delivery\Services\DeliveryZoneService;
use App\Support\StatusRegistry;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;
use InvalidArgumentException;

class DeliveryZoneController extends Controller
{
    public function __construct(
        private readonly DeliveryZoneService $zones,
    ) {}

    public function index(Request $request): Response
    {
        $this->authorize('viewAny', DeliveryZone::class);

        $filters = [
            'search' => $request->string('search')->trim()->toString() ?: null,
            'status' => $request->string('status')->toString() ?: null,
        ];

        $paginator = $this->zones->paginate($filters);

        return Inertia::render('admin/delivery-zones/index', [
            'zones' => [
                'data' => $paginator->getCollection()
                    ->map(fn (DeliveryZone $zone) => $this->zones->toArray($zone))
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
            'canManage' => $request->user()?->can('delivery_zones.manage') ?? false,
        ]);
    }

    public function create(): Response
    {
        $this->authorize('create', DeliveryZone::class);

        return Inertia::render('admin/delivery-zones/create', [
            'statusOptions' => $this->statusOptions(),
        ]);
    }

    public function store(StoreDeliveryZoneRequest $request): RedirectResponse
    {
        $this->authorize('create', DeliveryZone::class);

        $zone = $this->zones->create($request->validated());

        return redirect()
            ->route('admin.delivery-zones.show', $zone)
            ->with('success', true)
            ->with('message', 'Delivery zone created successfully.')
            ->with('code', 'delivery_zone_created');
    }

    public function show(DeliveryZone $deliveryZone): Response
    {
        $this->authorize('view', $deliveryZone);

        $deliveryZone->loadCount('orders');

        return Inertia::render('admin/delivery-zones/show', [
            'zone' => $this->zones->toArray($deliveryZone),
            'canManage' => request()->user()?->can('delivery_zones.manage') ?? false,
        ]);
    }

    public function edit(DeliveryZone $deliveryZone): Response
    {
        $this->authorize('update', $deliveryZone);

        $deliveryZone->loadCount('orders');

        return Inertia::render('admin/delivery-zones/edit', [
            'zone' => $this->zones->toArray($deliveryZone),
            'statusOptions' => $this->statusOptions(),
        ]);
    }

    public function update(UpdateDeliveryZoneRequest $request, DeliveryZone $deliveryZone): RedirectResponse
    {
        $this->authorize('update', $deliveryZone);

        $this->zones->update($deliveryZone, $request->validated());

        return redirect()
            ->route('admin.delivery-zones.show', $deliveryZone)
            ->with('success', true)
            ->with('message', 'Delivery zone updated successfully.')
            ->with('code', 'delivery_zone_updated');
    }

    public function destroy(DeliveryZone $deliveryZone): RedirectResponse
    {
        $this->authorize('delete', $deliveryZone);

        try {
            $this->zones->delete($deliveryZone);
        } catch (InvalidArgumentException $e) {
            return back()->with('error', $e->getMessage());
        }

        return redirect()
            ->route('admin.delivery-zones.index')
            ->with('success', true)
            ->with('message', 'Delivery zone deleted successfully.')
            ->with('code', 'delivery_zone_deleted');
    }

    public function toggleStatus(DeliveryZone $deliveryZone): RedirectResponse
    {
        $this->authorize('update', $deliveryZone);

        $updated = $this->zones->toggleActive($deliveryZone);
        $label = $updated->is_active ? 'activated' : 'deactivated';

        return back()
            ->with('success', true)
            ->with('message', "Delivery zone {$label} successfully.")
            ->with('code', 'delivery_zone_status_toggled');
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
