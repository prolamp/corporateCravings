<?php

namespace App\Support;

/**
 * Stateless dish cost calculator.
 *
 * Flow: pick grocery expenses (chicken, rice, oil…) → enter how much of each
 * goes into the dish batch → enter how many portions the batch yields → get
 * total cost and cost per portion. Nothing is persisted.
 */
class DishCostCalculator
{
    /**
     * @param  list<array{unit_price: float|int|string, quantity: float|int|string}>  $lines
     *         Each line is one selected grocery expense's unit_price × qty used in the dish.
     * @return array{total_cost: float, cost_per_portion: float, line_costs: list<float>}
     */
    public static function estimate(array $lines, float $yieldPortions): array
    {
        $lineCosts = [];
        $total = 0.0;

        foreach ($lines as $line) {
            $cost = round((float) $line['unit_price'] * (float) $line['quantity'], 2);
            $lineCosts[] = $cost;
            $total += $cost;
        }

        $total = round($total, 2);
        $perPortion = $yieldPortions > 0 ? round($total / $yieldPortions, 2) : 0.0;

        return [
            'total_cost' => $total,
            'cost_per_portion' => $perPortion,
            'line_costs' => $lineCosts,
        ];
    }
}
