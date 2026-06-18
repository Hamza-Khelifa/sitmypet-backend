<?php

declare(strict_types=1);

namespace App\Domains\Communication\Actions;

use App\Domains\Communication\DTOs\SendMessageDTO;
use App\Domains\Communication\Entities\Attachment;
use App\Domains\Communication\Entities\Message;
use App\Domains\Communication\Events\MessageSent;
use App\Domains\Communication\Repositories\Contracts\ConversationRepositoryInterface;
use App\Domains\Communication\Repositories\Contracts\MessageRepositoryInterface;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Event;
use InvalidArgumentException;

final class SendMessageAction
{
    public function __construct(
        private readonly ConversationRepositoryInterface $conversationRepository,
        private readonly MessageRepositoryInterface $messageRepository
    ) {}

    public function execute(SendMessageDTO $dto): Message
    {
        return DB::transaction(function () use ($dto) {
            $conversation = $this->conversationRepository->findById($dto->conversationId);
            
            if (!$conversation) {
                throw new InvalidArgumentException('Conversation not found');
            }

            $message = new Message([
                'conversation_id' => $conversation->id,
                'sender_id' => $dto->senderId,
                'type' => $dto->type->value,
                'content' => $dto->content,
            ]);

            $message = $this->messageRepository->save($message);

            // Handle Attachments (Simplified for scaffolding)
            foreach ($dto->attachments as $filePath) {
                $attachment = new Attachment([
                    'message_id' => $message->id,
                    'file_path' => $filePath,
                    'file_type' => 'unknown', // Would normally derive from file
                    'file_size' => 0,
                    'original_name' => basename($filePath),
                ]);
                $attachment->save();
            }

            // Update Conversation last message time
            $conversation->last_message_at = now();
            $this->conversationRepository->save($conversation);

            // Broadcast to Reverb
            Event::dispatch(new MessageSent($message->id));

            // Send Push Notification
            $recipientIds = $conversation->users()
                ->where('user_id', '!=', $dto->senderId)
                ->pluck('user_id');
                
            if ($recipientIds->isNotEmpty()) {
                $recipients = \App\Domains\Identity\Entities\User::whereIn('id', $recipientIds)->get();
                \Illuminate\Support\Facades\Notification::send($recipients, new \App\Domains\Communication\Notifications\NewMessageNotification($message));
            }

            // Dispatch Moderation Job to a background queue
            \App\Jobs\ModerateMessageJob::dispatch($message);

            return $message->load('attachments');
        });
    }
}
