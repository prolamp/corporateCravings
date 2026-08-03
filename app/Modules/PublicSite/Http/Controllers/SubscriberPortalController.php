<?php

namespace App\Modules\PublicSite\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Modules\PublicSite\Services\SubscriberPortalService;
use App\Modules\Subscribers\Models\SubscriberBillSlip;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class SubscriberPortalController extends Controller
{
    public function __construct(
        private readonly SubscriberPortalService $portal,
    ) {}

    public function dashboard(Request $request): Response
    {
        $user = $this->subscriber($request);

        return Inertia::render(
            'subscriber/dashboard',
            $this->portal->dashboard($user),
        );
    }

    public function schedules(Request $request): Response
    {
        $user = $this->subscriber($request);
        abort_unless($user->can('portal.subscriber.schedules'), 403);

        return Inertia::render(
            'subscriber/schedules',
            $this->portal->schedules($user, $request->string('month')->toString() ?: null),
        );
    }

    public function history(Request $request): Response
    {
        $user = $this->subscriber($request);
        abort_unless($user->can('portal.subscriber.history'), 403);

        return Inertia::render(
            'subscriber/history',
            $this->portal->history($user),
        );
    }

    public function contract(Request $request): Response
    {
        $user = $this->subscriber($request);
        abort_unless($user->can('portal.subscriber.contract'), 403);

        return Inertia::render(
            'subscriber/contract',
            $this->portal->contract($user),
        );
    }

    public function bills(Request $request): Response
    {
        $user = $this->subscriber($request);
        abort_unless($user->can('portal.subscriber.bills'), 403);

        return Inertia::render(
            'subscriber/bills/index',
            $this->portal->bills($user, [
                'status' => $request->string('status')->toString() ?: null,
            ]),
        );
    }

    public function billShow(Request $request, SubscriberBillSlip $bill_slip): Response
    {
        $user = $this->subscriber($request);
        abort_unless($user->can('portal.subscriber.bills'), 403);
        $this->authorize('view', $bill_slip);

        return Inertia::render(
            'subscriber/bills/show',
            $this->portal->billShow($user, $bill_slip),
        );
    }

    public function billReceipt(Request $request, SubscriberBillSlip $bill_slip): Response
    {
        $user = $this->subscriber($request);
        abort_unless($user->can('portal.subscriber.bills'), 403);
        $this->authorize('view', $bill_slip);

        return Inertia::render(
            'subscriber/bills/receipt',
            $this->portal->billReceipt($user, $bill_slip),
        );
    }

    private function subscriber(Request $request): User
    {
        $user = $request->user();
        abort_unless($user instanceof User, 401);
        abort_unless($user->can('portal.subscriber.dashboard'), 403);

        return $user;
    }
}
