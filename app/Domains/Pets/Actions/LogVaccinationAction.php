<?php

declare(strict_types=1);

namespace App\Domains\Pets\Actions;

use App\Domains\Pets\DTOs\LogVaccinationDTO;
use App\Domains\Pets\Entities\Vaccination;
use App\Domains\Pets\Events\VaccinationLogged;
use App\Domains\Pets\Repositories\Contracts\MedicalDataRepositoryInterface;
use Illuminate\Support\Facades\Event;

final class LogVaccinationAction
{
    public function __construct(
        private readonly MedicalDataRepositoryInterface $medicalDataRepository
    ) {}

    public function execute(LogVaccinationDTO $dto): Vaccination
    {
        $vaccination = new Vaccination([
            'pet_id' => $dto->petId,
            'name' => $dto->name,
            'date_administered' => $dto->dateAdministered,
            'next_due_date' => $dto->nextDueDate,
            'document_url' => $dto->documentUrl,
        ]);

        $vaccination = $this->medicalDataRepository->saveVaccination($vaccination);

        Event::dispatch(new VaccinationLogged($vaccination->id));

        return $vaccination;
    }
}
