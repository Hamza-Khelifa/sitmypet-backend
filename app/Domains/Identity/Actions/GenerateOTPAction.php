<?php

declare(strict_types=1);

namespace App\Domains\Identity\Actions;

use App\Domains\Identity\Entities\User;
use App\Domains\Identity\Repositories\Contracts\OTPRepositoryInterface;
use Illuminate\Support\Str;

final readonly class GenerateOTPAction
{
    public function __construct(
        private OTPRepositoryInterface $otpRepository
    ) {
    }

    public function execute(User $user): object
    {
        // 6 digit code
        $code = (string) random_int(100000, 999999);

        return $this->otpRepository->create([
            'id' => (string) Str::uuid(),
            'user_id' => $user->id,
            'code' => $code,
            'attempts' => 0,
            'expires_at' => now()->addMinutes(10), // 10 minutes expiry
        ]);
    }
}
