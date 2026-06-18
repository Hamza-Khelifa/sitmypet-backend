<?php

declare(strict_types=1);

namespace App\Domains\Identity\Repositories\Contracts;

use App\Domains\Identity\Entities\RefreshToken;

interface RefreshTokenRepositoryInterface
{
    public function create(array $data): RefreshToken;
    
    public function findByHash(string $hash): ?RefreshToken;
    
    public function revoke(RefreshToken $token): bool;
    
    public function revokeFamily(string $familyId): bool;
}
