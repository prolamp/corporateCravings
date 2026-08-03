<?php

namespace App\Modules\Reports\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Modules\Reports\Services\ReportService;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class ReportController extends Controller
{
    public function __construct(
        private readonly ReportService $reports,
    ) {}

    public function index(Request $request): Response
    {
        $this->authorizeAnyReport($request);

        return Inertia::render('admin/reports/index', [
            'reports' => $this->reports->availableReports($request->user()),
            'canManageBillSlips' => $request->user()?->can('bill_slips.manage') ?? false,
        ]);
    }

    public function orders(Request $request): Response
    {
        abort_unless($request->user()?->can('reports.orders'), 403);

        $range = $this->reports->resolveDateRange($request->only(['date_from', 'date_to']));

        return Inertia::render('admin/reports/orders', [
            'filters' => $range,
            'report' => $this->reports->ordersReport($range),
        ]);
    }

    public function contracts(Request $request): Response
    {
        abort_unless($request->user()?->can('reports.contracts'), 403);

        return Inertia::render('admin/reports/contracts', [
            'report' => $this->reports->contractsReport(),
        ]);
    }

    public function earnings(Request $request): Response
    {
        abort_unless($request->user()?->can('reports.earnings'), 403);

        $range = $this->reports->resolveDateRange($request->only(['date_from', 'date_to']));

        return Inertia::render('admin/reports/earnings', [
            'filters' => $range,
            'report' => $this->reports->earningsReport($range),
        ]);
    }

    public function profitLoss(Request $request): Response
    {
        abort_unless($request->user()?->can('reports.profit_loss'), 403);

        $range = $this->reports->resolveDateRange($request->only(['date_from', 'date_to']));

        return Inertia::render('admin/reports/profit-loss', [
            'filters' => $range,
            'report' => $this->reports->profitLossReport($range),
        ]);
    }

    private function authorizeAnyReport(Request $request): void
    {
        $user = $request->user();
        $allowed = $user
            && (
                $user->can('reports.orders')
                || $user->can('reports.contracts')
                || $user->can('reports.earnings')
                || $user->can('reports.profit_loss')
            );

        abort_unless($allowed, 403);
    }
}
