<?php

declare(strict_types=1);

namespace App\Domains\Marketplace\Enums;

enum BookingStatus: string
{
    case CONFIRMED = 'confirmed';
    case IN_PROGRESS = 'in_progress';
    case COMPLETED = 'completed';
    case CANCELLED = 'cancelled';
}
