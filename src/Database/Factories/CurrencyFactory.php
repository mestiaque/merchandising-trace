<?php

namespace ME\MerchandisingTrace\Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use ME\MerchandisingTrace\Models\Currency;

class CurrencyFactory extends Factory
{
    protected $model = Currency::class;

    public function definition(): array
    {
        return [
            'name' => $this->faker->randomElement(['US Dollar', 'Euro', 'Bangladeshi Taka', 'British Pound']),
            'code' => strtoupper($this->faker->unique()->lexify('???')),
            'symbol' => '$',
            'exchange_rate' => $this->faker->randomFloat(4, 0.5, 120),
            'is_active' => true,
        ];
    }
}
