<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;

class Registration extends Model
{
    use HasUuids;

    protected $guarded = [];

    protected function casts(): array
    {
        return [
            'buyer_name' => 'encrypted', 'buyer_email' => 'encrypted',
            'buyer_phone' => 'encrypted', 'participant_name' => 'encrypted',
            'participant_birth_date' => 'encrypted', 'paid_at' => 'datetime',
        ];
    }
}
