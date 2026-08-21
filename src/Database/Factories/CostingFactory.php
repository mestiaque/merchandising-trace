<?php

namespace ME\MerchandisingTrace\Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use ME\MerchandisingTrace\Models\Costing;
use ME\MerchandisingTrace\Models\Order;

class CostingFactory extends Factory
{
    protected $model = Costing::class;

    public function definition(): array
    {
        return [
            'order_id'              => Order::factory(),
            'type'                  => 'pre',
            'fob_price'             => $this->faker->randomFloat(4, 3, 15),
            'fabric_cost'           => $this->faker->randomFloat(4, 1, 5),
            'trim_cost'             => $this->faker->randomFloat(4, 0.2, 1.5),
            'wash_cost'             => $this->faker->randomFloat(4, 0, 0.5),
            'embroidery_print_cost' => $this->faker->randomFloat(4, 0, 0.8),
            'overhead_cost'         => $this->faker->randomFloat(4, 0.3, 1),
            'status'                => 'draft',
        ];
    }
}
