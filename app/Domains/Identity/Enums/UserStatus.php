<?php

declare(strict_types=1);

namespace App\Domains\Identity\Enums;

enum UserStatus: string
{
    case PENDING_EMAIL_VERIFICATION = 'pending_email_verification';
    case PENDING_PROFILE_COMPLETION = 'pending_profile_completion';
    case ACTIVE = 'active';
    case SUSPENDED = 'suspended';
    case BANNED = 'banned';
    case PENDING_DELETION = 'pending_deletion';
    case DELETED = 'deleted';

    public function canLogin(): bool
    {
        return match($this) {
            self::ACTIVE, self::PENDING_PROFILE_COMPLETION, self::PENDING_EMAIL_VERIFICATION => true,
            default => false,
        };
    }
}
