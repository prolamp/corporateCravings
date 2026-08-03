<?php

namespace App\Modules\Subscribers\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Modules\Subscribers\Http\Requests\StoreSubscriberRequest;
use App\Modules\Subscribers\Http\Requests\UpdateSubscriberRequest;
use App\Modules\Subscribers\Services\BillSlipService;
use App\Modules\Subscribers\Services\SubscriberService;
use App\Modules\Subscribers\Models\SubscriberBillSlip;
use App\Support\StatusRegistry;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;
use InvalidArgumentException;

class SubscriberController extends Controller
{
    public function __construct(
        private readonly SubscriberService $subscribers,
        private readonly BillSlipService $billSlips,
    ) {}

    public function index(Request $request): Response
    {
        abort_unless($request->user()?->can('contracts.manage'), 403);

        $filters = [
            'search' => $request->string('search')->trim()->toString() ?: null,
            'status' => $request->string('status')->toString() ?: null,
        ];

        $actor = $request->user();
        abort_unless($actor instanceof User, 401);

        $paginator = $this->subscribers->paginate($filters);

        return Inertia::render('admin/subscribers/index', [
            'subscribers' => [
                'data' => $paginator->getCollection()
                    ->map(fn (User $user) => $this->subscribers->toArray($user, $actor))
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
            'canCreate' => $actor->can('users.create_subscriber'),
            'canManage' => $actor->can('users.update'),
        ]);
    }

    public function create(Request $request): Response
    {
        abort_unless($request->user()?->can('users.create_subscriber'), 403);

        return Inertia::render('admin/subscribers/create', [
            'statusOptions' => $this->statusOptions(),
        ]);
    }

    public function store(StoreSubscriberRequest $request): RedirectResponse
    {
        abort_unless($request->user()?->can('users.create_subscriber'), 403);

        try {
            $subscriber = $this->subscribers->create($request->user(), $request->validated());
        } catch (InvalidArgumentException $e) {
            return back()->withInput()->with('error', $e->getMessage());
        }

        return redirect()
            ->route('admin.subscribers.show', $subscriber)
            ->with('success', true)
            ->with('message', 'Subscriber created successfully.')
            ->with('code', 'subscriber_created');
    }

    public function show(Request $request, User $subscriber): Response
    {
        abort_unless($request->user()?->can('contracts.manage'), 403);
        $this->subscribers->assertSubscriber($subscriber);

        $subscriber->load([
            'roles',
            'subscribeContracts' => fn ($q) => $q->with('package')->latest('id')->limit(1),
        ]);

        $recentSlips = SubscriberBillSlip::query()
            ->whereHas('contract', fn ($q) => $q->where('user_id', $subscriber->id))
            ->withCount('items')
            ->latest('id')
            ->limit(5)
            ->get()
            ->map(fn (SubscriberBillSlip $slip) => $this->billSlips->toArray($slip))
            ->values()
            ->all();

        $contractId = $subscriber->subscribeContracts->first()?->id;

        return Inertia::render('admin/subscribers/show', [
            'subscriber' => $this->subscribers->toArray($subscriber, $request->user()),
            'dailyBills' => $this->subscribers->deliveredDailyBills($subscriber),
            'recentSlips' => $recentSlips,
            'canManage' => $request->user()?->can('update', $subscriber) ?? false,
            'canManageBillSlips' => $request->user()?->can('bill_slips.manage') ?? false,
            'createBillSlipUrl' => $contractId
                ? '/admin/bill-slips/create?contract_id='.$contractId.'&user_id='.$subscriber->id
                : '/admin/bill-slips/create?user_id='.$subscriber->id,
        ]);
    }

    public function edit(Request $request, User $subscriber): Response
    {
        $this->authorize('update', $subscriber);
        $this->subscribers->assertSubscriber($subscriber);

        return Inertia::render('admin/subscribers/edit', [
            'subscriber' => $this->subscribers->toArray($subscriber->load('roles'), $request->user()),
            'statusOptions' => $this->statusOptions(),
        ]);
    }

    public function update(UpdateSubscriberRequest $request, User $subscriber): RedirectResponse
    {
        $this->authorize('update', $subscriber);
        $this->subscribers->assertSubscriber($subscriber);

        try {
            $this->subscribers->update($request->user(), $subscriber, $request->validated());
        } catch (InvalidArgumentException $e) {
            return back()->withInput()->with('error', $e->getMessage());
        }

        return redirect()
            ->route('admin.subscribers.show', $subscriber)
            ->with('success', true)
            ->with('message', 'Subscriber updated successfully.')
            ->with('code', 'subscriber_updated');
    }

    public function destroy(Request $request, User $subscriber): RedirectResponse
    {
        $this->authorize('delete', $subscriber);
        $this->subscribers->assertSubscriber($subscriber);

        try {
            $this->subscribers->delete($request->user(), $subscriber);
        } catch (InvalidArgumentException $e) {
            return back()->with('error', $e->getMessage());
        }

        return redirect()
            ->route('admin.subscribers.index')
            ->with('success', true)
            ->with('message', 'Subscriber deleted successfully.')
            ->with('code', 'subscriber_deleted');
    }

    public function toggleStatus(Request $request, User $subscriber): RedirectResponse
    {
        $this->authorize('update', $subscriber);
        $this->subscribers->assertSubscriber($subscriber);

        try {
            $updated = $this->subscribers->toggleActive($request->user(), $subscriber);
        } catch (InvalidArgumentException $e) {
            return back()->with('error', $e->getMessage());
        }

        $label = $updated->status === 'active' ? 'activated' : 'deactivated';

        return back()
            ->with('success', true)
            ->with('message', "Subscriber {$label} successfully.")
            ->with('code', 'subscriber_status_toggled');
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
            StatusRegistry::all('user'),
        );
    }
}
