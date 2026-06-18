<?php

declare(strict_types=1);

namespace App\Domains\Pets\Actions;

use App\Domains\Pets\DTOs\RegisterPetDTO;
use App\Domains\Pets\Entities\Pet;
use App\Domains\Pets\Events\PetRegistered;
use App\Domains\Pets\Repositories\Contracts\PetRepositoryInterface;
use Illuminate\Support\Facades\Event;

final class RegisterPetAction
{
    public function __construct(
        private readonly PetRepositoryInterface $petRepository
    ) {}

    public function execute(RegisterPetDTO $dto): Pet
    {
        $pet = new Pet([
            'owner_id' => $dto->ownerId,
            'name' => $dto->name,
            'species' => $dto->species,
            'breed' => $dto->breed,
            'birth_date' => $dto->birthDate,
            'weight' => $dto->weight,
            'behavior_notes' => $dto->behaviorNotes,
            'emergency_contact_name' => $dto->emergencyContactName,
            'emergency_contact_phone' => $dto->emergencyContactPhone,
        ]);

        $pet = $this->petRepository->save($pet);

        Event::dispatch(new PetRegistered($pet->id));

        return $pet;
    }
}
