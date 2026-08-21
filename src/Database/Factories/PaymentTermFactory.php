<?php

namespace ME\MerchandisingTrace\Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use ME\MerchandisingTrace\Models\PaymentTerm;

class PaymentTermFactory extends Factory
{
    protected $model = PaymentTerm::class;

    public function definition(): array
    {
        return [
            'name' => 'Net ' . $this->faker->randomElement([30, 45, 60, 90]),
            'code' => strtoupper($this->faker->unique()->bothify('PT-###')),
            'days' => $this->faker->randomElement([30, 45, 60, 90]),
            'description' => $this->faker->sentence(),
            'is_active' => true,
        ];
    }
}
