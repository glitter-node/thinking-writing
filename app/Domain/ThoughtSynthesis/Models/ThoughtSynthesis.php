<?php

namespace App\Domain\ThoughtSynthesis\Models;

use App\Domain\Thought\Models\Thought;
use App\Models\User;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class ThoughtSynthesis extends Model
{
    public $timestamps = false;

    protected $fillable = [
        'user_id',
        'synthesized_thought_id',
        'created_at',
    ];

    protected $casts = [
        'created_at' => 'datetime',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function synthesizedThought(): BelongsTo
    {
        return $this->belongsTo(Thought::class, 'synthesized_thought_id');
    }

    public function items(): HasMany
    {
        return $this->hasMany(ThoughtSynthesisItem::class, 'synthesis_id');
    }
}
