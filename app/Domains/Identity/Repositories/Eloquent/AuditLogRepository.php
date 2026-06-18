<?php

declare(strict_types=1);

namespace App\Domains\Identity\Repositories\Eloquent;

use App\Domains\Identity\Entities\AuditLog;
use App\Domains\Identity\Repositories\Contracts\AuditLogRepositoryInterface;

class AuditLogRepository implements AuditLogRepositoryInterface
{
    public function create(array $data): AuditLog
    {
        return AuditLog::create($data);
    }
}
