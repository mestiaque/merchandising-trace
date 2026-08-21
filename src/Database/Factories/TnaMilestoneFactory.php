<?php

namespace ME\MerchandisingTrace\Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use ME\MerchandisingTrace\Models\Order;
use ME\MerchandisingTrace\Models\TnaMilestone;

class TnaMilestoneFactory extends Factory
{
    protected $model = TnaMilestone::class;

    public function definition(): array
    {
        return [
            'order_id'       => Order::factory(),
            'milestone_name' => $this->faker->randomElement(TnaMilestone::MILESTONES),
            'planned_date'   => $this->faker->dateTimeBetween('-10 days', '+30 days')->format('Y-m-d'),
            'status'         => 'pending',
        ];
    }
}
