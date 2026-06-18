<?php

declare(strict_types=1);

namespace App\Domains\Marketplace\ReadModels\Redis;

use App\Domains\Marketplace\DTOs\SearchDemandsDTO;
use App\Domains\Marketplace\Entities\Demand;
use App\Domains\Marketplace\ReadModels\Contracts\DemandFeedReadModelInterface;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Pagination\LengthAwarePaginator as Paginator;
use Illuminate\Support\Facades\Redis;

final class RedisDemandFeedReadModel implements DemandFeedReadModelInterface
{
    private const INDEX_KEY = 'marketplace:demands:geo';
    private const DATA_KEY_PREFIX = 'marketplace:demands:data:';

    public function searchNearbyOpenDemands(SearchDemandsDTO $dto): LengthAwarePaginator
    {
        // GEORADIUS key longitude latitude radius m|km|ft|mi [WITHCOORD] [WITHDIST] [WITHHASH] [COUNT count [ANY]] [ASC|DESC]
        $results = Redis::georadius(
            self::INDEX_KEY,
            $dto->center->longitude,
            $dto->center->latitude,
            $dto->radiusInMeters,
            'm',
            ['WITHDIST', 'ASC']
        );

        if (empty($results)) {
            return new Paginator([], 0, $dto->limit, 1);
        }

        // Apply pagination
        $total = count($results);
        $paginatedResults = array_slice($results, $dto->offset, $dto->limit);

        $demandData = [];
        foreach ($paginatedResults as $result) {
            $demandId = $result[0];
            $distance = $result[1];

            $json = Redis::get(self::DATA_KEY_PREFIX . $demandId);
            if ($json) {
                $data = json_decode($json, true);
                $data['distance_meters'] = $distance;
                $demandData[] = $data;
            } else {
                // Fallback to database if missing in Redis but exists in GEO
                $demand = Demand::with('pets')->find($demandId);
                if ($demand) {
                    $data = $demand->toArray();
                    $data['distance_meters'] = $distance;
                    $demandData[] = $data;
                }
            }
        }

        $currentPage = (int) floor($dto->offset / $dto->limit) + 1;
        
        return new Paginator($demandData, $total, $dto->limit, $currentPage);
    }
}
