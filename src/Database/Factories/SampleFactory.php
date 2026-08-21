<?php

namespace ME\MerchandisingTrace\Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use ME\MerchandisingTrace\Models\Buyer;
use ME\MerchandisingTrace\Models\Sample;
use ME\MerchandisingTrace\Models\Size;
use ME\MerchandisingTrace\Models\Style;

class SampleFactory extends Factory
{
    protected $model = Sample::class;

    public function definition(): array
    {
        return [
            'buyer_id' => Buyer::factory(),
            'style_id' => Style::factory(),
            'sample_type' => $this->faker->randomElement(Sample::SAMPLE_TYPES),
            'qty' => $this->faker->numberBetween(1, 5),
            'size_id' => Size::factory(),
            'request_date' => $this->faker->dateTimeBetween('-2 months', 'now')->format('Y-m-d'),
            'status' => 'pending',
        ];
    }
}
