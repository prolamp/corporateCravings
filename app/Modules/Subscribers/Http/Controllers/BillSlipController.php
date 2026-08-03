<?php

namespace App\Modules\Subscribers\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Modules\Subscribers\Http\Requests\StoreBillSlipRequest;
use App\Modules\Subscribers\Http\Requests\UpdateBillSlipRequest;
use App\Modules\Subscribers\Models\SubscribeContract;
use App\Modules\Subscribers\Models\SubscriberBillSlip;
use App\Modules\Subscribers\Services\BillSlipService;
use App\Support\StatusRegistry;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;
use InvalidArgumentException;

class BillSlipController extends Controller
{
    public function __construct(
        private readonly BillSlipService $slips,
    ) {}

    public function index(Request $request): Response
    {
        $this->authorize('viewAny', SubscriberBillSlip::class);

        $filters = [
            'search' => $request->string('search')->trim()->toString() ?: null,
            'status' => $request->string('status')->toString() ?: null,
            'user_id' => $request->integer('user_id') ?: null,
            'contract_id' => $request->integer('contract_id') ?: null,
            'date_from' => $request->string('date_from')->toString() ?: null,
            'date_to' => $request->string('date_to')->toString() ?: null,
        ];

        $paginator = $this->slips->paginate($filters);
        $actor = $request->user();

        return Inertia::render('admin/bill-slips/index', [
            'slips' => [
                'data' => $paginator->getCollection()
                    ->map(fn (SubscriberBillSlip $slip) => $this->slips->toArray($slip))
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
            'contractOptions' => $this->slips->contractOptions(),
            'canManage' => $actor?->can('bill_slips.manage') ?? false,
        ]);
    }

    public function create(Request $request): Response
    {
        $this->authorize('create', SubscriberBillSlip::class);

        $contractId = $request->integer('contract_id') ?: null;
        $userId = $request->integer('user_id') ?: null;
        $from = $request->string('from')->toString() ?: null;
        $to = $request->string('to')->toString() ?: null;

        if (! $from && ! $to && $request->boolean('last_month')) {
            $from = now()->subMonth()->startOfMonth()->toDateString();
            $to = now()->subMonth()->endOfMonth()->toDateString();
        }

        $contract = $contractId
            ? SubscribeContract::query()->with('user')->find($contractId)
            : null;

        if ($contract && $userId && (int) $contract->user_id !== $userId) {
            $contract = null;
            $contractId = null;
        }

        if (! $contract && $userId) {
            $contract = SubscribeContract::query()
                ->where('user_id', $userId)
                ->latest('id')
                ->first();
            $contractId = $contract?->id;
        }

        return Inertia::render('admin/bill-slips/create', [
            'contractOptions' => $this->slips->contractOptions($userId ?: null),
            'selectedContractId' => $contractId,
            'filters' => [
                'from' => $from,
                'to' => $to,
                'user_id' => $userId,
            ],
            'eligibleDeliveries' => $contract
                ? $this->slips->eligibleDeliveryOptions($contract, $from, $to)
                : [],
            'contract' => $contract ? [
                'id' => $contract->id,
                'label' => ($contract->user?->company_name ?: $contract->user?->name ?: 'Contract')
                    .' · #'.$contract->id,
                'user_id' => $contract->user_id,
                'company_name' => $contract->user?->company_name ?: $contract->user?->name,
            ] : null,
        ]);
    }

    public function store(StoreBillSlipRequest $request): RedirectResponse
    {
        $this->authorize('create', SubscriberBillSlip::class);

        $data = $request->validated();
        $contract = SubscribeContract::query()->findOrFail((int) $data['subscribe_contract_id']);

        try {
            $slip = $this->slips->create(
                $request->user(),
                $contract,
                $data['schedule_ids'],
                $data['adjustment'] ?? 0,
                $data['notes'] ?? null,
            );
        } catch (InvalidArgumentException $e) {
            return back()->withErrors(['schedule_ids' => $e->getMessage()]);
        }

        return redirect()
            ->route('admin.bill-slips.show', $slip)
            ->with('success', true)
            ->with('message', 'Bill slip created successfully.')
            ->with('code', 'bill_slip_created');
    }

    public function show(SubscriberBillSlip $bill_slip): Response
    {
        $this->authorize('view', $bill_slip);

        return Inertia::render('admin/bill-slips/show', [
            'slip' => $this->slips->toArray($bill_slip, includeItems: true),
            'canManage' => request()->user()?->can('bill_slips.manage') ?? false,
        ]);
    }

    public function edit(Request $request, SubscriberBillSlip $bill_slip): Response
    {
        $this->authorize('update', $bill_slip);

        abort_unless($bill_slip->isEditable(), 403, 'Paid or cancelled slips cannot be edited.');

        $bill_slip->loadMissing('contract.user');
        $from = $request->string('from')->toString() ?: null;
        $to = $request->string('to')->toString() ?: null;

        $eligible = $this->slips->eligibleDeliveryOptions(
            $bill_slip->contract,
            $from,
            $to,
            $bill_slip,
        );

        return Inertia::render('admin/bill-slips/edit', [
            'slip' => $this->slips->toArray($bill_slip, includeItems: true),
            'eligibleDeliveries' => $eligible,
            'filters' => ['from' => $from, 'to' => $to],
        ]);
    }

    public function update(UpdateBillSlipRequest $request, SubscriberBillSlip $bill_slip): RedirectResponse
    {
        $this->authorize('update', $bill_slip);

        try {
            $this->slips->update($request->user(), $bill_slip, $request->validated());
        } catch (InvalidArgumentException $e) {
            return back()->withErrors(['schedule_ids' => $e->getMessage()]);
        }

        return redirect()
            ->route('admin.bill-slips.show', $bill_slip)
            ->with('success', true)
            ->with('message', 'Bill slip updated successfully.')
            ->with('code', 'bill_slip_updated');
    }

    public function markPaid(Request $request, SubscriberBillSlip $bill_slip): RedirectResponse
    {
        $this->authorize('markPaid', $bill_slip);

        try {
            $this->slips->markPaid($request->user(), $bill_slip);
        } catch (InvalidArgumentException $e) {
            return back()->withErrors(['status' => $e->getMessage()]);
        }

        return back()
            ->with('success', true)
            ->with('message', 'Bill slip marked as paid.')
            ->with('code', 'bill_slip_paid');
    }

    public function markUnpaid(Request $request, SubscriberBillSlip $bill_slip): RedirectResponse
    {
        $this->authorize('markPaid', $bill_slip);

        try {
            $this->slips->markUnpaid($request->user(), $bill_slip);
        } catch (InvalidArgumentException $e) {
            return back()->withErrors(['status' => $e->getMessage()]);
        }

        return back()
            ->with('success', true)
            ->with('message', 'Bill slip marked as unpaid.')
            ->with('code', 'bill_slip_unpaid');
    }

    public function cancel(Request $request, SubscriberBillSlip $bill_slip): RedirectResponse
    {
        $this->authorize('cancel', $bill_slip);

        try {
            $this->slips->cancel($request->user(), $bill_slip);
        } catch (InvalidArgumentException $e) {
            return back()->withErrors(['status' => $e->getMessage()]);
        }

        return redirect()
            ->route('admin.bill-slips.index')
            ->with('success', true)
            ->with('message', 'Bill slip cancelled.')
            ->with('code', 'bill_slip_cancelled');
    }

    public function receipt(SubscriberBillSlip $bill_slip): Response
    {
        $this->authorize('view', $bill_slip);

        return Inertia::render('admin/bill-slips/receipt', [
            'slip' => $this->slips->downloadPayload($bill_slip),
        ]);
    }

    /**
     * @return list<array{value: string, label: string}>
     */
    private function statusOptions(): array
    {
        return array_map(
            fn (array $row) => [
                'value' => $row['code'],
                'label' => $row['label'],
            ],
            StatusRegistry::all('bill_slip'),
        );
    }
}
