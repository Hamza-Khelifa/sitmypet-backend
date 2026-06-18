<?php

declare(strict_types=1);

namespace App\Domains\Profile\DTOs;

use App\Domains\Profile\ValueObjects\LocationPoint;

final class UpdateLocationDTO
{
    public function __construct(
        public readonly string $sitterProfileId,
        public readonly LocationPoint $location
    ) {}
}
