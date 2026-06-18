<?php

declare(strict_types=1);

namespace App\Domains\Marketplace\Events;

use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

use Illuminate\Contracts\Broadcasting\ShouldBroadcastNow;

class BookingConfirmed implements ShouldBroadcastNow
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public function __construct(
        public readonly string $bookingId
    ) {}

    public function broadcastOn(): array
    {
        $booking = \App\Domains\Marketplace\Entities\Booking::with('demand')->find($this->bookingId);

        if (!$booking) {
            return [];
        }

        return [
            new \Illuminate\Broadcasting\PrivateChannel('user.' . $booking->demand->owner_id),
            new \Illuminate\Broadcasting\PrivateChannel('user.' . $booking->sitter_id),
        ];
    }
}
