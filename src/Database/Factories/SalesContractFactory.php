<?php

namespace ME\MerchandisingTrace\Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use ME\MerchandisingTrace\Models\Buyer;
use ME\MerchandisingTrace\Models\Order;
use ME\MerchandisingTrace\Models\SalesContract;

class SalesContractFactory extends Factory
{
    protected $model = SalesContract::class;

    public function definition(): array
    {
        return [
            'order_id' => Order::factory(),
            'buyer_id' => Buyer::factory(),
            'contract_date' => $this->faker->dateTimeBetween('-1 month', 'now')->format('Y-m-d'),
            'terms' => $this->faker->paragraph(),
            'status' => 'draft',
        ];
    }
}
