<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api\V1\Communication;

use App\Domains\Communication\Entities\Conversation;
use App\Domains\Identity\Entities\User;
use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class ConversationController extends Controller
{
    /**
     * List all conversations for the authenticated user.
     */
    public function index(Request $request): JsonResponse
    {
        $user = $request->user();

        $conversations = Conversation::whereHas('users', function ($query) use ($user) {
            $query->where('user_id', $user->id);
        })
        ->with(['users:id,email', 'messages' => function ($query) {
            $query->latest()->limit(1);
        }])
        ->orderByDesc('last_message_at')
        ->paginate(20);

        return response()->json($conversations);
    }

    /**
     * Start a new conversation or return the existing one.
     */
    public function store(Request $request): JsonResponse
    {
        $request->validate([
            'participant_id' => 'required|uuid|exists:users,id',
            'title' => 'nullable|string|max:255',
        ]);

        $user = $request->user();
        $participantId = $request->participant_id;

        if ($user->id === $participantId) {
            return response()->json(['message' => 'Cannot start a conversation with yourself.'], 422);
        }

        // Check if a direct conversation already exists
        $existingConversation = Conversation::whereHas('users', function ($query) use ($user) {
            $query->where('user_id', $user->id);
        })
        ->whereHas('users', function ($query) use ($participantId) {
            $query->where('user_id', $participantId);
        })
        ->with('users:id,email')
        ->first();

        if ($existingConversation) {
            return response()->json($existingConversation);
        }

        // Create new conversation
        DB::beginTransaction();
        try {
            $conversation = Conversation::create([
                'title' => $request->title,
                'last_message_at' => now(),
            ]);

            $conversation->users()->attach([
                $user->id => ['joined_at' => now()],
                $participantId => ['joined_at' => now()],
            ]);

            DB::commit();

            $conversation->load('users:id,email');
            
            return response()->json($conversation, 201);
        } catch (\Exception $e) {
            DB::rollBack();
            throw $e;
        }
    }

    /**
     * Get conversation details and messages.
     */
    public function show(Request $request, string $id): JsonResponse
    {
        $user = $request->user();

        $conversation = Conversation::whereHas('users', function ($query) use ($user) {
            $query->where('user_id', $user->id);
        })
        ->with(['users:id,email', 'messages' => function ($query) {
            // Do not load toxic/flagged messages unless we are admins.
            // For now, we filter out flagged messages
            $query->where('is_flagged', false)->orderByDesc('created_at')->limit(50);
        }])
        ->findOrFail($id);

        return response()->json($conversation);
    }
}
