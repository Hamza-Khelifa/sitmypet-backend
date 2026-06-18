<?php

declare(strict_types=1);

namespace App\Domains\Profile\Repositories\Contracts;

use App\Domains\Profile\Entities\SitterProfile;
use App\Domains\Profile\ValueObjects\LocationPoint;
use Illuminate\Support\Collection;

interface SitterProfileRepositoryInterface
{
    public function findById(string $id): ?SitterProfile;
    
    public function findByUserId(string $userId): ?SitterProfile;
    
    public function save(SitterProfile $profile): SitterProfile;
    
    public function updateLocation(string $id, LocationPoint $point): bool;
    
    /**
     * Finds verified sitters within a specific radius in meters.
     *
     * @return Collection<int, SitterProfile>
     */
    public function findNearby(LocationPoint $center, int $radiusInMeters): Collection;
}
