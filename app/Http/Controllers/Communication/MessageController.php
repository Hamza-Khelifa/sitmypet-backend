<?php

declare(strict_types=1);

namespace App\Http\Controllers\Communication;

use App\Domains\Communication\Actions\MarkMessageAsReadAction;
use App\Domains\Communication\Actions\SendMessageAction;
use App\Domains\Communication\DTOs\MarkAsReadDTO;
use App\Domains\Communication\DTOs\SendMessageDTO;
use App\Domains\Communication\Enums\MessageType;
use App\Domains\Communication\Repositories\Contracts\ConversationRepositoryInterface;
use App\Domains\Communication\Repositories\Contracts\MessageRepositoryInterface;
use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class MessageController extends Controller
{
    public function __construct(
        private readonly MessageRepositoryInterface $messageRepository,
        private readonly ConversationRepositoryInterface $conversationRepository,
        private readonly SendMessageAction $sendMessageAction,
        private readonly MarkMessageAsReadAction $markMessageAsReadAction
    ) {}

    public function index(Request $request, string $conversationId): JsonResponse
    {
        $conversation = $this->conversationRepository->findById($conversationId);
        if (!$conversation) {
            return response()->json(['message' => 'Conversation not found'], 404);
        }

        $limit = (int) $request->query('limit', 50);
        $offset = (int) $request->query('offset', 0);

        $messages = $this->messageRepository->findByConversationId($conversationId, $limit, $offset);

        return response()->json(['data' => $messages]);
    }

    public function store(Request $request, string $conversationId): JsonResponse
    {
        $validated = $request->validate([
            'content' => ['nullable', 'string'],
            'type' => ['nullable', 'string', 'in:text,image,system'],
            'attachments' => ['nullable', 'array'],
        ]);

        $dto = new SendMessageDTO(
            conversationId: $conversationId,
            senderId: $request->user()->id,
            content: $validated['content'] ?? null,
            type: MessageType::tryFrom($validated['type'] ?? 'text') ?? MessageType::TEXT,
            attachments: $validated['attachments'] ?? []
        );

        $message = $this->sendMessageAction->execute($dto);

        return response()->json([
            'message' => 'Message sent.',
            'data' => $message
        ], 201);
    }

    public function markAsRead(Request $request, string $conversationId, string $messageId): JsonResponse
    {
        $dto = new MarkAsReadDTO(
            conversationId: $conversationId,
            userId: $request->user()->id,
            messageId: $messageId
        );

        $this->markMessageAsReadAction->execute($dto);

        return response()->json(['message' => 'Message marked as read.']);
    }
}
