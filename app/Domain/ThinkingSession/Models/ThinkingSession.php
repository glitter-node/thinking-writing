<?php

namespace App\Domain\ThinkingSession\Models;

use App\Models\User;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ThinkingSession extends Model
{
    public $timestamps = false;

    protected $fillable = [
        'user_id',
        'started_at',
        'thought_count',
    ];

    protected $casts = [
        'started_at' => 'datetime',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
