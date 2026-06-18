<?php

declare(strict_types=1);

namespace App\Domains\Identity\Repositories\Contracts;

use App\Domains\Identity\Entities\User;

interface UserRepositoryInterface
{
    public function create(array $data): User;
    
    public function findById(string $id): ?User;
    
    public function findByEmail(string $email): ?User;
    
    public function update(User $user, array $data): bool;
}
