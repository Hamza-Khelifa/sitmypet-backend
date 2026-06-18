<?php

declare(strict_types=1);

namespace App\Domains\Payments\Policies;

use App\Domains\Identity\Entities\User;
use App\Domains\Payments\Entities\Wallet;

class WalletPolicy
{
    public function view(User $user, Wallet $wallet): bool
    {
        return $user->id === $wallet->user_id;
    }

    public function create(User $user): bool
    {
        return true;
    }

    public function manage(User $user, Wallet $wallet): bool
    {
        return $user->id === $wallet->user_id;
    }
}
