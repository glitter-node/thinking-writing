<?php

namespace App\Domain\ThoughtVersion\Models;

use App\Domain\Thought\Models\Thought;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ThoughtVersion extends Model
{
    protected $fillable = [
        'thought_id',
        'version',
        'content',
    ];

    public function thought(): BelongsTo
    {
        return $this->belongsTo(Thought::class);
    }
}
