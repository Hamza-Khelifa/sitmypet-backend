<?php

namespace Database\Factories;

use App\Domains\Marketplace\Entities\Booking;
use Illuminate\Database\Eloquent\Factories\Factory;

class BookingFactory extends Factory
{
    protected $model = Booking::class;

    public function definition(): array
    {
        return [
            'demand_id' => \App\Domains\Marketplace\Entities\Demand::factory(),
            'sitter_id' => \App\Domains\Identity\Entities\User::factory(),
            'status' => 'confirmed',
            'total_price' => 150.00,
        ];
    }
}
