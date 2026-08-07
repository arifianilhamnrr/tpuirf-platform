<?php

namespace App\Application\Services;

use App\Infrastructure\Persistence\Models\AuditLog;
use Illuminate\Support\Facades\Request;

class AuditService
{
    public function log(string $action, string $entityType, string $entityId, ?array $oldValues = null, ?array $newValues = null): void
    {
        AuditLog::create([
            'user_id' => session('user_id'),
            'action' => $action,
            'entity_type' => $entityType,
            'entity_id' => $entityId,
            'old_values' => $oldValues,
            'new_values' => $newValues,
            'ip_address' => Request::ip(),
            'user_agent' => Request::userAgent(),
            'timestamp' => now(),
        ]);
    }
}
