<?php

declare(strict_types=1);

namespace App\Domains\Marketplace\Events;

use App\Domains\Marketplace\Entities\Booking;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class BookingForceCancelled
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public function __construct(public readonly Booking $booking)
    {
    }
}
