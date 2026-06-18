<?php

declare(strict_types=1);

namespace App\Domains\Payments\ValueObjects;

use InvalidArgumentException;

final class Money
{
    public function __construct(
        public readonly int $amountInCents,
        public readonly string $currency = 'EUR'
    ) {
        if ($amountInCents < 0) {
            throw new InvalidArgumentException("Amount cannot be negative");
        }
        if (strlen($currency) !== 3) {
            throw new InvalidArgumentException("Currency must be 3 letters (ISO 4217)");
        }
    }

    public static function fromDecimal(float $amount, string $currency = 'EUR'): self
    {
        return new self((int) round($amount * 100), $currency);
    }

    public function getDecimalAmount(): float
    {
        return $this->amountInCents / 100;
    }
}
