<?php

declare(strict_types=1);

namespace App\Domains\Identity\Actions;

use App\Domains\Identity\Entities\User;
use App\Domains\Identity\Repositories\Contracts\RefreshTokenRepositoryInterface;
use App\Domains\Identity\Repositories\Contracts\SessionRepositoryInterface;
use Illuminate\Support\Facades\DB;

final readonly class RevokeSessionsAction
{
    public function __construct(
        private SessionRepositoryInterface $sessionRepository,
        private RefreshTokenRepositoryInterface $refreshTokenRepository
    ) {
    }

    /**
     * Immediately revokes all sessions, refresh tokens, and access tokens for a user.
     */
    public function execute(User $user): void
    {
        DB::transaction(function () use ($user) {
            // 1. Delete Personal Access Tokens (Sanctum)
            $user->tokens()->delete();

            // 2. Revoke all Refresh Tokens
            $user->refreshTokens()->update(['revoked_at' => now()]);

            // 3. Delete UI active sessions
            $this->sessionRepository->revokeAllForUser($user->id);
        });
    }
}
