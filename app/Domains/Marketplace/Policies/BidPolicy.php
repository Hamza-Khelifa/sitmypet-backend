<?php

declare(strict_types=1);

namespace App\Domains\Marketplace\Policies;

use App\Domains\Identity\Entities\User;
use App\Domains\Marketplace\Entities\Bid;
use App\Domains\Marketplace\Entities\Demand;

class BidPolicy
{
    public function view(User $user, Bid $bid): bool
    {
        return $user->id === $bid->sitter_id || $user->id === $bid->demand->owner_id;
    }

    public function create(User $user, Demand $demand): bool
    {
        // Owner cannot bid on their own demand
        return $user->id !== $demand->owner_id;
    }

    public function accept(User $user, Bid $bid): bool
    {
        // Only the demand owner can accept a bid
        return $user->id === $bid->demand->owner_id;
    }
}
