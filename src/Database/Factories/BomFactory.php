<?php

namespace ME\MerchandisingTrace\Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use ME\MerchandisingTrace\Models\Bom;
use ME\MerchandisingTrace\Models\Style;

class BomFactory extends Factory
{
    protected $model = Bom::class;

    public function definition(): array
    {
        return [
            'style_id' => Style::factory(),
            'version'  => 1,
            'status'   => 'draft',
        ];
    }
}
