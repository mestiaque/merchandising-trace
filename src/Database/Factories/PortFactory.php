<?php

namespace ME\MerchandisingTrace\Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use ME\MerchandisingTrace\Models\Port;

class PortFactory extends Factory
{
    protected $model = Port::class;

    public function definition(): array
    {
        return [
            'name' => $this->faker->city() . ' Port',
            'code' => strtoupper($this->faker->unique()->bothify('PRT-###')),
            'country' => $this->faker->country(),
            'port_type' => $this->faker->randomElement(['sea', 'air', 'land']),
            'is_active' => true,
        ];
    }
}
