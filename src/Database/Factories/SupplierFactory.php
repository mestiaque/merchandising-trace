<?php

namespace ME\MerchandisingTrace\Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use ME\MerchandisingTrace\Models\Supplier;

class SupplierFactory extends Factory
{
    protected $model = Supplier::class;

    public function definition(): array
    {
        return [
            'name' => $this->faker->company(),
            'code' => strtoupper($this->faker->unique()->bothify('SUP-###')),
            'address' => $this->faker->address(),
            'contact_person' => $this->faker->name(),
            'phone' => $this->faker->phoneNumber(),
            'email' => $this->faker->companyEmail(),
            'is_active' => true,
        ];
    }
}
