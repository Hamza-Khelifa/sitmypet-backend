<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api\V1\Communication;

use App\Domains\Communication\Entities\Conversation;
use App\Domains\Communication\Entities\Message;
use App\Domains\Communication\Enums\MessageType;
use App\Events\MessageSent;
use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class MessageController extends Controller
{
    /**
     * Send a new message in a conversation.
     */
    public function store(Request $request, string $conversationId): JsonResponse
    {
        $request->validate([
            'content' => 'required|string|max:2000',
        ]);

        $user = $request->user();

        // Ensure user is part of the conversation
        $conversation = Conversation::whereHas('users', function ($query) use ($user) {
            $query->where('user_id', $user->id);
        })->findOrFail($conversationId);

        $message = Message::create([
            'conversation_id' => $conversation->id,
            'sender_id' => $user->id,
            'type' => MessageType::TEXT,
            'content' => $request->input('content'),
            'is_flagged' => false,
        ]);

        $conversation->update(['last_message_at' => now()]);

        // Load sender for broadcasting
        $message->load('sender:id,email');

        // Broadcast to private channel
        broadcast(new MessageSent($message))->toOthers();

        // Dispatch Async Job to Moderate Message via OpenAI Gateway
        \App\Jobs\ModerateMessageJob::dispatch($message);

        return response()->json($message, 201);
    }
}
