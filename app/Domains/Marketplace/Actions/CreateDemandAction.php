<?php

declare(strict_types=1);

namespace App\Domains\Marketplace\Actions;

use App\Domains\Marketplace\DTOs\CreateDemandDTO;
use App\Domains\Marketplace\Entities\Demand;
use App\Domains\Marketplace\Enums\DemandStatus;
use App\Domains\Marketplace\Events\DemandPublished;
use App\Domains\Marketplace\Repositories\Contracts\DemandRepositoryInterface;
use Illuminate\Support\Facades\Event;

final class CreateDemandAction
{
    public function __construct(
        private readonly DemandRepositoryInterface $demandRepository
    ) {}

    public function execute(CreateDemandDTO $dto): Demand
    {
        $demand = current([new Demand([
            'owner_id' => $dto->ownerId,
            'start_date' => $dto->startDate,
            'end_date' => $dto->endDate,
            'requirements' => $dto->requirements,
        ])]);
        
        $demand->transitionTo(DemandStatus::OPEN);

        $demand = $this->demandRepository->save($demand);

        // Attach pets
        if (!empty($dto->petIds)) {
            $demand->pets()->sync($dto->petIds);
        }

        // Update PostGIS location
        $this->demandRepository->updateLocation($demand->id, $dto->location);

        // Touch to fire the 'saved' event again so the Observer projects the new location
        $demand->touch();

        Event::dispatch(new DemandPublished($demand->id));

        return $demand;
    }
}
