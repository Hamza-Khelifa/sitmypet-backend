<?php

declare(strict_types=1);

namespace App\Domains\Pets\Repositories\Contracts;

use App\Domains\Pets\Entities\Pet;
use Illuminate\Support\Collection;

interface PetRepositoryInterface
{
    public function findById(string $id): ?Pet;
    
    /**
     * @return Collection<int, Pet>
     */
    public function findByOwnerId(string $ownerId): Collection;
    
    public function save(Pet $pet): Pet;
    
    public function delete(string $id): bool;
}
