<?php

declare(strict_types=1);

namespace App\Domains\Marketplace\Enums;

enum BidStatus: string
{
    case PENDING = 'pending';
    case ACCEPTED = 'accepted';
    case REJECTED = 'rejected';
}
