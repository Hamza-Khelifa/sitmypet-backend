<?php

declare(strict_types=1);

namespace App\Domains\Communication\Actions;

use App\Domains\Communication\DTOs\MarkAsReadDTO;
use App\Domains\Communication\Events\MessageRead;
use App\Domains\Communication\Repositories\Contracts\ConversationRepositoryInterface;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Event;
use InvalidArgumentException;

final class MarkMessageAsReadAction
{
    public function __construct(
        private readonly ConversationRepositoryInterface $conversationRepository
    ) {}

    public function execute(MarkAsReadDTO $dto): void
    {
        $conversation = $this->conversationRepository->findById($dto->conversationId);

        if (!$conversation) {
            throw new InvalidArgumentException('Conversation not found');
        }

        // Update pivot table
        DB::table('conversation_user')
            ->where('conversation_id', $conversation->id)
            ->where('user_id', $dto->userId)
            ->update([
                'last_read_message_id' => $dto->messageId,
                'updated_at' => now(),
            ]);

        // Broadcast to other participants via Reverb
        Event::dispatch(new MessageRead($dto->conversationId, $dto->messageId, $dto->userId));
    }
}
