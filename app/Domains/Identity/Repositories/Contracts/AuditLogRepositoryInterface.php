<?php

declare(strict_types=1);

namespace App\Domains\Identity\Repositories\Contracts;

use App\Domains\Identity\Entities\AuditLog;

interface AuditLogRepositoryInterface
{
    public function create(array $data): AuditLog;
}
