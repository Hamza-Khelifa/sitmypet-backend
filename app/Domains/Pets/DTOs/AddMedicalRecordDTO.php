<?php

declare(strict_types=1);

namespace App\Domains\Pets\DTOs;

final class AddMedicalRecordDTO
{
    public function __construct(
        public readonly string $petId,
        public readonly string $condition,
        public readonly ?string $medication,
        public readonly ?string $notes
    ) {}
}
