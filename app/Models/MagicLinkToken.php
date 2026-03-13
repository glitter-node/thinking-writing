<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class MagicLinkToken extends Model
{
    protected $table = 'magic_login_tokens';

    protected $fillable = [
        'email',
        'token_hash',
        'expires_at',
        'used_at',
    ];

    protected function casts(): array
    {
        return [
            'expires_at' => 'datetime',
            'used_at' => 'datetime',
        ];
    }
}
