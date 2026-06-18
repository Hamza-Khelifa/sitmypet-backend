<?php

declare(strict_types=1);

namespace App\Domains\Marketplace\Policies;

use App\Domains\Identity\Entities\User;
use App\Domains\Marketplace\Entities\Demand;

class DemandPolicy
{
    public function view(User $user, Demand $demand): bool
    {
        return true; // Any verified user can view public demands
    }

    public function create(User $user): bool
    {
        return true;
    }

    public function update(User $user, Demand $demand): bool
    {
        return $user->id === $demand->owner_id;
    }

    public function delete(User $user, Demand $demand): bool
    {
        return $user->id === $demand->owner_id;
    }
}
