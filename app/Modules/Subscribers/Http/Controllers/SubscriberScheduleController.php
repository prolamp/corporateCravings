<?php

namespace App\Modules\Subscribers\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Modules\Subscribers\Http\Requests\GenerateSubscriberScheduleRequest;
use App\Modules\Subscribers\Http\Requests\UpdateSubscriberScheduleRequest;
use App\Modules\Subscribers\Models\SubscribeContract;
use App\Modules\Subscribers\Models\SubscriberSchedule;
use App\Modules\Subscribers\Services\SubscriberScheduleService;
use App\Support\StatusRegistry;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;
use InvalidArgumentException;

class SubscriberScheduleController extends Controller
{
    public function __construct(
        private readonly SubscriberScheduleService $schedules,
    ) {}

    public function index(Request $request): Response
    {
        $this->authorize('viewAny', SubscriberSchedule::class);

        $filters = [
            'year' => $request->integer('year') ?: now()->year,
            'month' => $request->integer('month') ?: now()->month,
            'contract_id' => $request->integer('contract_id') ?: null,
            'status' => $request->string('status')->toString() ?: null,
            'search' => $request->string('search')->trim()->toString() ?: null,
            'view' => $request->string('view')->toString() ?: 'month',
        ];

        $payload = $this->schedules->monthPayload($filters);

        $selectedContract = null;
        $dishOptions = [];
        if ($filters['contract_id']) {
            $selectedContract = SubscribeContract::query()
                ->with('user')
                ->find($filters['contract_id']);
            if ($selectedContract) {
                $dishOptions = $this->schedules->dishOptionsForContract($selectedContract);
            }
        }

        return Inertia::render('admin/schedules/index', [
            'calendar' => $payload,
            'filters' => $filters,
            'statusOptions' => $this->statusOptions(),
            'contractOptions' => $this->schedules->contractOptions(),
            'dishOptions' => $dishOptions,
            'canManage' => $request->user()?->can('schedules.manage') ?? false,
        ]);
    }

    public function generate(GenerateSubscriberScheduleRequest $request): RedirectResponse
    {
        $this->authorize('create', SubscriberSchedule::class);

        $data = $request->validated();
        $contract = SubscribeContract::query()->findOrFail($data['contract_id']);

        try {
            $result = $this->schedules->generateMonth(
                $contract,
                (int) $data['year'],
                (int) $data['month'],
            );
        } catch (InvalidArgumentException $e) {
            return back()->with('error', $e->getMessage());
        }

        return back()
            ->with('success', true)
            ->with(
                'message',
                "Generated {$result['created']} day(s); {$result['skipped']} already existed.",
            )
            ->with('code', 'schedule_generated');
    }

    public function shuffle(GenerateSubscriberScheduleRequest $request): RedirectResponse
    {
        $this->authorize('create', SubscriberSchedule::class);

        $data = $request->validated();
        $contract = SubscribeContract::query()->findOrFail($data['contract_id']);

        try {
            $result = $this->schedules->shuffleMonth(
                $contract,
                (int) $data['year'],
                (int) $data['month'],
            );
        } catch (InvalidArgumentException $e) {
            return back()->with('error', $e->getMessage());
        }

        return back()
            ->with('success', true)
            ->with('message', "Shuffled {$result['updated']} day(s).")
            ->with('code', 'schedule_shuffled');
    }

    public function update(
        UpdateSubscriberScheduleRequest $request,
        SubscriberSchedule $schedule,
    ): RedirectResponse {
        $this->authorize('update', $schedule);

        try {
            $this->schedules->update($schedule, $request->validated());
        } catch (InvalidArgumentException $e) {
            return back()->with('error', $e->getMessage());
        }

        return back()
            ->with('success', true)
            ->with('message', 'Schedule day updated.')
            ->with('code', 'schedule_updated');
    }

    public function destroy(SubscriberSchedule $schedule): RedirectResponse
    {
        $this->authorize('delete', $schedule);

        try {
            $this->schedules->delete($schedule);
        } catch (InvalidArgumentException $e) {
            return back()->with('error', $e->getMessage());
        }

        return back()
            ->with('success', true)
            ->with('message', 'Schedule day removed.')
            ->with('code', 'schedule_deleted');
    }

    public function deliver(SubscriberSchedule $schedule): RedirectResponse
    {
        $this->authorize('update', $schedule);

        try {
            $this->schedules->markDelivered($schedule);
        } catch (InvalidArgumentException $e) {
            return back()->with('error', $e->getMessage());
        }

        return back()
            ->with('success', true)
            ->with('message', 'Marked delivered and billed.')
            ->with('code', 'schedule_delivered');
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
            StatusRegistry::all('schedule'),
        );
    }
}
