<?php

declare(strict_types=1);

namespace App\Domains\Identity\Events;

use App\Domains\Identity\Entities\User;
use App\Domains\Identity\Enums\SecurityEventSeverity;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class ThreatDetected
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public function __construct(
        public readonly ?User $user,
        public readonly string $eventType,
        public readonly SecurityEventSeverity $severity,
        public readonly array $context = [],
        public readonly ?string $ipAddress = null
    ) {
    }
}
