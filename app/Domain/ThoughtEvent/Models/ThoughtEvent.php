<?php

namespace App\Domain\ThoughtEvent\Models;

use App\Domain\Thought\Models\Thought;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ThoughtEvent extends Model
{
    protected $fillable = [
        'thought_id',
        'event_type',
        'metadata',
    ];

    protected $casts = [
        'metadata' => 'array',
    ];

    public function thought(): BelongsTo
    {
        return $this->belongsTo(Thought::class);
    }
}
