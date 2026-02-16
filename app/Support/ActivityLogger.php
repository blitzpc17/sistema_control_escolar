<?php

namespace App\Support;

use App\Models\Module;
use App\Models\UserActivityLog;
use Illuminate\Support\Facades\Cache;

class ActivityLogger
{
    public static function moduleId(?string $moduleKey): ?int
    {
        if (!$moduleKey) return null;

        return Cache::remember("module_id:$moduleKey", 3600, function () use ($moduleKey) {
            return Module::where('key', $moduleKey)->value('id');
        });
    }

    public static function currentModuleKey(): ?string
    {
        // si el middleware resolvió módulo, aquí lo tienes
        return request()->attributes->get('module_key');
    }

    public static function log(
        int $userId,
        string $action,
        ?string $moduleKey = null,
        ?string $entityTable = null,
        ?int $entityId = null,
        $before = null,
        $after = null
    ): void {
        UserActivityLog::create([
            'user_id'      => $userId,
            'module_id'    => self::moduleId($moduleKey),
            'action'       => $action,
            'entity_table' => $entityTable,
            'entity_id'    => $entityId,
            'before_json'  => $before,
            'after_json'   => $after,
            'ip_address'   => request()->ip(),
            'user_agent'   => substr((string) request()->userAgent(), 0, 2000),
            'created_at'   => now(),
        ]);
    }
}
