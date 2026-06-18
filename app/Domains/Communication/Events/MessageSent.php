<?php

declare(strict_types=1);

namespace App\Domains\Communication\Events;

use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcastNow;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class MessageSent implements ShouldBroadcastNow
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public function __construct(
        public readonly string $messageId
    ) {}

    public function broadcastOn(): array
    {
        // Must retrieve the message to get conversation_id, or pass it directly.
        // For efficiency, usually passed directly. Let's assume we lookup here or it's passed.
        $message = \App\Domains\Communication\Entities\Message::find($this->messageId);

        return [
            new \Illuminate\Broadcasting\PresenceChannel('conversation.' . $message->conversation_id),
        ];
    }
}
