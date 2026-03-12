<?php

namespace App\Domain\ThoughtGraphIndex\Models;

use App\Domain\Thought\Models\Thought;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ThoughtGraphIndex extends Model
{
    public $timestamps = false;

    protected $table = 'thought_graph_index';

    protected $fillable = [
        'thought_id',
        'linked_thought_id',
        'link_type',
        'depth',
        'created_at',
    ];

    protected $casts = [
        'created_at' => 'datetime',
    ];

    public function thought(): BelongsTo
    {
        return $this->belongsTo(Thought::class);
    }

    public function linkedThought(): BelongsTo
    {
        return $this->belongsTo(Thought::class, 'linked_thought_id');
    }
}
