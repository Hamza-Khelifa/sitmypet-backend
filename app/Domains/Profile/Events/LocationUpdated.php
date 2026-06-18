<?php

declare(strict_types=1);

namespace App\Domains\Profile\Events;

use App\Domains\Profile\ValueObjects\LocationPoint;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class LocationUpdated
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public function __construct(
        public readonly string $sitterProfileId,
        public readonly LocationPoint $location
    ) {}
}
