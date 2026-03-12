<?php

namespace App\Domain\ThoughtEmergence\Repositories;

use App\Domain\ThoughtEmergence\Models\ThoughtCooccurrence;
use Illuminate\Support\Collection as SupportCollection;

class ThoughtCooccurrenceRepository
{
    public function replaceForThoughtIds(array $thoughtIds, array $rows): void
    {
        ThoughtCooccurrence::query()
            ->whereIn('thought_a_id', $thoughtIds)
            ->orWhereIn('thought_b_id', $thoughtIds)
            ->delete();

        if ($rows !== []) {
            ThoughtCooccurrence::query()->insert($rows);
        }
    }

    public function topForThought(int $thoughtId, int $limit = 6): SupportCollection
    {
        return ThoughtCooccurrence::query()
            ->with(['thoughtA.stream:id,space_id,title', 'thoughtB.stream:id,space_id,title'])
            ->where('thought_a_id', $thoughtId)
            ->orWhere('thought_b_id', $thoughtId)
            ->orderByDesc('score')
            ->limit($limit)
            ->get();
    }

    public function topForUser(int $userId, int $limit = 8): SupportCollection
    {
        return ThoughtCooccurrence::query()
            ->select('thought_cooccurrence.*')
            ->join('thoughts as a', 'a.id', '=', 'thought_cooccurrence.thought_a_id')
            ->join('thoughts as b', 'b.id', '=', 'thought_cooccurrence.thought_b_id')
            ->where('a.user_id', $userId)
            ->where('b.user_id', $userId)
            ->with(['thoughtA.stream:id,space_id,title', 'thoughtB.stream:id,space_id,title'])
            ->orderByDesc('score')
            ->limit($limit)
            ->get();
    }
}
