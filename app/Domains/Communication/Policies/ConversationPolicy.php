<?php

declare(strict_types=1);

namespace App\Domains\Communication\Policies;

use App\Domains\Identity\Entities\User;
use App\Domains\Communication\Entities\Conversation;

class ConversationPolicy
{
    public function view(User $user, Conversation $conversation): bool
    {
        return $conversation->participants()->where('user_id', $user->id)->exists();
    }

    public function create(User $user): bool
    {
        return true;
    }

    public function message(User $user, Conversation $conversation): bool
    {
        return $this->view($user, $conversation);
    }
}
