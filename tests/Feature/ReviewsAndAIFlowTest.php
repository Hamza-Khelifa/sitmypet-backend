<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Domains\Identity\Entities\User;
use App\Domains\Marketplace\Entities\Demand;
use App\Domains\Marketplace\Entities\Bid;
use App\Domains\Marketplace\Entities\Booking;
use App\Domains\AiGateway\Integrations\Contracts\AiGatewayInterface;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ReviewsAndAIFlowTest extends TestCase
{
    use RefreshDatabase;

    public function test_owner_can_submit_review_for_confirmed_booking(): void
    {
        $owner = User::factory()->create();
        $sitter = User::factory()->create();

        $demand = Demand::factory()->create(['owner_id' => $owner->id]);
        $bid = Bid::factory()->create(['demand_id' => $demand->id, 'sitter_id' => $sitter->id]);
        
        $booking = Booking::factory()->create([
            'demand_id' => $demand->id,
            'sitter_id' => $sitter->id,
            'status' => 'confirmed'
        ]);

        $response = $this->actingAs($owner)->postJson("/api/v1/reviews/bookings/{$booking->id}", [
            'rating' => 5,
            'content' => 'Amazing sitter, highly recommended!'
        ]);

        $response->assertStatus(201);
        
        $this->assertDatabaseHas('reviews', [
            'booking_id' => $booking->id,
            'reviewer_id' => $owner->id,
            'reviewee_id' => $sitter->id,
            'rating' => 5,
        ]);
    }

    public function test_ai_gateway_moderates_toxic_review_content(): void
    {
        // Mock the interface directly
        $this->mock(AiGatewayInterface::class, function ($mock) {
            $mock->shouldReceive('moderateContent')->with('This is a terrible toxic review!')->andReturn(true);
        });

        // We can simulate an action call here to prove moderation is triggered
        $action = $this->app->make(\App\Domains\AiGateway\Actions\ModerateContentAction::class);
        
        $user = User::factory()->create();
        $dto = new \App\Domains\AiGateway\DTOs\ModerateContentDTO($user->id, 'This is a terrible toxic review!');
        
        $isFlagged = $action->execute($dto);

        $this->assertTrue($isFlagged);
        $this->assertDatabaseHas('ai_interactions', [
            'user_id' => $user->id,
            'type' => 'moderation',
            'is_flagged' => true
        ]);
    }

    public function test_user_can_ask_ai_assistant(): void
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user)->postJson("/api/v1/ai/assistant/ask", [
            'prompt' => 'How many times a day should I feed my cat?'
        ]);

        $response->assertStatus(200);
        $response->assertJsonStructure(['data' => ['prompt', 'response']]);

        // Verify audit log
        $this->assertDatabaseHas('ai_interactions', [
            'user_id' => $user->id,
            'type' => 'assistant',
            'prompt' => 'How many times a day should I feed my cat?'
        ]);
    }
}
