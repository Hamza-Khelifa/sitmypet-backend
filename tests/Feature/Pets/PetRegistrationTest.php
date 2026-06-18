<?php

declare(strict_types=1);

namespace Tests\Feature\Pets;

use App\Domains\Identity\Entities\User;
use App\Domains\Pets\Entities\Pet;
use App\Domains\Pets\Enums\SpeciesType;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class PetRegistrationTest extends TestCase
{
    use RefreshDatabase;

    public function test_user_can_register_a_pet(): void
    {
        /** @var User $user */
        $user = User::factory()->create();

        $response = $this->actingAs($user)->postJson('/api/v1/pets', [
            'name' => 'Max',
            'species' => SpeciesType::DOG->value,
            'breed' => 'Golden Retriever',
            'birth_date' => '2020-05-15',
            'weight' => 30.5,
        ]);

        $response->assertStatus(201)
                 ->assertJsonPath('data.name', 'Max')
                 ->assertJsonPath('data.species', 'dog');

        $this->assertDatabaseHas('pets', [
            'owner_id' => $user->id,
            'name' => 'Max',
            'breed' => 'Golden Retriever',
        ]);
    }

    public function test_user_can_log_vaccination_for_pet(): void
    {
        /** @var User $user */
        $user = User::factory()->create();
        
        $pet = Pet::factory()->create([
            'owner_id' => $user->id,
        ]);

        $response = $this->actingAs($user)->postJson("/api/v1/pets/{$pet->id}/vaccinations", [
            'name' => 'Rabies',
            'date_administered' => '2023-01-10',
            'next_due_date' => '2024-01-10',
        ]);

        $response->assertStatus(201)
                 ->assertJsonPath('data.name', 'Rabies');

        $this->assertDatabaseHas('vaccinations', [
            'pet_id' => $pet->id,
            'name' => 'Rabies',
        ]);
    }
}
