<?php

declare(strict_types=1);

namespace App\Domains\Pets\Repositories\Contracts;

use App\Domains\Pets\Entities\MedicalRecord;
use App\Domains\Pets\Entities\Vaccination;
use Illuminate\Support\Collection;

interface MedicalDataRepositoryInterface
{
    /**
     * @return Collection<int, MedicalRecord>
     */
    public function findMedicalRecordsByPetId(string $petId): Collection;
    
    public function saveMedicalRecord(MedicalRecord $record): MedicalRecord;
    
    /**
     * @return Collection<int, Vaccination>
     */
    public function findVaccinationsByPetId(string $petId): Collection;
    
    public function saveVaccination(Vaccination $vaccination): Vaccination;
}
