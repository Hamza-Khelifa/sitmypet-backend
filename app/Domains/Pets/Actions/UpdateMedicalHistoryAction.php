<?php

declare(strict_types=1);

namespace App\Domains\Pets\Actions;

use App\Domains\Pets\DTOs\AddMedicalRecordDTO;
use App\Domains\Pets\Entities\MedicalRecord;
use App\Domains\Pets\Repositories\Contracts\MedicalDataRepositoryInterface;

final class UpdateMedicalHistoryAction
{
    public function __construct(
        private readonly MedicalDataRepositoryInterface $medicalDataRepository
    ) {}

    public function execute(AddMedicalRecordDTO $dto): MedicalRecord
    {
        $record = new MedicalRecord([
            'pet_id' => $dto->petId,
            'condition' => $dto->condition,
            'medication' => $dto->medication,
            'notes' => $dto->notes,
        ]);

        return $this->medicalDataRepository->saveMedicalRecord($record);
    }
}
