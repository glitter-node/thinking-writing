<?php

namespace App\Domain\ThoughtEmergence\Repositories;

use App\Domain\ThoughtEmergence\Models\ThoughtTagIndex;
use App\Domain\Thought\Models\Thought;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Collection as SupportCollection;

class ThoughtTagIndexRepository
{
    public function replaceForThought(Thought $thought, array $tags): void
    {
        ThoughtTagIndex::query()->where('thought_id', $thought->id)->delete();

        if ($tags === []) {
            return;
        }

        ThoughtTagIndex::query()->insert(
            collect($tags)->unique()->values()->map(fn (string $tag): array => [
                'thought_id' => $thought->id,
                'tag' => $tag,
                'created_at' => now(),
            ])->all(),
        );
    }

    public function rebuildFromThoughts(Collection $thoughts, int $userId): void
    {
        ThoughtTagIndex::query()
            ->whereIn('thought_id', function ($query) use ($userId) {
                $query->select('id')
                    ->from('thoughts')
                    ->where('user_id', $userId);
            })
            ->delete();

        $rows = $thoughts->flatMap(function (Thought $thought) {
            return collect($thought->tags ?? [])
                ->filter()
                ->unique()
                ->map(fn (string $tag): array => [
                    'thought_id' => $thought->id,
                    'tag' => $tag,
                    'created_at' => now(),
                ]);
        })->values()->all();

        if ($rows !== []) {
            ThoughtTagIndex::query()->insert($rows);
        }
    }

    public function getTrendingTagsForUser(int $userId, int $limit = 8): SupportCollection
    {
        return ThoughtTagIndex::query()
            ->selectRaw('thought_tag_index.tag, COUNT(*) as usage_count')
            ->join('thoughts', 'thoughts.id', '=', 'thought_tag_index.thought_id')
            ->where('thoughts.user_id', $userId)
            ->groupBy('thought_tag_index.tag')
            ->orderByDesc('usage_count')
            ->orderBy('thought_tag_index.tag')
            ->limit($limit)
            ->get();
    }

    public function getSharedTagsForThoughtIds(array $thoughtIds): SupportCollection
    {
        return ThoughtTagIndex::query()
            ->whereIn('thought_id', $thoughtIds)
            ->get(['thought_id', 'tag']);
    }
}
