<?php

declare(strict_types=1);

namespace App\Domains\AiGateway\DTOs;

final class AskAssistantDTO
{
    public function __construct(
        public readonly string $userId,
        public readonly string $prompt,
        public readonly array $context = []
    ) {}
}
