<?php

declare(strict_types=1);

namespace App\Domains\Payments\Enums;

enum TransactionType: string
{
    case PAY_IN = 'pay_in';
    case TRANSFER = 'transfer';
    case PAY_OUT = 'pay_out';
    case REFUND = 'refund';
}
