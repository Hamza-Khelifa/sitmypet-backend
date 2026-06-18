<?php

declare(strict_types=1);

namespace App\Domains\Identity\Services;

use App\Domains\Identity\Entities\RefreshToken;
use App\Domains\Identity\Entities\User;
use App\Domains\Identity\Enums\SecurityEventSeverity;
use App\Domains\Identity\Events\ThreatDetected;
use App\Domains\Identity\Repositories\Contracts\RefreshTokenRepositoryInterface;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

final readonly class TokenFamilyService
{
    public function __construct(
        private RefreshTokenRepositoryInterface $refreshTokenRepository
    ) {
    }

    public function rotateToken(string $plainToken, string $ipAddress): ?RefreshToken
    {
        $tokenHash = hash('sha256', $plainToken);

        $existingToken = $this->refreshTokenRepository->findByHash($tokenHash);

        if (!$existingToken) {
            return null; // Invalid token
        }

        return DB::transaction(function () use ($existingToken, $ipAddress) {
            if ($existingToken->isRevoked()) {
                $this->handleTokenReuse($existingToken, $ipAddress);
                return null;
            }

            if ($existingToken->isExpired()) {
                return null;
            }

            $this->refreshTokenRepository->revoke($existingToken);

            $newPlainToken = Str::random(64);
            $newToken = $this->refreshTokenRepository->create([
                'id' => (string) Str::uuid(),
                'user_id' => $existingToken->user_id,
                'token_hash' => hash('sha256', $newPlainToken),
                'token_family_id' => $existingToken->token_family_id,
                'parent_token_id' => $existingToken->id,
                'expires_at' => now()->addDays(14),
            ]);

            $newToken->plainTextToken = $newPlainToken;

            return $newToken;
        });
    }

    public function createInitialToken(User $user): RefreshToken
    {
        $plainToken = Str::random(64);
        
        $token = $this->refreshTokenRepository->create([
            'id' => (string) Str::uuid(),
            'user_id' => $user->id,
            'token_hash' => hash('sha256', $plainToken),
            'token_family_id' => (string) Str::uuid(),
            'parent_token_id' => null,
            'expires_at' => now()->addDays(14),
        ]);

        $token->plainTextToken = $plainToken;

        return $token;
    }

    public function revokeFamily(string $tokenFamilyId): void
    {
        $this->refreshTokenRepository->revokeFamily($tokenFamilyId);
    }

    private function handleTokenReuse(RefreshToken $token, string $ipAddress): void
    {
        $this->revokeFamily($token->token_family_id);

        ThreatDetected::dispatch(
            $token->user,
            'REFRESH_TOKEN_REUSE',
            SecurityEventSeverity::CRITICAL,
            [
                'token_family_id' => $token->token_family_id,
                'attempted_token_id' => $token->id,
            ],
            $ipAddress
        );

        $token->user->tokens()->delete();
    }
}
