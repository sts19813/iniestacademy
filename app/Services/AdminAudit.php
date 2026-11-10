<?php

namespace App\Services;

use Illuminate\Support\Facades\DB;

class AdminAudit
{
    public static function record(?int $adminId, string $event, array $metadata = []): void
    {
        DB::table('admin_audit_events')->insert([
            'admin_account_id' => $adminId,
            'event' => $event,
            'metadata' => $metadata ? json_encode($metadata, JSON_THROW_ON_ERROR) : null,
            'created_at' => now(),
        ]);
    }
}
