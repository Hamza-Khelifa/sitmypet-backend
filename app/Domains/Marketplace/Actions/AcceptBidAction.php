<?php

declare(strict_types=1);

namespace App\Domains\Marketplace\Actions;

use App\Domains\Marketplace\Entities\Booking;
use App\Domains\Marketplace\Enums\BidStatus;
use App\Domains\Marketplace\Enums\BookingStatus;
use App\Domains\Marketplace\Enums\DemandStatus;
use App\Domains\Marketplace\Events\BookingConfirmed;
use App\Domains\Marketplace\Repositories\Contracts\BidRepositoryInterface;
use App\Domains\Marketplace\Repositories\Contracts\BookingRepositoryInterface;
use App\Domains\Marketplace\Repositories\Contracts\DemandRepositoryInterface;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Event;
use InvalidArgumentException;

final class AcceptBidAction
{
    public function __construct(
        private readonly BidRepositoryInterface $bidRepository,
        private readonly DemandRepositoryInterface $demandRepository,
        private readonly BookingRepositoryInterface $bookingRepository
    ) {}

    public function execute(string $bidId): Booking
    {
        return DB::transaction(function () use ($bidId) {
            $bid = $this->bidRepository->findById($bidId);
            
            if (!$bid || $bid->status !== BidStatus::PENDING) {
                throw new InvalidArgumentException('Invalid or non-pending bid.');
            }

            $demand = $this->demandRepository->findById($bid->demand_id);

            // Accept this bid
            $bid->transitionTo(BidStatus::ACCEPTED);
            $this->bidRepository->save($bid);

            // Reject all other bids for this demand
            $this->bidRepository->rejectAllOtherBids($demand->id, $bid->id);

            // Update Demand status
            $demand->transitionTo(DemandStatus::ASSIGNED);
            $this->demandRepository->save($demand);

            // Create the Booking
            $booking = current([new Booking([
                'demand_id' => $demand->id,
                'sitter_id' => $bid->sitter_id,
                'total_price' => $bid->proposed_rate, // Assuming flat rate for scaffolding
            ])]);
            $booking->transitionTo(BookingStatus::CONFIRMED);
            $booking = $this->bookingRepository->save($booking);

            Event::dispatch(new BookingConfirmed($booking->id));

            // Send Push Notifications
            $usersToNotify = \App\Domains\Identity\Entities\User::whereIn('id', [
                $demand->owner_id, 
                $booking->sitter_id
            ])->get();
            \Illuminate\Support\Facades\Notification::send($usersToNotify, new \App\Domains\Marketplace\Notifications\BookingConfirmedNotification($booking));

            return $booking;
        });
    }
}
