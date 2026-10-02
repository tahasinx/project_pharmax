<?php

namespace App\Domain\Audit;

use App\Models\AuditLog;
use Illuminate\Database\Eloquent\Model;

class AuditRecorder
{
    public static function record(string $module, Model $record, string $action, ?array $old, ?array $new): void
    {
        AuditLog::create([
            'user_id'     => auth()->id(),
            'module'      => $module,
            'record_type' => $record->getMorphClass(),
            'record_id'   => $record->getKey(),
            'action'      => $action,
            'old_values'  => $old,
            'new_values'  => $new,
            'ip'          => request()?->ip(),
        ]);
    }
}
