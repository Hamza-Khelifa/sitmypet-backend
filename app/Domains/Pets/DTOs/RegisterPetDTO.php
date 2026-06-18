<?php

declare(strict_types=1);

namespace App\Domains\Pets\DTOs;

use App\Domains\Pets\Enums\SpeciesType;
use Carbon\Carbon;

final class RegisterPetDTO
{
    public function __construct(
        public readonly string $ownerId,
        public readonly string $name,
        public readonly SpeciesType $species,
        public readonly ?string $breed,
        public readonly ?Carbon $birthDate,
        public readonly ?float $weight,
        public readonly ?string $behaviorNotes,
        public readonly ?string $emergencyContactName,
        public readonly ?string $emergencyContactPhone
    ) {}
}
