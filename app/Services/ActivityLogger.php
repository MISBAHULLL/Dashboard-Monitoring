<?php

namespace App\Services;

use App\Models\ActivityLog;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Request;

class ActivityLogger
{
    public static function log(
        string $action,
        string $module,
        ?int $targetId = null,
        ?string $targetTitle = null,
        ?string $description = null,
        ?array $oldValues = null,
        ?array $newValues = null,
    ): void {
        ActivityLog::create([
            'user_id' => Auth::id(),
            'action' => $action,
            'module' => $module,
            'target_id' => $targetId,
            'target_title' => $targetTitle,
            'description' => $description,
            'old_values' => $oldValues,
            'new_values' => $newValues,
            'ip_address' => Request::ip(),
        ]);
    }

    public static function created(string $module, int $targetId, string $targetTitle, ?string $description = null, ?array $values = null): void
    {
        self::log('created', $module, $targetId, $targetTitle, $description ?? "Membuat {$module} baru", null, $values);
    }

    public static function updated(string $module, int $targetId, string $targetTitle, array $oldValues, array $newValues, ?string $description = null): void
    {
        self::log('updated', $module, $targetId, $targetTitle, $description ?? "Mengupdate {$module}", $oldValues, $newValues);
    }

    public static function deleted(string $module, int $targetId, string $targetTitle, ?string $description = null): void
    {
        self::log('deleted', $module, $targetId, $targetTitle, $description ?? "Menghapus {$module}", null, null);
    }

    public static function statusChanged(string $module, int $targetId, string $targetTitle, string $oldStatus, string $newStatus): void
    {
        self::log(
            'status_changed',
            $module,
            $targetId,
            $targetTitle,
            "Mengubah status dari '{$oldStatus}' ke '{$newStatus}'",
            ['status' => $oldStatus],
            ['status' => $newStatus]
        );
    }
}
