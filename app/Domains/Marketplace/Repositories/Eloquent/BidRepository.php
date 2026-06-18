<?php

declare(strict_types=1);

namespace App\Domains\Marketplace\Repositories\Eloquent;

use App\Domains\Marketplace\Entities\Bid;
use App\Domains\Marketplace\Enums\BidStatus;
use App\Domains\Marketplace\Repositories\Contracts\BidRepositoryInterface;
use Illuminate\Support\Collection;

final class BidRepository implements BidRepositoryInterface
{
    public function findById(string $id): ?Bid
    {
        return Bid::with(['sitter'])->find($id);
    }
    
    public function findByDemandId(string $demandId): Collection
    {
        return Bid::where('demand_id', $demandId)->orderBy('created_at', 'desc')->get();
    }
    
    public function findBySitterId(string $sitterId): Collection
    {
        return Bid::where('sitter_id', $sitterId)->orderBy('created_at', 'desc')->get();
    }
    
    public function save(Bid $bid): Bid
    {
        $bid->save();
        return $bid;
    }
    
    public function rejectAllOtherBids(string $demandId, string $acceptedBidId): int
    {
        return Bid::where('demand_id', $demandId)
            ->where('id', '!=', $acceptedBidId)
            ->update(['status' => BidStatus::REJECTED->value]);
    }
}
