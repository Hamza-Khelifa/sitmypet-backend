<?php

declare(strict_types=1);

namespace App\Domains\Identity\DTOs;

final readonly class UserRegistrationDTO
{
    public function __construct(
        public string $email,
        public string $password,
        public string $role, // 'pet-owner' or 'pet-sitter'
        public ?string $ipAddress = null,
        public ?string $userAgent = null,
    ) {
    }
}
