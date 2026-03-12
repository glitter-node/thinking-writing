<?php

namespace App\Domain\ThoughtSynthesis\Models;

use App\Domain\Thought\Models\Thought;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ThoughtSynthesisItem extends Model
{
    public $timestamps = false;

    protected $fillable = [
        'synthesis_id',
        'thought_id',
    ];

    public function synthesis(): BelongsTo
    {
        return $this->belongsTo(ThoughtSynthesis::class, 'synthesis_id');
    }

    public function thought(): BelongsTo
    {
        return $this->belongsTo(Thought::class);
    }
}
