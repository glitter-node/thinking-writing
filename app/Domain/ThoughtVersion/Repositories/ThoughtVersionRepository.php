<?php

namespace App\Domain\ThoughtVersion\Repositories;

use App\Domain\Thought\Models\Thought;
use App\Domain\ThoughtVersion\Models\ThoughtVersion;
use Illuminate\Database\Eloquent\Collection;

class ThoughtVersionRepository
{
    public function create(Thought $thought, int $version, string $content): ThoughtVersion
    {
        return $thought->versions()->create([
            'version' => $version,
            'content' => $content,
        ]);
    }

    public function latestVersionNumber(Thought $thought): int
    {
        return (int) $thought->versions()->max('version');
    }

    public function existsForVersion(Thought $thought, int $version): bool
    {
        return $thought->versions()->where('version', $version)->exists();
    }

    public function historyForThought(Thought $thought): Collection
    {
        return $thought->versions()->latest('version')->get();
    }
}
