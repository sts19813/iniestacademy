<?php

namespace App\Services;

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class SiteSettings
{
    public static function apply(): void
    {
        if (! Schema::hasTable('site_settings')) {
            return;
        }
        $values = DB::table('site_settings')->pluck('value', 'key');
        foreach (['regular_amount', 'promotion_amount', 'capacity', 'contact', 'enabled'] as $key) {
            if (! $values->has($key)) {
                continue;
            }
            $value = $values[$key];
            $value = match ($key) {
                'regular_amount', 'capacity' => (int) $value,
                'promotion_amount' => $value === null || $value === '' ? null : (int) $value,
                'enabled' => $value === '1',
                default => $value,
            };
            config(['registration.'.$key => $value]);
        }
    }
}
