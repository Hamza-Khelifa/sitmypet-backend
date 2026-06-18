<?php

declare(strict_types=1);

namespace App\Domains\Identity\Repositories\Contracts;

interface SessionRepositoryInterface
{
    public function create(array $data): void;
    
    public function revokeAllForUser(string $userId): void;
}
