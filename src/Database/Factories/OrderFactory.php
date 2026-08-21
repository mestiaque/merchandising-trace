<?php

namespace ME\MerchandisingTrace\Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use ME\MerchandisingTrace\Models\Buyer;
use ME\MerchandisingTrace\Models\Order;
use ME\MerchandisingTrace\Models\Style;

class OrderFactory extends Factory
{
    protected $model = Order::class;

    public function definition(): array
    {
        return [
            'buyer_id' => Buyer::factory(),
            'style_id' => Style::factory(),
            'order_qty' => 0,
            'delivery_date' => $this->faker->dateTimeBetween('now', '+3 months')->format('Y-m-d'),
            'price' => $this->faker->randomFloat(4, 2, 30),
            'currency' => 'USD',
            'status' => 'pending',
        ];
    }
}
