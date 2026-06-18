<?php

declare(strict_types=1);

namespace App\Domains\Pets\Repositories\Eloquent;

use App\Domains\Pets\Entities\MedicalRecord;
use App\Domains\Pets\Entities\Vaccination;
use App\Domains\Pets\Repositories\Contracts\MedicalDataRepositoryInterface;
use Illuminate\Support\Collection;

final class MedicalDataRepository implements MedicalDataRepositoryInterface
{
    public function findMedicalRecordsByPetId(string $petId): Collection
    {
        return MedicalRecord::where('pet_id', $petId)->orderBy('created_at', 'desc')->get();
    }
    
    public function saveMedicalRecord(MedicalRecord $record): MedicalRecord
    {
        $record->save();
        return $record;
    }
    
    public function findVaccinationsByPetId(string $petId): Collection
    {
        return Vaccination::where('pet_id', $petId)->orderBy('date_administered', 'desc')->get();
    }
    
    public function saveVaccination(Vaccination $vaccination): Vaccination
    {
        $vaccination->save();
        return $vaccination;
    }
}
