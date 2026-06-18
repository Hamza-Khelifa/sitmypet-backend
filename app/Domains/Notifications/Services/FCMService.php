<?php

declare(strict_types=1);

namespace App\Domains\Notifications\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class FCMService
{
    private string $projectId;

    public function __construct()
    {
        // Typically from config('services.firebase.project_id')
        $this->projectId = config('services.firebase.project_id', 'sitmypet-dev');
    }

    /**
     * Send an FCM notification via the HTTP v1 API.
     */
    public function send(string $token, array $notification, array $data = []): bool
    {
        // Graceful degradation for scaffolding without real credentials
        if (!config('services.firebase.credentials_path')) {
            Log::info("FCM MOCK: Sending Push Notification to token {$token}", [
                'notification' => $notification,
                'data' => $data,
            ]);
            return true;
        }

        try {
            $accessToken = $this->getAccessToken();

            $payload = [
                'message' => [
                    'token' => $token,
                    'notification' => $notification,
                    'data' => $data,
                ],
            ];

            $response = Http::withToken($accessToken)
                ->post("https://fcm.googleapis.com/v1/projects/{$this->projectId}/messages:send", $payload);

            if ($response->failed()) {
                Log::error('FCM sending failed', ['response' => $response->body()]);
                return false;
            }

            return true;
        } catch (\Exception $e) {
            Log::error('FCM exception', ['message' => $e->getMessage()]);
            return false;
        }
    }

    private function getAccessToken(): string
    {
        // In a real app, this would use google/auth library to fetch an OAuth2 token
        // using the firebase-credentials.json service account key.
        return 'mocked_oauth_token';
    }
}
