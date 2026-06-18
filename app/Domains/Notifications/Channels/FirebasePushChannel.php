<?php

declare(strict_types=1);

namespace App\Domains\Notifications\Channels;

use App\Domains\Notifications\Services\FCMService;
use Illuminate\Notifications\Notification;
use Illuminate\Support\Facades\Log;

class FirebasePushChannel
{
    public function __construct(
        private readonly FCMService $fcmService
    ) {}

    public function send($notifiable, Notification $notification): void
    {
        if (!method_exists($notification, 'toFirebasePush')) {
            return;
        }

        // The model (e.g. User) should implement routeNotificationForFcm
        $token = $notifiable->routeNotificationFor('fcm', $notification);

        if (!$token) {
            Log::debug('No FCM token found for notifiable');
            return;
        }

        $message = $notification->toFirebasePush($notifiable);

        $this->fcmService->send(
            $token,
            $message['notification'] ?? [],
            $message['data'] ?? []
        );
    }
}
