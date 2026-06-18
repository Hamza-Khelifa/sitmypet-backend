<?php

namespace Database\Factories;

use App\Domains\Pets\Entities\Pet;
use Illuminate\Database\Eloquent\Factories\Factory;

class PetFactory extends Factory
{
    protected $model = Pet::class;

    public function definition(): array
    {
        return [
            'owner_id' => \App\Domains\Identity\Entities\User::factory(),
            'name' => $this->faker->firstName,
            'species' => 'dog',
            'breed' => 'Labrador',
            'birth_date' => now()->subYears(2),
            'weight' => 20.5,
            'behavior_notes' => 'None',
        ];
    }
}
