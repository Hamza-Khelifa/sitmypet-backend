<?php

declare(strict_types=1);

namespace App\Domains\Marketplace\Enums;

enum DemandStatus: string
{
    case OPEN = 'open';
    case ASSIGNED = 'assigned';
    case COMPLETED = 'completed';
    case CANCELLED = 'cancelled';
}
