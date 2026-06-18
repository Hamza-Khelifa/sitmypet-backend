<?php

namespace Database\Factories;

use App\Domains\Marketplace\Entities\Bid;
use Illuminate\Database\Eloquent\Factories\Factory;

class BidFactory extends Factory
{
    protected $model = Bid::class;

    public function definition(): array
    {
        return [
            'demand_id' => \App\Domains\Marketplace\Entities\Demand::factory(),
            'sitter_id' => \App\Domains\Identity\Entities\User::factory(),
            'proposed_rate' => 150.00,
            'status' => 'pending',
            'cover_letter' => 'I can do this!',
        ];
    }
}
