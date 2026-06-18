<?php

declare(strict_types=1);

namespace App\Domains\Marketplace\Repositories\Eloquent;

use App\Domains\Marketplace\Entities\Booking;
use App\Domains\Marketplace\Repositories\Contracts\BookingRepositoryInterface;

final class BookingRepository implements BookingRepositoryInterface
{
    public function findById(string $id): ?Booking
    {
        return Booking::with(['demand', 'sitter'])->find($id);
    }
    
    public function save(Booking $booking): Booking
    {
        $booking->save();
        return $booking;
    }
}
