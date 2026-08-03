<?php

namespace App\Modules\Expenses\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Modules\Expenses\Http\Requests\EstimateDishCostRequest;
use App\Modules\Expenses\Services\DishCostCalculatorService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class DishCostCalculatorController extends Controller
{
    public function __construct(
        private readonly DishCostCalculatorService $calculator,
    ) {}

    public function show(Request $request): Response
    {
        abort_unless($request->user()?->can('cost_calculator.view'), 403);

        return Inertia::render('admin/cost-calculator/index', [
            'groceryOptions' => $this->calculator->groceryOptions(),
        ]);
    }

    public function estimate(EstimateDishCostRequest $request): JsonResponse
    {
        $data = $request->validated();

        return response()->json(
            $this->calculator->estimate(
                $data['lines'],
                (float) $data['yield_portions'],
            ),
        );
    }
}
