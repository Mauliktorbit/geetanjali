<?php

namespace App\Services;

use App\Models\AuditLog;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Request;

class AuditLogService
{
    public function log(string $action, ?Model $model = null, ?array $old = null, ?array $new = null): void
    {
        try {
            AuditLog::create([
                'user_id' => Auth::id(),
                'action' => $action,
                'auditable_type' => $model ? get_class($model) : null,
                'auditable_id' => $model?->getKey(),
                'old_values' => $old,
                'new_values' => $new,
                'ip_address' => Request::ip(),
                'user_agent' => substr((string) Request::userAgent(), 0, 255),
                'url' => Request::fullUrl(),
            ]);
        } catch (\Throwable $e) {
            report($e);
        }
    }
}
