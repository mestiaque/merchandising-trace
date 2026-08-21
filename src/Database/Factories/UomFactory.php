<?php

namespace ME\MerchandisingTrace\Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use ME\MerchandisingTrace\Models\Uom;

class UomFactory extends Factory
{
    protected $model = Uom::class;

    public function definition(): array
    {
        return [
            'name' => $this->faker->randomElement(['Meter', 'Yard', 'Piece', 'Dozen', 'Kilogram']),
            'short_name' => $this->faker->randomElement(['mtr', 'yd', 'pc', 'dz', 'kg']),
            'is_active' => true,
        ];
    }
}
