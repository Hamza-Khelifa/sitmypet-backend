<?php

declare(strict_types=1);

namespace App\Domains\Marketplace\Events;

use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

use Illuminate\Contracts\Broadcasting\ShouldBroadcastNow;

class BidSubmitted implements ShouldBroadcastNow
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public function __construct(
        public readonly string $bidId
    ) {}

    public function broadcastOn(): array
    {
        $bid = \App\Domains\Marketplace\Entities\Bid::with('demand')->find($this->bidId);

        if (!$bid || !$bid->demand) {
            return [];
        }

        return [
            new \Illuminate\Broadcasting\PrivateChannel('user.' . $bid->demand->owner_id),
        ];
    }
}
