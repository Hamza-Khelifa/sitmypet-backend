<?php

declare(strict_types=1);

namespace App\Domains\Marketplace\DTOs;

final class SubmitBidDTO
{
    public function __construct(
        public readonly string $demandId,
        public readonly string $sitterId,
        public readonly float $proposedRate,
        public readonly ?string $coverLetter
    ) {}
}
