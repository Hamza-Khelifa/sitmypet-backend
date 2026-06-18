<?php

declare(strict_types=1);

namespace App\Domains\Identity\Listeners;

use App\Domains\Identity\Events\ThreatDetected;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Support\Facades\Log;

class SecurityAlertListener implements ShouldQueue
{
    use InteractsWithQueue;

    public function handle(ThreatDetected $event): void
    {
        Log::alert('SECURITY THREAT DETECTED', [
            'user_id' => $event->user?->id,
            'type' => $event->eventType,
            'severity' => $event->severity->value,
            'context' => $event->context,
            'ip' => $event->ipAddress,
        ]);

        if ($event->severity->value === 'CRITICAL') {
            // Ping Slack / PagerDuty / Sentry
        }
    }
}
