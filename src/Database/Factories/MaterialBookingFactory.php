<?php

namespace ME\MerchandisingTrace\Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use ME\MerchandisingTrace\Models\MaterialBooking;
use ME\MerchandisingTrace\Models\Order;

class MaterialBookingFactory extends Factory
{
    protected $model = MaterialBooking::class;

    public function definition(): array
    {
        return [
            'order_id'      => Order::factory(),
            'material_type' => $this->faker->randomElement(MaterialBooking::MATERIAL_TYPES),
            'material_name' => ucfirst($this->faker->word()),
            'qty'           => $this->faker->randomFloat(4, 100, 5000),
            'booking_date'  => $this->faker->dateTimeBetween('-10 days', 'now')->format('Y-m-d'),
            'expected_date' => $this->faker->dateTimeBetween('now', '+20 days')->format('Y-m-d'),
            'status'        => 'booked',
        ];
    }
}
