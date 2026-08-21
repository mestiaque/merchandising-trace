<?php

namespace ME\MerchandisingTrace\Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use ME\MerchandisingTrace\Models\Size;

class SizeFactory extends Factory
{
    protected $model = Size::class;

    public function definition(): array
    {
        return [
            'name' => $this->faker->randomElement(['XS', 'S', 'M', 'L', 'XL', 'XXL']),
            'sort_order' => $this->faker->numberBetween(1, 6),
            'is_active' => true,
        ];
    }
}
