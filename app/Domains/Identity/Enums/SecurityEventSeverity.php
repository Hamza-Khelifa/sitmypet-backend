<?php

declare(strict_types=1);

namespace App\Domains\Identity\Enums;

enum SecurityEventSeverity: string
{
    case LOW = 'LOW';
    case MEDIUM = 'MEDIUM';
    case HIGH = 'HIGH';
    case CRITICAL = 'CRITICAL';
}
