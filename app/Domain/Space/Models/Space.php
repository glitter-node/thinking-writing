<?php

namespace App\Domain\Space\Models;

use App\Domain\Stream\Models\Stream;
use App\Domain\Thought\Models\Thought;
use App\Domain\ThoughtPosition\Models\ThoughtPosition;
use App\Models\User;
use Database\Factories\SpaceFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasManyThrough;

class Space extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'title',
        'description',
    ];

    protected static function newFactory()
    {
        return SpaceFactory::new();
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function streams(): HasMany
    {
        return $this->hasMany(Stream::class)->orderBy('position');
    }

    public function thoughts(): HasManyThrough
    {
        return $this->hasManyThrough(Thought::class, Stream::class);
    }

    public function thoughtPositions(): HasMany
    {
        return $this->hasMany(ThoughtPosition::class);
    }
}
