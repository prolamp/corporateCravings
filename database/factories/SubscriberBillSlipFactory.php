<?php

namespace Database\Factories;

use App\Modules\Subscribers\Models\SubscribeContract;
use App\Modules\Subscribers\Models\SubscriberBillSlip;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

/**
 * @extends Factory<SubscriberBillSlip>
 */
class SubscriberBillSlipFactory extends Factory
{
    protected $model = SubscriberBillSlip::class;

    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $start = now()->startOfMonth();
        $end = now()->endOfMonth();
        $subtotal = fake()->numberBetween(5000, 50000);

        return [
            'subscribe_contract_id' => SubscribeContract::factory(),
            'slip_number' => 'CC-BS-'.Str::upper(Str::random(8)),
            'period_start' => $start->toDateString(),
            'period_end' => $end->toDateString(),
            'subtotal' => $subtotal,
            'adjustment' => 0,
            'total' => $subtotal,
            'status' => 'issued',
            'paid_at' => null,
            'paid_by' => null,
            'notes' => null,
            'created_by' => null,
        ];
    }

    public function paid(): static
    {
        return $this->state(fn () => [
            'status' => 'paid',
            'paid_at' => now(),
        ]);
    }

    public function cancelled(): static
    {
        return $this->state(fn () => ['status' => 'cancelled']);
    }
}
