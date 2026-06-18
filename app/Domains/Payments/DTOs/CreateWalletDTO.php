<?php

declare(strict_types=1);

namespace App\Domains\Payments\DTOs;

final class CreateWalletDTO
{
    public function __construct(
        public readonly string $userId,
        public readonly string $firstName,
        public readonly string $lastName,
        public readonly string $email,
        public readonly string $nationality,
        public readonly string $countryOfResidence,
        public readonly string $currency = 'EUR'
    ) {}
}
