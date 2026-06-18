<?php

declare(strict_types=1);

namespace App\Domains\Identity\Actions;

use App\Domains\Identity\DTOs\UserRegistrationDTO;
use App\Domains\Identity\Entities\User;
use App\Domains\Identity\Enums\UserStatus;
use App\Domains\Identity\Events\UserRegistered;
use App\Domains\Identity\Repositories\Contracts\UserRepositoryInterface;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

final readonly class RegisterUserAction
{
    public function __construct(
        private UserRepositoryInterface $userRepository
    ) {
    }

    /**
     * Executes the registration transaction.
     */
    public function execute(UserRegistrationDTO $dto): User
    {
        return DB::transaction(function () use ($dto) {
            $user = $this->userRepository->create([
                'id' => (string) Str::uuid(),
                'email' => $dto->email,
                'password' => Hash::make($dto->password),
                'status' => UserStatus::PENDING_EMAIL_VERIFICATION->value,
            ]);

            // Assign base role dynamically
            $user->assignRole($dto->requestedRole);

            // Dispatch domain event (Listeners will trigger email)
            UserRegistered::dispatch($user);

            return $user;
        });
    }
}
