<?php

declare(strict_types=1);

namespace App\Domains\Profile\DTOs;

final class SitterProfileCreationDTO
{
    public function __construct(
        public readonly string $userId,
        public readonly string $bio,
        public readonly float $hourlyRate
    ) {}
}
