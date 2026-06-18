<?php

declare(strict_types=1);

namespace App\Domains\Marketplace\Projectors;

use App\Domains\Marketplace\Entities\Demand;
use App\Domains\Marketplace\Enums\DemandStatus;
use Illuminate\Support\Facades\Redis;

final class DemandStateProjector
{
    private const INDEX_KEY = 'marketplace:demands:geo';
    private const DATA_KEY_PREFIX = 'marketplace:demands:data:';

    /**
     * Handle the Demand "saved" event.
     */
    public function saved(Demand $demand): void
    {
        // Only project OPEN demands into the feed
        if ($demand->status === DemandStatus::OPEN || $demand->status === DemandStatus::OPEN->value) {
            
            // Extract coordinates using ST_X and ST_Y from PostGIS if location is set
            // Since the location column is a PostGIS geography, we need to query it raw, or we can require 
            // the latitude/longitude to be cached on the model.
            // Wait, DemandController uses raw SQL on insert? Let's check Demand::location.
            $rawLocation = \Illuminate\Support\Facades\DB::selectOne(
                "SELECT ST_X(location::geometry) as lng, ST_Y(location::geometry) as lat FROM demands WHERE id = ?",
                [$demand->id]
            );

            if ($rawLocation && $rawLocation->lng !== null && $rawLocation->lat !== null) {
                // Add to Geo index
                Redis::geoadd(self::INDEX_KEY, $rawLocation->lng, $rawLocation->lat, $demand->id);
                
                // Load relations if needed
                $demand->loadMissing('pets');
                
                // Store payload
                Redis::set(self::DATA_KEY_PREFIX . $demand->id, json_encode($demand->toArray()));
            }
        } else {
            // Remove from feed if it's no longer open
            $this->removeFromRedis($demand);
        }
    }

    /**
     * Handle the Demand "deleted" event.
     */
    public function deleted(Demand $demand): void
    {
        $this->removeFromRedis($demand);
    }

    private function removeFromRedis(Demand $demand): void
    {
        Redis::zrem(self::INDEX_KEY, $demand->id);
        Redis::del(self::DATA_KEY_PREFIX . $demand->id);
    }
}
