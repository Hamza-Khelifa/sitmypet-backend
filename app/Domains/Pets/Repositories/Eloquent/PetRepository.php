<?php

declare(strict_types=1);

namespace App\Domains\Pets\Repositories\Eloquent;

use App\Domains\Pets\Entities\Pet;
use App\Domains\Pets\Repositories\Contracts\PetRepositoryInterface;
use Illuminate\Support\Collection;

final class PetRepository implements PetRepositoryInterface
{
    public function findById(string $id): ?Pet
    {
        return Pet::with(['medicalRecords', 'vaccinations', 'photos'])->find($id);
    }
    
    public function findByOwnerId(string $ownerId): Collection
    {
        return Pet::where('owner_id', $ownerId)->get();
    }
    
    public function save(Pet $pet): Pet
    {
        $pet->save();
        return $pet;
    }
    
    public function delete(string $id): bool
    {
        return (bool) Pet::destroy($id);
    }
}
