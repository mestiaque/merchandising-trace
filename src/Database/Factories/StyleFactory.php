<?php

namespace ME\MerchandisingTrace\Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use ME\MerchandisingTrace\Models\Brand;
use ME\MerchandisingTrace\Models\Buyer;
use ME\MerchandisingTrace\Models\Style;

class StyleFactory extends Factory
{
    protected $model = Style::class;

    public function definition(): array
    {
        return [
            'style_no' => strtoupper($this->faker->unique()->bothify('STY-####')),
            'name' => $this->faker->words(3, true),
            'description' => $this->faker->sentence(),
            'buyer_id' => Buyer::factory(),
            'brand_id' => Brand::factory(),
            'is_active' => true,
        ];
    }
}
