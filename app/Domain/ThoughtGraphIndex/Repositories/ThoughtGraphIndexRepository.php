<?php

namespace App\Domain\ThoughtGraphIndex\Repositories;

use App\Domain\ThoughtGraphIndex\Models\ThoughtGraphIndex;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Collection as SupportCollection;

class ThoughtGraphIndexRepository
{
    public function deleteForThoughtIds(array $thoughtIds): void
    {
        if ($thoughtIds === []) {
            return;
        }

        ThoughtGraphIndex::query()
            ->whereIn('thought_id', $thoughtIds)
            ->delete();
    }

    public function insertRows(array $rows): void
    {
        if ($rows === []) {
            return;
        }

        ThoughtGraphIndex::query()->insert($rows);
    }

    public function neighborsForThought(int $thoughtId, int $maxDepth): Collection
    {
        return ThoughtGraphIndex::query()
            ->with(['linkedThought.stream:id,space_id,title'])
            ->where('thought_id', $thoughtId)
            ->where('depth', '<=', $maxDepth)
            ->orderBy('depth')
            ->orderBy('linked_thought_id')
            ->get();
    }

    public function directNeighborsForThought(int $thoughtId): Collection
    {
        return ThoughtGraphIndex::query()
            ->with(['linkedThought.stream:id,space_id,title'])
            ->where('thought_id', $thoughtId)
            ->where('depth', 1)
            ->orderBy('linked_thought_id')
            ->get();
    }

    public function relatedThoughtIdsForThought(int $thoughtId): SupportCollection
    {
        return ThoughtGraphIndex::query()
            ->where('thought_id', $thoughtId)
            ->orWhere('linked_thought_id', $thoughtId)
            ->get(['thought_id', 'linked_thought_id'])
            ->flatMap(fn (ThoughtGraphIndex $row): array => [
                (int) $row->thought_id,
                (int) $row->linked_thought_id,
            ])
            ->unique()
            ->values();
    }

    public function truncate(): void
    {
        ThoughtGraphIndex::query()->delete();
    }
}
