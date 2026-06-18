<?php

declare(strict_types=1);

namespace App\Domains\Marketplace\DTOs;

use App\Domains\Profile\ValueObjects\LocationPoint;
use Carbon\Carbon;

final class CreateDemandDTO
{
    public function __construct(
        public readonly string $ownerId,
        public readonly array $petIds,
        public readonly Carbon $startDate,
        public readonly Carbon $endDate,
        public readonly LocationPoint $location,
        public readonly ?string $requirements
    ) {}
}
