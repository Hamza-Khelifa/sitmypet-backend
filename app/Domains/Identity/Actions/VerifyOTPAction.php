<?php

declare(strict_types=1);

namespace App\Domains\Identity\Actions;

use App\Domains\Identity\DTOs\VerifyOTPDTO;
use App\Domains\Identity\Events\OTPVerified;
use App\Domains\Identity\Repositories\Contracts\OTPRepositoryInterface;
use App\Domains\Identity\Repositories\Contracts\UserRepositoryInterface;
use Illuminate\Support\Facades\DB;

final readonly class VerifyOTPAction
{
    public function __construct(
        private OTPRepositoryInterface $otpRepository,
        private UserRepositoryInterface $userRepository
    ) {
    }

    public function execute(VerifyOTPDTO $dto): bool
    {
        return DB::transaction(function () use ($dto) {
            $otp = $this->otpRepository->findLatestValidCode($dto->userId);

            if (!$otp || $otp->attempts >= 3) {
                return false; // Code invalid or locked
            }

            if ($otp->code !== $dto->code) {
                $this->otpRepository->incrementAttempts($otp);
                return false;
            }

            // Valid code
            $this->otpRepository->markAsVerified($otp);

            $user = $this->userRepository->findById($dto->userId);
            if ($user) {
                $this->userRepository->update($user, [
                    'email_verified_at' => now(),
                    'status' => 'pending_profile_completion', // Transition state
                ]);
                
                OTPVerified::dispatch($user);
            }

            return true;
        });
    }
}
