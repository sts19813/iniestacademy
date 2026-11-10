<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class AdminAccount extends Model
{
    protected $guarded = [];

    protected $hidden = ['password_hash', 'totp_secret'];

    protected function casts(): array
    {
        return ['totp_secret' => 'encrypted', 'active' => 'boolean'];
    }
}
