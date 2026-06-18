<?php

declare(strict_types=1);

namespace App\Domains\Marketplace\Repositories\Contracts;

use App\Domains\Marketplace\Entities\Demand;
use App\Domains\Profile\ValueObjects\LocationPoint;
use Illuminate\Support\Collection;

interface DemandRepositoryInterface
{
    public function findById(string $id): ?Demand;
    
    /**
     * @return Collection<int, Demand>
     */
    public function findByOwnerId(string $ownerId): Collection;
    
    public function save(Demand $demand): Demand;
    
    public function updateLocation(string $id, LocationPoint $point): bool;
}
