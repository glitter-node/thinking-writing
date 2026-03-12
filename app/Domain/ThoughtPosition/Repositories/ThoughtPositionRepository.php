<?php

namespace App\Domain\ThoughtPosition\Repositories;

use App\Domain\Space\Models\Space;
use App\Domain\Thought\Models\Thought;
use App\Domain\ThoughtPosition\Models\ThoughtPosition;
use Illuminate\Database\Eloquent\Collection;

class ThoughtPositionRepository
{
    public function upsertForThought(Thought $thought, Space $space, int $x, int $y): ThoughtPosition
    {
        return ThoughtPosition::query()->updateOrCreate(
            [
                'thought_id' => $thought->id,
                'space_id' => $space->id,
            ],
            [
                'x' => $x,
                'y' => $y,
            ],
        );
    }

    public function getForSpace(Space $space): Collection
    {
        return ThoughtPosition::query()
            ->where('space_id', $space->id)
            ->orderBy('id')
            ->get();
    }

    public function getForThoughtInSpace(Thought $thought, Space $space): ?ThoughtPosition
    {
        return ThoughtPosition::query()
            ->where('thought_id', $thought->id)
            ->where('space_id', $space->id)
            ->first();
    }
}
