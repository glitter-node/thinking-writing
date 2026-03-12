<?php

namespace App\Domain\Project\Models;

use App\Domain\Task\Models\Task;
use App\Domain\Thought\Models\Thought;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Project extends Model
{
    public $timestamps = false;

    protected $fillable = [
        'thought_id',
        'title',
        'description',
        'status',
        'created_at',
    ];

    protected $casts = [
        'created_at' => 'datetime',
    ];

    public function thought(): BelongsTo
    {
        return $this->belongsTo(Thought::class);
    }

    public function tasks(): HasMany
    {
        return $this->hasMany(Task::class)->orderBy('id');
    }
}
