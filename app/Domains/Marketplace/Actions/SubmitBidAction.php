<?php

declare(strict_types=1);

namespace App\Domains\Marketplace\Actions;

use App\Domains\Marketplace\DTOs\SubmitBidDTO;
use App\Domains\Marketplace\Entities\Bid;
use App\Domains\Marketplace\Enums\BidStatus;
use App\Domains\Marketplace\Events\BidSubmitted;
use App\Domains\Marketplace\Repositories\Contracts\BidRepositoryInterface;
use Illuminate\Support\Facades\Event;

final class SubmitBidAction
{
    public function __construct(
        private readonly BidRepositoryInterface $bidRepository
    ) {}

    public function execute(SubmitBidDTO $dto): Bid
    {
        $bid = current([new Bid([
            'demand_id' => $dto->demandId,
            'sitter_id' => $dto->sitterId,
            'proposed_rate' => $dto->proposedRate,
            'cover_letter' => $dto->coverLetter,
        ])]);
        $bid->transitionTo(BidStatus::PENDING);

        $bid = $this->bidRepository->save($bid);

        Event::dispatch(new BidSubmitted($bid->id));

        // Notify the demand owner
        $demandOwner = \App\Domains\Identity\Entities\User::find($bid->demand->owner_id);
        if ($demandOwner) {
            $demandOwner->notify(new \App\Domains\Marketplace\Notifications\NewBidNotification($bid));
        }

        return $bid;
    }
}
