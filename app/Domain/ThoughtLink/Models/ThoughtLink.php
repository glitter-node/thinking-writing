<?php

namespace App\Domain\ThoughtLink\Models;

use App\Domain\Thought\Models\Thought;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ThoughtLink extends Model
{
    public $timestamps = false;

    protected $fillable = [
        'source_thought_id',
        'target_thought_id',
        'created_at',
    ];

    protected $casts = [
        'created_at' => 'datetime',
    ];

    public function sourceThought(): BelongsTo
    {
        return $this->belongsTo(Thought::class, 'source_thought_id');
    }

    public function targetThought(): BelongsTo
    {
        return $this->belongsTo(Thought::class, 'target_thought_id');
    }
}
