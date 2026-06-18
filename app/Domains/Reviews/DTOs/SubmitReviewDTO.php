<?php

declare(strict_types=1);

namespace App\Domains\Reviews\DTOs;

final class SubmitReviewDTO
{
    public function __construct(
        public readonly string $bookingId,
        public readonly string $reviewerId,
        public readonly int $rating,
        public readonly ?string $content
    ) {}
}
