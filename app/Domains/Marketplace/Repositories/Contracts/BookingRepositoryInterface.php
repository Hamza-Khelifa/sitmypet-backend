<?php

declare(strict_types=1);

namespace App\Domains\Marketplace\Repositories\Contracts;

use App\Domains\Marketplace\Entities\Booking;
use Illuminate\Support\Collection;

interface BookingRepositoryInterface
{
    public function findById(string $id): ?Booking;
    
    public function save(Booking $booking): Booking;
}
