<?php

declare(strict_types=1);

namespace App\Domains\Communication\Actions;

use App\Domains\Communication\DTOs\StartConversationDTO;
use App\Domains\Communication\Entities\Conversation;
use App\Domains\Communication\Repositories\Contracts\ConversationRepositoryInterface;
use Illuminate\Support\Facades\DB;

final class StartConversationAction
{
    public function __construct(
        private readonly ConversationRepositoryInterface $conversationRepository
    ) {}

    public function execute(StartConversationDTO $dto): Conversation
    {
        return DB::transaction(function () use ($dto) {
            // In a real app, you might want to check if a 1-on-1 conversation already exists
            // between these exact users before creating a new one.

            $conversation = new Conversation([
                'title' => $dto->title,
                'last_message_at' => now(),
            ]);

            $this->conversationRepository->save($conversation);

            // Attach participants
            $conversation->participants()->attach($dto->participantIds);

            return $conversation;
        });
    }
}
