<?php

declare(strict_types=1);

namespace App\Domains\Communication\Services;

use App\Domains\Identity\Entities\User;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class PushNotificationService
{
    private string $serverKey;

    public function __construct()
    {
        // For testing/mocking, default to 'mock-key' if not set
        $this->serverKey = config('services.fcm.key', 'mock-key');
    }

    public function sendToUser(User $user, string $title, string $body, array $data = []): void
    {
        // Get active devices with an FCM token
        $devices = $user->devices()->whereNotNull('fcm_token')->where('status', 'active')->get();

        if ($devices->isEmpty()) {
            return;
        }

        $tokens = $devices->pluck('fcm_token')->toArray();

        $this->sendToTokens($tokens, $title, $body, $data);
    }

    public function sendToTokens(array $tokens, string $title, string $body, array $data = []): void
    {
        if ($this->serverKey === 'mock-key') {
            Log::info('Mock FCM Push Notification Sent', [
                'tokens' => $tokens,
                'title' => $title,
                'body' => $body,
            ]);
            return;
        }

        $response = Http::withHeaders([
            'Authorization' => 'key=' . $this->serverKey,
            'Content-Type' => 'application/json',
        ])->post('https://fcm.googleapis.com/fcm/send', [
            'registration_ids' => $tokens,
            'notification' => [
                'title' => $title,
                'body' => $body,
            ],
            'data' => $data,
        ]);

        if ($response->failed()) {
            Log::error('FCM Push Notification Failed', [
                'response' => $response->body()
            ]);
        }
    }
}
