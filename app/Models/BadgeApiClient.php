<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class BadgeApiClient extends Model
{
    protected $fillable = [
        'name',
        'token_hash',
        'is_active',
        'expires_at',
        'last_used_at',
    ];

    protected function casts(): array
    {
        return [
            'is_active' => 'boolean',
            'expires_at' => 'datetime',
            'last_used_at' => 'datetime',
        ];
    }
}
