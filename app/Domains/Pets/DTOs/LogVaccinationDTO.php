<?php

declare(strict_types=1);

namespace App\Domains\Pets\DTOs;

use Carbon\Carbon;

final class LogVaccinationDTO
{
    public function __construct(
        public readonly string $petId,
        public readonly string $name,
        public readonly Carbon $dateAdministered,
        public readonly ?Carbon $nextDueDate,
        public readonly ?string $documentUrl
    ) {}
}
