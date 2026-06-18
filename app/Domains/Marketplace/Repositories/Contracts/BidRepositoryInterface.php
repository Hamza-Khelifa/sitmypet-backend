<?php

declare(strict_types=1);

namespace App\Domains\Marketplace\Repositories\Contracts;

use App\Domains\Marketplace\Entities\Bid;
use Illuminate\Support\Collection;

interface BidRepositoryInterface
{
    public function findById(string $id): ?Bid;
    
    public function findByDemandId(string $demandId): Collection;
    
    public function findBySitterId(string $sitterId): Collection;
    
    public function save(Bid $bid): Bid;
    
    public function rejectAllOtherBids(string $demandId, string $acceptedBidId): int;
}
