<?php

declare(strict_types=1);

namespace App\Domains\Payments\Enums;

enum TransactionStatus: string
{
    case PENDING = 'pending';
    case SUCCEEDED = 'succeeded';
    case FAILED = 'failed';
}
