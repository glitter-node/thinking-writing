<?php

namespace App\Domain\Thought\Models;

use App\Domain\Project\Models\Project;
use App\Domain\Stream\Models\Stream;
use App\Domain\Task\Models\Task;
use App\Domain\ThoughtLink\Models\ThoughtLink;
use App\Domain\ThoughtReview\Models\ThoughtReview;
use App\Domain\ThoughtSynthesis\Models\ThoughtSynthesis;
use App\Domain\ThoughtSynthesis\Models\ThoughtSynthesisItem;
use App\Domain\ThoughtEvent\Models\ThoughtEvent;
use App\Domain\ThoughtPosition\Models\ThoughtPosition;
use App\Domain\ThoughtVersion\Models\ThoughtVersion;
use App\Models\User;
use Database\Factories\ThoughtFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\Relations\HasManyThrough;
use Illuminate\Database\Eloquent\SoftDeletes;

class Thought extends Model
{
    use HasFactory;
    use SoftDeletes;

    protected $casts = [
        'tags' => 'array',
        'deleted_at' => 'datetime',
    ];

    protected $fillable = [
        'stream_id',
        'user_id',
        'parent_id',
        'content',
        'stage',
        'priority',
        'tags',
        'position',
    ];

    protected static function newFactory()
    {
        return ThoughtFactory::new();
    }

    public function stream(): BelongsTo
    {
        return $this->belongsTo(Stream::class);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function parent(): BelongsTo
    {
        return $this->belongsTo(self::class, 'parent_id');
    }

    public function children(): HasMany
    {
        return $this->hasMany(self::class, 'parent_id')->orderBy('created_at');
    }

    public function outgoingLinks(): HasMany
    {
        return $this->hasMany(ThoughtLink::class, 'source_thought_id');
    }

    public function incomingLinks(): HasMany
    {
        return $this->hasMany(ThoughtLink::class, 'target_thought_id');
    }

    public function synthesizedFrom(): HasOne
    {
        return $this->hasOne(ThoughtSynthesis::class, 'synthesized_thought_id');
    }

    public function project(): HasOne
    {
        return $this->hasOne(Project::class);
    }

    public function tasks(): HasManyThrough
    {
        return $this->hasManyThrough(Task::class, Project::class);
    }

    public function synthesisItems(): HasMany
    {
        return $this->hasMany(ThoughtSynthesisItem::class);
    }

    public function reviews(): HasMany
    {
        return $this->hasMany(ThoughtReview::class)->orderByDesc('reviewed_at');
    }

    public function position(): HasOne
    {
        return $this->hasOne(ThoughtPosition::class);
    }

    public function versions(): HasMany
    {
        return $this->hasMany(ThoughtVersion::class)->orderByDesc('version');
    }

    public function events(): HasMany
    {
        return $this->hasMany(ThoughtEvent::class)->orderByDesc('id');
    }
}
