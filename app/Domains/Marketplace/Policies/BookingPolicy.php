<?php

declare(strict_types=1);

namespace App\Domains\Marketplace\Policies;

use App\Domains\Identity\Entities\User;
use App\Domains\Marketplace\Entities\Booking;

class BookingPolicy
{
    public function view(User $user, Booking $booking): bool
    {
        return $user->id === $booking->sitter_id || $user->id === $booking->demand->owner_id;
    }

    public function cancel(User $user, Booking $booking): bool
    {
        return $user->id === $booking->sitter_id || $user->id === $booking->demand->owner_id;
    }
}
