<?php

namespace App\Domain\ThoughtEmergence\Models;

use App\Domain\Thought\Models\Thought;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ThoughtTagIndex extends Model
{
    public $timestamps = false;

    protected $table = 'thought_tag_index';

    protected $fillable = [
        'thought_id',
        'tag',
        'created_at',
    ];

    protected $casts = [
        'created_at' => 'datetime',
    ];

    public function thought(): BelongsTo
    {
        return $this->belongsTo(Thought::class);
    }
}
