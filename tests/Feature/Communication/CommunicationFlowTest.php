<?php

declare(strict_types=1);

namespace Tests\Feature\Communication;

use App\Domains\Identity\Entities\User;
use App\Domains\Communication\Entities\Conversation;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Event;
use Tests\TestCase;

class CommunicationFlowTest extends TestCase
{
    use RefreshDatabase;

    public function test_users_can_start_conversation_and_send_messages(): void
    {
        $owner = User::factory()->create();
        $sitter = User::factory()->create();

        // 1. Start Conversation
        $response = $this->actingAs($owner)->postJson('/api/v1/communication/conversations', [
            'participant_ids' => [$sitter->id]
        ]);

        $response->assertStatus(201);
        $conversationId = $response->json('data.id');

        $this->assertDatabaseHas('conversation_user', [
            'conversation_id' => $conversationId,
            'user_id' => $owner->id
        ]);
        $this->assertDatabaseHas('conversation_user', [
            'conversation_id' => $conversationId,
            'user_id' => $sitter->id
        ]);

        // Mock Event dispatching to prevent actual Reverb broadcasting during tests
        Event::fake([
            \App\Domains\Communication\Events\MessageSent::class,
            \App\Domains\Communication\Events\MessageRead::class,
        ]);

        // 2. Send Message
        $response = $this->actingAs($owner)->postJson("/api/v1/communication/conversations/{$conversationId}/messages", [
            'content' => 'Hello Sitter!',
            'type' => 'text'
        ]);

        $response->assertStatus(201);
        $messageId = $response->json('data.id');

        $this->assertDatabaseHas('messages', [
            'id' => $messageId,
            'content' => 'Hello Sitter!',
            'sender_id' => $owner->id
        ]);

        Event::assertDispatched(\App\Domains\Communication\Events\MessageSent::class);

        // 3. Mark as Read
        $response = $this->actingAs($sitter)->patchJson("/api/v1/communication/conversations/{$conversationId}/messages/{$messageId}/read");
        
        $response->assertStatus(200);

        $this->assertDatabaseHas('conversation_user', [
            'conversation_id' => $conversationId,
            'user_id' => $sitter->id,
            'last_read_message_id' => $messageId
        ]);

        Event::assertDispatched(\App\Domains\Communication\Events\MessageRead::class);
    }
}
