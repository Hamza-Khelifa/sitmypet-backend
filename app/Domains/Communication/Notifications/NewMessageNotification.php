<?php

declare(strict_types=1);

namespace App\Domains\Communication\Notifications;

use App\Domains\Communication\Entities\Message;
use App\Domains\Notifications\Channels\FirebasePushChannel;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Notification;

class NewMessageNotification extends Notification implements ShouldQueue
{
    use Queueable;

    public function __construct(
        public readonly Message $messageEntity
    ) {}

    public function via($notifiable): array
    {
        return [FirebasePushChannel::class];
    }

    public function toFirebasePush($notifiable): array
    {
        return [
            'notification' => [
                'title' => 'New Message',
                'body' => mb_substr($this->messageEntity->content, 0, 100) . (mb_strlen($this->messageEntity->content) > 100 ? '...' : ''),
            ],
            'data' => [
                'type' => 'new_message',
                'conversation_id' => $this->messageEntity->conversation_id,
                'message_id' => $this->messageEntity->id,
            ],
        ];
    }
}
