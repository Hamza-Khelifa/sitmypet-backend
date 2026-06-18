<?php

declare(strict_types=1);

namespace App\Domains\Identity\Repositories\Eloquent;

use App\Domains\Identity\Entities\UserSession;
use App\Domains\Identity\Repositories\Contracts\SessionRepositoryInterface;

class SessionRepository implements SessionRepositoryInterface
{
    public function create(array $data): void
    {
        UserSession::create($data);
    }

    public function revokeAllForUser(string $userId): void
    {
        UserSession::where('user_id', $userId)->delete();
    }
}
