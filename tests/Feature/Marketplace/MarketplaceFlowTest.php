<?php

declare(strict_types=1);

namespace Tests\Feature\Marketplace;

use App\Domains\Identity\Entities\User;
use App\Domains\Pets\Entities\Pet;
use App\Domains\Marketplace\Entities\Demand;
use App\Domains\Marketplace\Entities\Bid;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class MarketplaceFlowTest extends TestCase
{
    use RefreshDatabase;

    public function test_full_marketplace_flow(): void
    {
        $owner = User::factory()->create();
        $sitter = User::factory()->create();
        $pet = Pet::factory()->create(['owner_id' => $owner->id]);

        // 1. Owner creates a Demand
        $response = $this->actingAs($owner)->postJson('/api/v1/marketplace/demands', [
            'pet_ids' => [$pet->id],
            'start_date' => now()->addDays(2)->toDateTimeString(),
            'end_date' => now()->addDays(5)->toDateTimeString(),
            'latitude' => 40.7128,
            'longitude' => -74.0060,
            'requirements' => 'Needs daily walks.',
        ]);

        $response->assertStatus(201);
        $demandId = $response->json('data.id');

        // 2. Sitter queries for nearby demands
        $response = $this->actingAs($sitter)->getJson('/api/v1/marketplace/demands/feed?lat=40.7120&lng=-74.0050');
        $response->assertStatus(200);
        $this->assertGreaterThan(0, count($response->json('data')));

        // 3. Sitter submits a bid
        $response = $this->actingAs($sitter)->postJson("/api/v1/marketplace/demands/{$demandId}/bids", [
            'proposed_rate' => 150.00,
            'cover_letter' => 'I can take care of your pet.',
        ]);

        $response->assertStatus(201);
        $bidId = $response->json('data.id');

        // 4. Owner accepts the bid
        $response = $this->actingAs($owner)->postJson("/api/v1/marketplace/bids/{$bidId}/accept");
        $response->assertStatus(200);
        
        $this->assertDatabaseHas('bids', [
            'id' => $bidId,
            'status' => 'accepted'
        ]);

        $this->assertDatabaseHas('demands', [
            'id' => $demandId,
            'status' => 'assigned'
        ]);

        $this->assertDatabaseHas('bookings', [
            'demand_id' => $demandId,
            'sitter_id' => $sitter->id,
            'status' => 'confirmed'
        ]);
    }
}
