<?php

declare(strict_types=1);

namespace App\Domains\Identity\Repositories\Eloquent;

use App\Domains\Identity\Entities\OTPCode;
use App\Domains\Identity\Repositories\Contracts\OTPRepositoryInterface;

class OTPRepository implements OTPRepositoryInterface
{
    public function create(array $data): object
    {
        return OTPCode::create($data);
    }

    public function findLatestValidCode(string $userId): ?object
    {
        return OTPCode::where('user_id', $userId)
            ->whereNull('verified_at')
            ->where('expires_at', '>', now())
            ->latest()
            ->first();
    }

    public function incrementAttempts(object $otp): void
    {
        $otp->increment('attempts');
    }

    public function markAsVerified(object $otp): void
    {
        $otp->update(['verified_at' => now()]);
    }
}
