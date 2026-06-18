<?php

declare(strict_types=1);

namespace App\Domains\Communication\DTOs;

use App\Domains\Communication\Enums\MessageType;

final class SendMessageDTO
{
    public function __construct(
        public readonly string $conversationId,
        public readonly string $senderId,
        public readonly ?string $content,
        public readonly MessageType $type = MessageType::TEXT,
        public readonly array $attachments = [] // Array of uploaded files or paths
    ) {}
}
