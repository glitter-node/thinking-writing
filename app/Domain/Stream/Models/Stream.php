<?php

namespace App\Domain\Stream\Models;

use App\Domain\Space\Models\Space;
use App\Domain\Thought\Models\Thought;
use Database\Factories\StreamFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Stream extends Model
{
    use HasFactory;

    protected $fillable = [
        'space_id',
        'title',
        'position',
    ];

    protected static function newFactory()
    {
        return StreamFactory::new();
    }

    public function space(): BelongsTo
    {
        return $this->belongsTo(Space::class);
    }

    public function thoughts(): HasMany
    {
        return $this->hasMany(Thought::class)->orderBy('position');
    }
}
