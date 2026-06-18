<?php

declare(strict_types=1);

namespace App\Domains\Identity\Repositories\Eloquent;

use App\Domains\Identity\Entities\RefreshToken;
use App\Domains\Identity\Repositories\Contracts\RefreshTokenRepositoryInterface;

class RefreshTokenRepository implements RefreshTokenRepositoryInterface
{
    public function create(array $data): RefreshToken
    {
        return RefreshToken::create($data);
    }

    public function findByHash(string $hash): ?RefreshToken
    {
        return RefreshToken::where('token_hash', $hash)->first();
    }

    public function revoke(RefreshToken $token): bool
    {
        return $token->update(['revoked_at' => now()]);
    }

    public function revokeFamily(string $familyId): bool
    {
        return (bool) RefreshToken::where('token_family_id', $familyId)
            ->whereNull('revoked_at')
            ->update(['revoked_at' => now()]);
    }
}
