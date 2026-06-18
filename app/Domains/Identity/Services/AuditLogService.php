<?php

declare(strict_types=1);

namespace App\Domains\Identity\Services;

use App\Domains\Identity\Entities\AuditLog;
use App\Domains\Identity\Repositories\Contracts\AuditLogRepositoryInterface;
use Illuminate\Support\Str;

final readonly class AuditLogService
{
    public function __construct(
        private AuditLogRepositoryInterface $auditLogRepository
    ) {
    }

    public function log(
        string $action,
        ?string $userId,
        ?string $entityType = null,
        ?string $entityId = null,
        ?array $oldValues = null,
        ?array $newValues = null,
        ?string $ipAddress = null,
        ?string $userAgent = null
    ): AuditLog {
        return $this->auditLogRepository->create([
            'id' => (string) Str::uuid(),
            'user_id' => $userId,
            'action' => $action,
            'entity_type' => $entityType,
            'entity_id' => $entityId,
            'old_values' => $oldValues,
            'new_values' => $newValues,
            'ip_address' => $ipAddress ?? request()?->ip(),
            'user_agent' => $userAgent ?? request()?->userAgent(),
        ]);
    }
}
