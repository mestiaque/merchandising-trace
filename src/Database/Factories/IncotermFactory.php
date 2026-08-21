<?php

namespace ME\MerchandisingTrace\Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use ME\MerchandisingTrace\Models\Incoterm;

class IncotermFactory extends Factory
{
    protected $model = Incoterm::class;

    public function definition(): array
    {
        return [
            'name' => $this->faker->randomElement(['Free On Board', 'Cost Insurance Freight', 'Ex Works', 'Free Carrier']),
            'code' => strtoupper($this->faker->unique()->lexify('???')),
            'description' => $this->faker->sentence(),
            'is_active' => true,
        ];
    }
}
