<?php

namespace App\Domain\Thought\Listeners\Concerns;

use App\Domain\Thought\Models\Thought;

trait ResolvesThought
{
    protected function resolveThought(int $thoughtId, bool $withTrashed = false): Thought
    {
        $query = Thought::query()->with(['stream.space', 'versions']);

        if ($withTrashed) {
            $query->withTrashed();
        }

        return $query->findOrFail($thoughtId);
    }
}
