<?php

declare(strict_types=1);

namespace App\Domains\Payments\Enums;

enum KycStatus: string
{
    case CREATED = 'created';
    case VALIDATION_ASKED = 'validation_asked';
    case VALIDATED = 'validated';
    case REFUSED = 'refused';
}
