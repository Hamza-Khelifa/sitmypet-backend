<?php

declare(strict_types=1);

namespace App\Domains\Marketplace\Actions;

use App\Domains\Marketplace\Enums\BookingStatus;
use App\Domains\Marketplace\Enums\DemandStatus;
use App\Domains\Marketplace\Repositories\Contracts\BookingRepositoryInterface;
use App\Domains\Marketplace\Repositories\Contracts\DemandRepositoryInterface;
use Illuminate\Support\Facades\DB;

final class CancelBookingAction
{
    public function __construct(
        private readonly BookingRepositoryInterface $bookingRepository,
        private readonly DemandRepositoryInterface $demandRepository
    ) {}

    public function execute(string $bookingId): bool
    {
        return DB::transaction(function () use ($bookingId) {
            $booking = $this->bookingRepository->findById($bookingId);
            
            if (!$booking || $booking->status !== BookingStatus::CONFIRMED) {
                return false;
            }

            $booking->transitionTo(BookingStatus::CANCELLED);
            $this->bookingRepository->save($booking);

            $demand = $this->demandRepository->findById($booking->demand_id);
            $demand->transitionTo(DemandStatus::CANCELLED);
            $this->demandRepository->save($demand);

            return true;
        });
    }
}
