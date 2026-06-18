<?php

declare(strict_types=1);

namespace App\Domains\Profile\Enums;

enum CertificationStatus: string
{
    case PENDING = 'pending';
    case APPROVED = 'approved';
    case REJECTED = 'rejected';
}
