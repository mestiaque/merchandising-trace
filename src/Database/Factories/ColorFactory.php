<?php

namespace ME\MerchandisingTrace\Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use ME\MerchandisingTrace\Models\Color;

class ColorFactory extends Factory
{
    protected $model = Color::class;

    public function definition(): array
    {
        return [
            'name' => $this->faker->safeColorName(),
            'code' => strtoupper($this->faker->unique()->bothify('CLR-###')),
            'is_active' => true,
        ];
    }
}
