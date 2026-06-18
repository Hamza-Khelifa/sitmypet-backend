<?php

declare(strict_types=1);

namespace App\Domains\Communication\Repositories\Eloquent;

use App\Domains\Communication\Entities\Message;
use App\Domains\Communication\Repositories\Contracts\MessageRepositoryInterface;
use Illuminate\Support\Collection;

final class MessageRepository implements MessageRepositoryInterface
{
    public function findById(string $id): ?Message
    {
        return Message::find($id);
    }

    public function findByConversationId(string $conversationId, int $limit = 50, int $offset = 0): Collection
    {
        return Message::where('conversation_id', $conversationId)
                      ->with('attachments', 'sender')
                      ->orderByDesc('created_at')
                      ->skip($offset)
                      ->take($limit)
                      ->get();
    }

    public function save(Message $message): Message
    {
        $message->save();
        return $message;
    }
}
