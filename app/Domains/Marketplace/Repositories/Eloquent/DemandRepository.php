<?php

declare(strict_types=1);

namespace App\Domains\Marketplace\Repositories\Eloquent;

use App\Domains\Marketplace\Entities\Demand;
use App\Domains\Marketplace\Repositories\Contracts\DemandRepositoryInterface;
use App\Domains\Profile\ValueObjects\LocationPoint;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;

final class DemandRepository implements DemandRepositoryInterface
{
    public function findById(string $id): ?Demand
    {
        return Demand::with(['pets', 'bids'])->find($id);
    }
    
    public function findByOwnerId(string $ownerId): Collection
    {
        return Demand::where('owner_id', $ownerId)->orderBy('created_at', 'desc')->get();
    }
    
    public function save(Demand $demand): Demand
    {
        $demand->save();
        return $demand;
    }
    
    public function updateLocation(string $id, LocationPoint $point): bool
    {
        $sql = "UPDATE demands SET location = ST_SetSRID(ST_MakePoint(?, ?), 4326) WHERE id = ?";
        return (bool) DB::statement($sql, [$point->longitude, $point->latitude, $id]);
    }
}
