<?php

declare(strict_types=1);

namespace App\Domains\Marketplace\ReadModels\Eloquent;

use App\Domains\Marketplace\DTOs\SearchDemandsDTO;
use App\Domains\Marketplace\Entities\Demand;
use App\Domains\Marketplace\Enums\DemandStatus;
use App\Domains\Marketplace\ReadModels\Contracts\DemandFeedReadModelInterface;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;

final class DemandFeedReadModel implements DemandFeedReadModelInterface
{
    public function searchNearbyOpenDemands(SearchDemandsDTO $dto): LengthAwarePaginator
    {
        $centerPoint = "ST_SetSRID(ST_MakePoint({$dto->center->longitude}, {$dto->center->latitude}), 4326)";
        
        return Demand::with(['pets'])
            ->where('status', DemandStatus::OPEN->value)
            ->whereRaw("ST_DWithin(location, {$centerPoint}, ?)", [$dto->radiusInMeters])
            // Select standard columns and compute distance in meters
            ->selectRaw("*, ST_Distance(location, {$centerPoint}) as distance_meters")
            ->orderBy('distance_meters', 'asc')
            ->paginate($dto->limit, ['*'], 'page', ($dto->offset / $dto->limit) + 1);
    }
}
