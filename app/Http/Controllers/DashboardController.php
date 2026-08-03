<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Modules\PublicSite\Services\CustomerPortalService;
use App\Modules\PublicSite\Services\SubscriberPortalService;
use App\Support\AdminDashboardService;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class DashboardController extends Controller
{
    public function __construct(
        private readonly AdminDashboardService $dashboard,
        private readonly CustomerPortalService $customerPortal,
        private readonly SubscriberPortalService $subscriberPortal,
    ) {}

    public function __invoke(Request $request): Response
    {
        $user = $request->user();
        abort_unless($user instanceof User, 401);

        if ($user->isSubscriber()) {
            return Inertia::render(
                'subscriber/dashboard',
                $this->subscriberPortal->dashboard($user),
            );
        }

        if ($user->isCustomer() && ! $user->isManagement()) {
            return Inertia::render(
                'customer/dashboard',
                $this->customerPortal->dashboard($user),
            );
        }

        abort_unless($user->allows('dashboard.view'), 403);

        $widgets = $user->dashboardWidgets();
        $payload = $this->dashboard->build($widgets);

        return Inertia::render('dashboard', [
            'widgets' => $widgets,
            'stats' => $payload['stats'],
            'recentOrders' => $payload['recent_orders'],
            'todaysSchedule' => $payload['todays_schedule'],
            'canViewEarnings' => $user->allows('reports.earnings'),
            'canViewProfitLoss' => $user->allows('reports.profit_loss'),
            'canViewPayments' => $user->allows('payments.view'),
        ]);
    }
}
