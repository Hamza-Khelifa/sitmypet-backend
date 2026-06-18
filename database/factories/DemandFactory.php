<?php

namespace Database\Factories;

use App\Domains\Marketplace\Entities\Demand;
use Illuminate\Database\Eloquent\Factories\Factory;

class DemandFactory extends Factory
{
    protected $model = Demand::class;

    public function definition(): array
    {
        return [
            'owner_id' => \App\Domains\Identity\Entities\User::factory(),
            'location' => null, // Assuming tests mock this or ignore PostGIS
            'start_date' => now()->addDays(1),
            'end_date' => now()->addDays(3),
            'status' => 'open',
            'requirements' => 'Need a sitter',
        ];
    }
}
