<?php

declare(strict_types=1);

namespace App\Domains\AiGateway\DTOs;

final class ModerateContentDTO
{
    public function __construct(
        public readonly string $userId,
        public readonly string $content
    ) {}
}
