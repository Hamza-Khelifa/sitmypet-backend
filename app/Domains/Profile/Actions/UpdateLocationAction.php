<?php

declare(strict_types=1);

namespace App\Domains\Profile\Actions;

use App\Domains\Profile\DTOs\UpdateLocationDTO;
use App\Domains\Profile\Events\LocationUpdated;
use App\Domains\Profile\Repositories\Contracts\SitterProfileRepositoryInterface;
use Illuminate\Support\Facades\Event;

final class UpdateLocationAction
{
    public function __construct(
        private readonly SitterProfileRepositoryInterface $profileRepository
    ) {}

    public function execute(UpdateLocationDTO $dto): bool
    {
        $updated = $this->profileRepository->updateLocation($dto->sitterProfileId, $dto->location);

        if ($updated) {
            Event::dispatch(new LocationUpdated($dto->sitterProfileId, $dto->location));
        }

        return $updated;
    }
}
