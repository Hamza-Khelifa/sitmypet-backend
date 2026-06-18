<?php

declare(strict_types=1);

namespace App\Console\Commands;

use App\Domains\Identity\Entities\User;
use App\Domains\Marketplace\DTOs\SearchDemandsDTO;
use App\Domains\Marketplace\Entities\Demand;
use App\Domains\Marketplace\Enums\DemandStatus;
use App\Domains\Marketplace\ReadModels\Contracts\DemandFeedReadModelInterface;
use App\Domains\Marketplace\ReadModels\Eloquent\DemandFeedReadModel;
use App\Domains\Profile\ValueObjects\LocationPoint;
use Carbon\Carbon;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class MarketplaceLoadTestCommand extends Command
{
    protected $signature = 'marketplace:load-test {--count=10000 : Number of demands to seed}';
    protected $description = 'Seed demands and compare read latency between PostGIS and Redis Read Models';

    public function handle(DemandFeedReadModelInterface $redisReadModel, DemandFeedReadModel $postGisReadModel): int
    {
        $count = (int) $this->option('count');

        $this->info("Seeding {$count} demands...");

        $user = User::first() ?? User::factory()->create();

        // Batch insert for speed
        $demands = [];
        $now = now();
        for ($i = 0; $i < $count; $i++) {
            $demands[] = [
                'id' => (string) Str::uuid(),
                'owner_id' => $user->id,
                'start_date' => $now,
                'end_date' => $now->copy()->addDays(2),
                'status' => DemandStatus::OPEN->value,
                'requirements' => 'Needs to be walked',
                'created_at' => $now,
                'updated_at' => $now,
                // generate a point near Paris
                'location' => DB::raw("ST_SetSRID(ST_MakePoint(" . (2.3522 + (mt_rand(-100, 100) / 1000)) . ", " . (48.8566 + (mt_rand(-100, 100) / 1000)) . "), 4326)"),
            ];
        }

        foreach (array_chunk($demands, 1000) as $chunk) {
            DB::table('demands')->insert($chunk);
        }

        // To project them all into Redis, since we bypassed the observer with batch insert:
        $this->info('Projecting into Redis (simulating CQRS feed build)...');
        $allDemands = Demand::all();
        foreach ($allDemands as $d) {
            // Touch to trigger observer
            $d->touch();
        }

        $this->info('Starting Read Performance Test (100 parallel-like queries)');

        $dto = new SearchDemandsDTO(
            center: new LocationPoint(48.8566, 2.3522),
            radiusInMeters: 5000,
            limit: 50,
            offset: 0
        );

        // Benchmark PostGIS
        $postGisStart = microtime(true);
        for ($i = 0; $i < 100; $i++) {
            $postGisReadModel->searchNearbyOpenDemands($dto);
        }
        $postGisEnd = microtime(true);
        $postGisTime = ($postGisEnd - $postGisStart) * 1000;

        // Benchmark Redis
        $redisStart = microtime(true);
        for ($i = 0; $i < 100; $i++) {
            $redisReadModel->searchNearbyOpenDemands($dto);
        }
        $redisEnd = microtime(true);
        $redisTime = ($redisEnd - $redisStart) * 1000;

        $this->table(
            ['Read Model Engine', 'Time for 100 queries (ms)', 'Latency per query (ms)'],
            [
                ['PostGIS (ST_DWithin + GIST)', round($postGisTime, 2), round($postGisTime / 100, 2)],
                ['Redis 8 Geospatial (CQRS Feed)', round($redisTime, 2), round($redisTime / 100, 2)],
            ]
        );

        $this->info('Load test complete.');

        return Command::SUCCESS;
    }
}
