<?php

namespace Database\Factories;

use App\Models\User;
use App\Modules\Orders\Models\Order;
use App\Support\Enums\OrderChannel;
use App\Support\Enums\OrderType;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

/**
 * @extends Factory<Order>
 */
class OrderFactory extends Factory
{
    protected $model = Order::class;

    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $subtotal = fake()->numberBetween(500, 5000);
        $deliveryFee = fake()->randomElement([0, 150, 250]);

        return [
            'order_number' => 'CC-'.Str::upper(Str::random(10)),
            'user_id' => User::factory(),
            'type' => OrderType::Individual,
            'channel' => OrderChannel::Web,
            'status' => 'pending',
            'payment_method' => 'cod',
            'customer_name' => fake()->name(),
            'customer_phone' => fake()->numerify('03#########'),
            'customer_email' => null,
            'delivery_address' => fake()->address(),
            'delivery_zone_id' => null,
            'delivery_fee' => $deliveryFee,
            'subtotal' => $subtotal,
            'list_amount' => $subtotal,
            'discount_amount' => 0,
            'discount_percent' => 0,
            'quoted_amount' => $subtotal + $deliveryFee,
            'amount_paid' => null,
            'placed_at' => now(),
        ];
    }

    /**
     * Bulk/event deal entered by staff after a WhatsApp negotiation.
     */
    public function event(): static
    {
        return $this->state(function () {
            $list = fake()->numberBetween(20000, 90000);
            $discount = fake()->numberBetween(1000, 8000);

            return [
                'type' => OrderType::Event,
                'channel' => OrderChannel::Admin,
                'list_amount' => $list,
                'discount_amount' => $discount,
                'quoted_amount' => $list - $discount,
            ];
        });
    }

    public function paid(): static
    {
        return $this->afterCreating(function (Order $order) {
            $order->forceFill([
                'status' => 'completed',
                'amount_paid' => $order->quoted_amount,
            ])->save();
        });
    }

    public function status(string $status): static
    {
        return $this->state(fn () => ['status' => $status]);
    }
}
