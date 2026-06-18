<?php

declare(strict_types=1);

namespace App\Domains\Profile\Policies;

use App\Domains\Identity\Entities\User;
use App\Domains\Profile\Entities\SitterProfile;

class SitterProfilePolicy
{
    /**
     * Determine whether the user can view the sitter profile.
     */
    public function view(?User $user, SitterProfile $sitterProfile): bool
    {
        return true; // Publicly viewable
    }

    /**
     * Determine whether the user can create a sitter profile.
     */
    public function create(User $user): bool
    {
        return true; // Any authenticated user can attempt to create
    }

    /**
     * Determine whether the user can update the sitter profile.
     */
    public function update(User $user, SitterProfile $sitterProfile): bool
    {
        return $user->id === $sitterProfile->user_id;
    }

    /**
     * Determine whether the user can delete the sitter profile.
     */
    public function delete(User $user, SitterProfile $sitterProfile): bool
    {
        return $user->id === $sitterProfile->user_id;
    }
}
