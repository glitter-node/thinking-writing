<?php

namespace App\Domain\ThoughtPosition\Models;

use App\Domain\Space\Models\Space;
use App\Domain\Thought\Models\Thought;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ThoughtPosition extends Model
{
    protected $fillable = [
        'thought_id',
        'space_id',
        'x',
        'y',
    ];

    public function thought(): BelongsTo
    {
        return $this->belongsTo(Thought::class);
    }

    public function space(): BelongsTo
    {
        return $this->belongsTo(Space::class);
    }
}
