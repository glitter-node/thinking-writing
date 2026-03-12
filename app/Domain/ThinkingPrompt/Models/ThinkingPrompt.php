<?php

namespace App\Domain\ThinkingPrompt\Models;

use Illuminate\Database\Eloquent\Model;

class ThinkingPrompt extends Model
{
    public $timestamps = false;

    protected $fillable = [
        'prompt',
        'category',
        'created_at',
    ];

    protected $casts = [
        'created_at' => 'datetime',
    ];
}
