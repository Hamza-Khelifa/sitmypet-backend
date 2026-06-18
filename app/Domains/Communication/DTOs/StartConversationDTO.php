<?php

declare(strict_types=1);

namespace App\Domains\Communication\DTOs;

final class StartConversationDTO
{
    public function __construct(
        public readonly array $participantIds,
        public readonly ?string $title = null
    ) {}
}
