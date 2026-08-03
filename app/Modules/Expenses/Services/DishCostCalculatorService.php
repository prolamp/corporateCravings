<?php

namespace App\Modules\Expenses\Services;

use App\Modules\Expenses\Models\Expense;
use App\Support\DishCostCalculator;
use App\Support\Money;
use Illuminate\Support\Str;

/**
 * Stateless dish cost estimate from grocery expense items.
 * Only expenses under the "Groceries" category are selectable (Chicken, Rice, Oil…).
 * Enter weight/qty used × that item's unit price → batch total (+ optional cost/portion).
 */
class DishCostCalculatorService
{
    public const GROCERY_CATEGORY = 'Groceries';

    /**
     * Latest grocery *items* (expense titles under Groceries), deduped by title.
     *
     * @return list<array{
     *     value: int,
     *     label: string,
     *     title: string,
     *     unit: string|null,
     *     unit_price: float,
     *     unit_price_formatted: string
     * }>
     */
    public function groceryOptions(): array
    {
        $expenses = Expense::query()
            ->whereHas('expenseCategory', function ($query): void {
                $query->where('name', self::GROCERY_CATEGORY)
                    ->where('is_active', true);
            })
            ->where('unit_price', '>', 0)
            ->orderByDesc('date')
            ->orderByDesc('id')
            ->limit(300)
            ->get(['id', 'title', 'unit', 'unit_price', 'date']);

        $seen = [];
        $options = [];

        foreach ($expenses as $expense) {
            $key = Str::lower(trim($expense->title));
            if ($key === '' || isset($seen[$key])) {
                continue;
            }
            $seen[$key] = true;

            $options[] = [
                'value' => $expense->id,
                'label' => $expense->title,
                'title' => $expense->title,
                'unit' => $expense->unit,
                'unit_price' => (float) $expense->unit_price,
                'unit_price_formatted' => Money::format((float) $expense->unit_price),
            ];
        }

        usort($options, fn (array $a, array $b) => strcasecmp($a['title'], $b['title']));

        return $options;
    }

    /**
     * @param  list<array{unit_price: float|int|string, quantity: float|int|string}>  $lines
     * @return array{
     *     total_cost: float,
     *     total_cost_formatted: string,
     *     cost_per_portion: float,
     *     cost_per_portion_formatted: string,
     *     line_costs: list<float>,
     *     line_costs_formatted: list<string>
     * }
     */
    public function estimate(array $lines, float $yieldPortions): array
    {
        $result = DishCostCalculator::estimate($lines, $yieldPortions);

        return [
            'total_cost' => $result['total_cost'],
            'total_cost_formatted' => Money::format($result['total_cost']),
            'cost_per_portion' => $result['cost_per_portion'],
            'cost_per_portion_formatted' => Money::format($result['cost_per_portion']),
            'line_costs' => $result['line_costs'],
            'line_costs_formatted' => array_map(
                fn (float $cost) => Money::format($cost),
                $result['line_costs'],
            ),
        ];
    }
}
