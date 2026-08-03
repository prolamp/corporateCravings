<?php

namespace App\Modules\Orders\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Modules\Orders\Http\Requests\StoreManualOrderRequest;
use App\Modules\Orders\Http\Requests\UpdateManualOrderRequest;
use App\Modules\Orders\Http\Requests\UpdateOrderStatusRequest;
use App\Modules\Orders\Models\Order;
use App\Modules\Orders\Services\OrderService;
use App\Support\StatusRegistry;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;
use InvalidArgumentException;

class OrderController extends Controller
{
    public function __construct(
        private readonly OrderService $orders,
    ) {}

    public function index(Request $request): Response
    {
        $this->authorize('viewAny', Order::class);

        $filters = [
            'search' => $request->string('search')->trim()->toString() ?: null,
            'type' => $request->string('type')->toString() ?: null,
            'channel' => $request->string('channel')->toString() ?: null,
            'status' => $request->string('status')->toString() ?: null,
            'date_from' => $request->string('date_from')->toString() ?: null,
            'date_to' => $request->string('date_to')->toString() ?: null,
        ];

        $paginator = $this->orders->paginate($filters);
        $actor = $request->user();

        return Inertia::render('admin/orders/index', [
            'orders' => [
                'data' => $paginator->getCollection()
                    ->map(fn (Order $order) => $this->orders->toArray($order))
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
            'typeOptions' => $this->orders->typeOptions(),
            'channelOptions' => $this->orders->channelOptions(),
            'canUpdateStatus' => $actor?->can('orders.update_status') ?? false,
            'canManage' => $actor?->can('orders.manage') ?? false,
        ]);
    }

    public function create(): Response
    {
        $this->authorize('create', Order::class);

        return Inertia::render('admin/orders/create', $this->formProps());
    }

    public function store(StoreManualOrderRequest $request): RedirectResponse
    {
        $this->authorize('create', Order::class);

        try {
            $order = $this->orders->createManual($request->validated(), $request->user());
        } catch (InvalidArgumentException $e) {
            return back()->withInput()->with('error', $e->getMessage());
        }

        return redirect()
            ->route('admin.orders.show', $order)
            ->with('success', true)
            ->with('message', 'Order created successfully.')
            ->with('code', 'order_created');
    }

    public function show(Order $order): Response
    {
        $this->authorize('view', $order);

        $order->loadCount('items');

        return Inertia::render('admin/orders/show', [
            'order' => $this->orders->toArray($order, detailed: true),
            'statusOptions' => $this->statusOptions(),
            'canUpdateStatus' => request()->user()?->can('updateStatus', $order) ?? false,
            'canManage' => request()->user()?->can('orders.manage') ?? false,
        ]);
    }

    public function edit(Order $order): Response
    {
        $this->authorize('update', $order);

        if (! ($this->orders->toArray($order)['can_edit'] ?? false)) {
            abort(403, 'This order cannot be edited.');
        }

        $order->load(['items.addons'])->loadCount('items');

        return Inertia::render('admin/orders/edit', [
            ...$this->formProps(),
            'order' => $this->orders->toArray($order, detailed: true),
        ]);
    }

    public function update(UpdateManualOrderRequest $request, Order $order): RedirectResponse
    {
        $this->authorize('update', $order);

        try {
            $this->orders->updateManual($order, $request->validated(), $request->user());
        } catch (InvalidArgumentException $e) {
            return back()->withInput()->with('error', $e->getMessage());
        }

        return redirect()
            ->route('admin.orders.show', $order)
            ->with('success', true)
            ->with('message', 'Order updated successfully.')
            ->with('code', 'order_updated');
    }

    public function updateStatus(UpdateOrderStatusRequest $request, Order $order): RedirectResponse
    {
        $this->authorize('updateStatus', $order);

        $data = $request->validated();

        try {
            $this->orders->updateStatus(
                $order,
                $data['status'],
                $request->user(),
                $data['note'] ?? null,
                $data['delivery_person_name'] ?? null,
            );
        } catch (InvalidArgumentException $e) {
            return back()->with('error', $e->getMessage());
        }

        return back()
            ->with('success', true)
            ->with('message', 'Order status updated.')
            ->with('code', 'order_status_updated');
    }

    /**
     * @return array<string, mixed>
     */
    private function formProps(): array
    {
        return [
            'statusOptions' => $this->statusOptions(),
            'typeOptions' => $this->orders->manualTypeOptions(),
            'zoneOptions' => $this->orders->zoneOptions(),
            'variationOptions' => $this->orders->variationOptions(),
            'addonOptions' => $this->orders->addonOptions(),
        ];
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
            StatusRegistry::all('order'),
        );
    }
}
