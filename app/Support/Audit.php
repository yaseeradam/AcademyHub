<?php

namespace App\Support;

use App\Models\AuditLog;
use Illuminate\Database\Eloquent\Model;

final class Audit
{
    /**
     * Record an audit event.
     *
     * @param  array<string,mixed>  $meta
     */
    public static function log(string $action, ?Model $model = null, array $meta = []): void
    {
        try {
            $request = request();

            AuditLog::query()->create([
                'user_id' => auth()->id(),
                'action' => $action,
                'auditable_type' => $model?->getMorphClass(),
                'auditable_id' => $model?->getKey(),
                'meta' => $meta !== [] ? $meta : null,
                'ip_address' => $request?->ip(),
                'user_agent' => $request?->userAgent(),
            ]);
        } catch (\Throwable) {
            // Never block core flows because of audit logging.
        }
    }
}

