<?php

declare(strict_types=1);

namespace App\Domains\Communication\Enums;

enum MessageType: string
{
    case TEXT = 'text';
    case IMAGE = 'image';
    case SYSTEM = 'system';
}
