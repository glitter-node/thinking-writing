<?php

namespace App\Domain\ThoughtEmergence\Models;

use App\Domain\Thought\Models\Thought;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ThoughtCooccurrence extends Model
{
    public $timestamps = false;

    protected $table = 'thought_cooccurrence';

    protected $fillable = [
        'thought_a_id',
        'thought_b_id',
        'score',
        'created_at',
    ];

    protected $casts = [
        'score' => 'integer',
        'created_at' => 'datetime',
    ];

    public function thoughtA(): BelongsTo
    {
        return $this->belongsTo(Thought::class, 'thought_a_id');
    }

    public function thoughtB(): BelongsTo
    {
        return $this->belongsTo(Thought::class, 'thought_b_id');
    }
}
