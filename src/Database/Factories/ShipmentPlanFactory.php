<?php

namespace ME\MerchandisingTrace\Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use ME\MerchandisingTrace\Models\Order;
use ME\MerchandisingTrace\Models\ShipmentPlan;

class ShipmentPlanFactory extends Factory
{
    protected $model = ShipmentPlan::class;

    public function definition(): array
    {
        return [
            'order_id'         => Order::factory(),
            'planned_date'     => $this->faker->dateTimeBetween('now', '+30 days')->format('Y-m-d'),
            'planned_qty'      => $this->faker->numberBetween(500, 10000),
            'destination_port' => $this->faker->city() . ' Port',
            'mode'             => $this->faker->randomElement(['sea', 'air']),
            'status'           => 'planned',
        ];
    }
}
