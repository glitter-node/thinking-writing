<?php

namespace App\Domain\ThoughtSynthesis\Repositories;

use App\Domain\Thought\Models\Thought;
use App\Domain\ThoughtSynthesis\Models\ThoughtSynthesis;
use App\Domain\ThoughtSynthesis\Models\ThoughtSynthesisItem;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Collection as SupportCollection;

class ThoughtSynthesisRepository
{
    public function create(int $userId, Thought $synthesizedThought): ThoughtSynthesis
    {
        return ThoughtSynthesis::query()->create([
            'user_id' => $userId,
            'synthesized_thought_id' => $synthesizedThought->id,
            'created_at' => now(),
        ]);
    }

    public function createItems(ThoughtSynthesis $synthesis, array $thoughtIds): void
    {
        $synthesis->items()->createMany(
            collect($thoughtIds)
                ->unique()
                ->values()
                ->map(fn (int $thoughtId): array => ['thought_id' => $thoughtId])
                ->all(),
        );
    }

    public function getSourceThoughtsForSynthesis(Thought $thought): Collection
    {
        return Thought::query()
            ->whereHas('synthesisItems', fn ($query) => $query->whereHas('synthesis', fn ($inner) => $inner->where('synthesized_thought_id', $thought->id)))
            ->with(['stream:id,space_id,title'])
            ->orderBy('id')
            ->get();
    }

    public function getSynthesisEdgesForSpace(int $spaceId): Collection
    {
        return ThoughtSynthesis::query()
            ->with(['items.thought.stream', 'synthesizedThought.stream'])
            ->whereHas('synthesizedThought.stream', fn ($query) => $query->where('space_id', $spaceId))
            ->get();
    }

    public function getAdjacencyPairs(): SupportCollection
    {
        return ThoughtSynthesisItem::query()
            ->with('synthesis')
            ->whereHas('thought')
            ->whereHas('synthesis.synthesizedThought')
            ->get()
            ->flatMap(fn (ThoughtSynthesisItem $item) => [
                ['source' => $item->thought_id, 'target' => $item->synthesis->synthesized_thought_id],
                ['source' => $item->synthesis->synthesized_thought_id, 'target' => $item->thought_id],
            ])
            ->values();
    }

    public function pairExistsInSynthesis(int $thoughtAId, int $thoughtBId): bool
    {
        $synthesisIdsForA = ThoughtSynthesisItem::query()
            ->where('thought_id', $thoughtAId)
            ->pluck('synthesis_id');

        if ($synthesisIdsForA->isEmpty()) {
            return false;
        }

        return ThoughtSynthesisItem::query()
            ->whereIn('synthesis_id', $synthesisIdsForA)
            ->where('thought_id', $thoughtBId)
            ->exists();
    }

    public function pairKeysForThoughtIds(array $thoughtIds): array
    {
        if ($thoughtIds === []) {
            return [];
        }

        return ThoughtSynthesisItem::query()
            ->whereIn('thought_id', $thoughtIds)
            ->orderBy('synthesis_id')
            ->orderBy('thought_id')
            ->get(['synthesis_id', 'thought_id'])
            ->groupBy('synthesis_id')
            ->reduce(function (array $pairs, Collection $items): array {
                $ids = $items->pluck('thought_id')->unique()->values()->all();
                $count = count($ids);

                for ($left = 0; $left < $count; $left++) {
                    for ($right = $left + 1; $right < $count; $right++) {
                        $pairs[$this->pairKey($ids[$left], $ids[$right])] = true;
                    }
                }

                return $pairs;
            }, []);
    }

    private function pairKey(int $thoughtAId, int $thoughtBId): string
    {
        return min($thoughtAId, $thoughtBId).':'.max($thoughtAId, $thoughtBId);
    }
}
