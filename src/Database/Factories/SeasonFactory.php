<?php

namespace ME\MerchandisingTrace\Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use ME\MerchandisingTrace\Models\Season;

class SeasonFactory extends Factory
{
    protected $model = Season::class;

    public function definition(): array
    {
        return [
            'name' => $this->faker->randomElement(['Spring/Summer', 'Autumn/Winter', 'Resort']) . ' ' . $this->faker->year(),
            'code' => strtoupper($this->faker->unique()->bothify('SSN-###')),
            'start_date' => $this->faker->dateTimeBetween('-6 months', 'now')->format('Y-m-d'),
            'end_date' => $this->faker->dateTimeBetween('now', '+6 months')->format('Y-m-d'),
            'is_active' => true,
        ];
    }
}
