<?php

declare(strict_types=1);

namespace App\Domains\Pets\Policies;

use App\Domains\Identity\Entities\User;
use App\Domains\Pets\Entities\Pet;

class PetPolicy
{
    /**
     * Determine whether the user can view the pet profile and medical data.
     */
    public function view(User $user, Pet $pet): bool
    {
        // Add logic here to allow Sitters with an active booking to view.
        // For now, restrict to owner only.
        return $user->id === $pet->owner_id;
    }

    /**
     * Determine whether the user can create a pet.
     */
    public function create(User $user): bool
    {
        return true;
    }

    /**
     * Determine whether the user can update the pet.
     */
    public function update(User $user, Pet $pet): bool
    {
        return $user->id === $pet->owner_id;
    }

    /**
     * Determine whether the user can delete the pet.
     */
    public function delete(User $user, Pet $pet): bool
    {
        return $user->id === $pet->owner_id;
    }
}
