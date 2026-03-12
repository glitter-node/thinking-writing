<?php

namespace App\Domain\ThoughtReview\Models;

use App\Domain\Thought\Models\Thought;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ThoughtReview extends Model
{
    public const SCORE_USEFUL = 'useful';
    public const SCORE_ARCHIVE = 'archive';
    public const SCORE_EVOLVE = 'evolve';

    protected $fillable = [
        'thought_id',
        'reviewed_at',
        'review_score',
    ];

    protected $casts = [
        'reviewed_at' => 'datetime',
    ];

    public function thought(): BelongsTo
    {
        return $this->belongsTo(Thought::class);
    }
}
