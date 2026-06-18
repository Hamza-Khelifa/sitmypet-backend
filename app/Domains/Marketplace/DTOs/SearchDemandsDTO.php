<?php

declare(strict_types=1);

namespace App\Domains\Marketplace\DTOs;

use App\Domains\Profile\ValueObjects\LocationPoint;

final class SearchDemandsDTO
{
    public function __construct(
        public readonly LocationPoint $center,
        public readonly int $radiusInMeters,
        public readonly int $limit = 50,
        public readonly int $offset = 0
    ) {}
}
