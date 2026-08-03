<?php

namespace App\Modules\Subscribers\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Modules\Subscribers\Http\Requests\StoreSubscribeContractRequest;
use App\Modules\Subscribers\Http\Requests\UpdateSubscribeContractRequest;
use App\Modules\Subscribers\Models\SubscribeContract;
use App\Modules\Subscribers\Models\SubscriberBillSlip;
use App\Modules\Subscribers\Services\BillSlipService;
use App\Modules\Subscribers\Services\SubscribeContractService;
use App\Support\StatusRegistry;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;
use InvalidArgumentException;

class SubscribeContractController extends Controller
{
    public function __construct(
        private readonly SubscribeContractService $contracts,
        private readonly BillSlipService $billSlips,
    ) {}

    public function index(Request $request): Response
    {
        $this->authorize('viewAny', SubscribeContract::class);

        $filters = [
            'search' => $request->string('search')->trim()->toString() ?: null,
            'status' => $request->string('status')->toString() ?: null,
            'user_id' => $request->integer('user_id') ?: null,
        ];

        $paginator = $this->contracts->paginate($filters);

        return Inertia::render('admin/contracts/index', [
            'contracts' => [
                'data' => $paginator->getCollection()
                    ->map(fn (SubscribeContract $contract) => $this->contracts->toArray($contract))
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
            'canManage' => $request->user()?->can('contracts.manage') ?? false,
        ]);
    }

    public function create(Request $request): Response
    {
        $this->authorize('create', SubscribeContract::class);

        return Inertia::render('admin/contracts/create', [
            'statusOptions' => $this->statusOptions(),
            'packageOptions' => $this->contracts->packageOptions(),
            'subscriberOptions' => $this->contracts->subscriberOptions(),
            'mealTypeOptions' => $this->contracts->mealTypeOptions(),
            'dayOptions' => $this->contracts->dayOptions(),
            'defaults' => [
                'user_id' => $request->integer('subscriber_id') ?: null,
            ],
        ]);
    }

    public function store(StoreSubscribeContractRequest $request): RedirectResponse
    {
        $this->authorize('create', SubscribeContract::class);

        try {
            $contract = $this->contracts->create($request->validated());
        } catch (InvalidArgumentException $e) {
            return back()->withInput()->with('error', $e->getMessage());
        }

        return redirect()
            ->route('admin.contracts.show', $contract)
            ->with('success', true)
            ->with('message', 'Contract created successfully.')
            ->with('code', 'contract_created');
    }

    public function show(SubscribeContract $contract): Response
    {
        $this->authorize('view', $contract);

        $contract->load(['user', 'package', 'packageHistory' => fn ($q) => $q->latest('effective_from')])
            ->loadCount(['schedules', 'orders']);

        $recentSlips = SubscriberBillSlip::query()
            ->where('subscribe_contract_id', $contract->id)
            ->withCount('items')
            ->latest('id')
            ->limit(5)
            ->get()
            ->map(fn (SubscriberBillSlip $slip) => $this->billSlips->toArray($slip))
            ->values()
            ->all();

        return Inertia::render('admin/contracts/show', [
            'contract' => $this->contracts->toArray($contract),
            'recentSlips' => $recentSlips,
            'canManage' => request()->user()?->can('contracts.manage') ?? false,
            'canManageBillSlips' => request()->user()?->can('bill_slips.manage') ?? false,
        ]);
    }

    public function edit(SubscribeContract $contract): Response
    {
        $this->authorize('update', $contract);

        $contract->load(['user', 'package'])->loadCount(['schedules', 'orders']);

        return Inertia::render('admin/contracts/edit', [
            'contract' => $this->contracts->toArray($contract),
            'statusOptions' => $this->statusOptions(),
            'packageOptions' => $this->contracts->packageOptions($contract->package_id),
            'subscriberOptions' => $this->contracts->subscriberOptions(),
            'mealTypeOptions' => $this->contracts->mealTypeOptions(),
            'dayOptions' => $this->contracts->dayOptions(),
        ]);
    }

    public function update(UpdateSubscribeContractRequest $request, SubscribeContract $contract): RedirectResponse
    {
        $this->authorize('update', $contract);

        try {
            $this->contracts->update($contract, $request->validated());
        } catch (InvalidArgumentException $e) {
            return back()->withInput()->with('error', $e->getMessage());
        }

        return redirect()
            ->route('admin.contracts.show', $contract)
            ->with('success', true)
            ->with('message', 'Contract updated successfully.')
            ->with('code', 'contract_updated');
    }

    public function destroy(SubscribeContract $contract): RedirectResponse
    {
        $this->authorize('delete', $contract);

        try {
            $this->contracts->delete($contract);
        } catch (InvalidArgumentException $e) {
            return back()->with('error', $e->getMessage());
        }

        return redirect()
            ->route('admin.contracts.index')
            ->with('success', true)
            ->with('message', 'Contract deleted successfully.')
            ->with('code', 'contract_deleted');
    }

    public function toggleStatus(SubscribeContract $contract): RedirectResponse
    {
        $this->authorize('update', $contract);

        $updated = $this->contracts->toggleActive($contract);
        $label = $updated->status === 'active' ? 'activated' : 'deactivated';

        return back()
            ->with('success', true)
            ->with('message', "Contract {$label} successfully.")
            ->with('code', 'contract_status_toggled');
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
