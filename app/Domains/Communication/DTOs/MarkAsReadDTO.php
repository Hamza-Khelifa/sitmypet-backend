<?php

declare(strict_types=1);

namespace App\Domains\Communication\DTOs;

final class MarkAsReadDTO
{
    public function __construct(
        public readonly string $conversationId,
        public readonly string $userId,
        public readonly string $messageId
    ) {}
}
