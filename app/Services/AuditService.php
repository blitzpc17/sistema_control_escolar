<?php

namespace App\Services;

use Illuminate\Support\Facades\DB;

class AuditService
{
    public function log(array $data): void
    {
        DB::table('user_activity_logs')->insert([
            'user_id'      => $data['user_id'],
            'module_id'    => $data['module_id'] ?? null,
            'action'       => $data['action'],
            'entity_table' => $data['entity_table'] ?? null,
            'entity_id'    => $data['entity_id'] ?? null,
            'before_json'  => isset($data['before']) ? json_encode($data['before']) : null,
            'after_json'   => isset($data['after']) ? json_encode($data['after']) : null,
            'ip_address'   => request()->ip(),
            'user_agent'   => substr((string) request()->userAgent(), 0, 500),
            'created_at'   => now(),
        ]);
    }
}
