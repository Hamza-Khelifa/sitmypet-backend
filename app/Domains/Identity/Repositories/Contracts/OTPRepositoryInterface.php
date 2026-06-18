<?php

declare(strict_types=1);

namespace App\Domains\Identity\Repositories\Contracts;

interface OTPRepositoryInterface
{
    public function create(array $data): object;

    public function findLatestValidCode(string $userId): ?object;

    public function incrementAttempts(object $otp): void;

    public function markAsVerified(object $otp): void;
}
