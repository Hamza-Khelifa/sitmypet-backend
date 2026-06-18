<?php

declare(strict_types=1);

namespace App\Domains\Profile\Actions;

use App\Domains\Profile\DTOs\SitterProfileCreationDTO;
use App\Domains\Profile\Entities\SitterProfile;
use App\Domains\Profile\Repositories\Contracts\SitterProfileRepositoryInterface;
use InvalidArgumentException;

final class CreateSitterProfileAction
{
    public function __construct(
        private readonly SitterProfileRepositoryInterface $profileRepository
    ) {}

    public function execute(SitterProfileCreationDTO $dto): SitterProfile
    {
        if ($this->profileRepository->findByUserId($dto->userId) !== null) {
            throw new InvalidArgumentException('User already has a sitter profile.');
        }

        $profile = new SitterProfile([
            'user_id' => $dto->userId,
            'bio' => $dto->bio,
            'hourly_rate' => $dto->hourlyRate,
            'is_verified' => false,
        ]);

        return $this->profileRepository->save($profile);
    }
}
