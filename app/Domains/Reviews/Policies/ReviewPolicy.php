<?php

declare(strict_types=1);

namespace App\Domains\Reviews\Policies;

use App\Domains\Identity\Entities\User;
use App\Domains\Marketplace\Entities\Booking;

class ReviewPolicy
{
    public function create(User $user, Booking $booking): bool
    {
        // Must be either the sitter or the owner to review
        return $user->id === $booking->sitter_id || $user->id === $booking->demand->owner_id;
    }
}
