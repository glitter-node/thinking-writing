<?php

namespace App\Domain\Task\Models;

use App\Domain\Project\Models\Project;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Task extends Model
{
    public $timestamps = false;

    protected $fillable = [
        'project_id',
        'title',
        'status',
        'priority',
        'created_at',
    ];

    protected $casts = [
        'created_at' => 'datetime',
    ];

    public function project(): BelongsTo
    {
        return $this->belongsTo(Project::class);
    }
}
