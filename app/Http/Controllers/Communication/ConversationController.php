<?php

declare(strict_types=1);

namespace App\Http\Controllers\Communication;

use App\Domains\Communication\Actions\StartConversationAction;
use App\Domains\Communication\DTOs\StartConversationDTO;
use App\Domains\Communication\Repositories\Contracts\ConversationRepositoryInterface;
use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class ConversationController extends Controller
{
    public function __construct(
        private readonly ConversationRepositoryInterface $conversationRepository,
        private readonly StartConversationAction $startConversationAction
    ) {}

    public function index(Request $request): JsonResponse
    {
        $conversations = $this->conversationRepository->findByUserId($request->user()->id);
        return response()->json(['data' => $conversations]);
    }

    public function store(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'participant_ids' => ['required', 'array', 'min:1'],
            'participant_ids.*' => ['required', 'uuid', 'exists:users,id'],
            'title' => ['nullable', 'string', 'max:255'],
        ]);

        // Automatically add the creator to the conversation
        $participantIds = array_unique(array_merge($validated['participant_ids'], [$request->user()->id]));

        $dto = new StartConversationDTO($participantIds, $validated['title'] ?? null);
        $conversation = $this->startConversationAction->execute($dto);

        return response()->json([
            'message' => 'Conversation started.',
            'data' => $conversation
        ], 201);
    }

    public function show(Request $request, string $id): JsonResponse
    {
        $conversation = $this->conversationRepository->findById($id);

        if (!$conversation) {
            return response()->json(['message' => 'Conversation not found'], 404);
        }

        // Authorization should happen here
        // $this->authorize('view', $conversation);

        return response()->json(['data' => $conversation]);
    }
}
