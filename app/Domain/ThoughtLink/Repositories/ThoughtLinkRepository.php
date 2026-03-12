<?php

namespace App\Domain\ThoughtLink\Repositories;

use App\Domain\Thought\Models\Thought;
use App\Domain\ThoughtLink\Models\ThoughtLink;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Collection as SupportCollection;
use Illuminate\Support\Carbon;

class ThoughtLinkRepository
{
    public function deleteForSource(Thought $thought): void
    {
        $thought->outgoingLinks()->delete();
    }

    public function createMany(Thought $sourceThought, array $targetThoughtIds): void
    {
        if ($targetThoughtIds === []) {
            return;
        }

        $timestamp = Carbon::now();

        $sourceThought->outgoingLinks()->createMany(
            collect($targetThoughtIds)
                ->unique()
                ->values()
                ->map(fn (int $targetThoughtId): array => [
                    'target_thought_id' => $targetThoughtId,
                    'created_at' => $timestamp,
                ])
                ->all(),
        );
    }

    public function getConnectedLinks(Thought $thought): Collection
    {
        return ThoughtLink::query()
            ->with([
                'sourceThought.stream.space',
                'targetThought.stream.space',
            ])
            ->where('source_thought_id', $thought->id)
            ->orWhere('target_thought_id', $thought->id)
            ->get();
    }

    public function getAdjacencyPairs(): SupportCollection
    {
        return ThoughtLink::query()
            ->get(['source_thought_id', 'target_thought_id'])
            ->flatMap(fn (ThoughtLink $link) => [
                ['source' => $link->source_thought_id, 'target' => $link->target_thought_id],
                ['source' => $link->target_thought_id, 'target' => $link->source_thought_id],
            ])
            ->values();
    }

    public function pairExists(int $thoughtAId, int $thoughtBId): bool
    {
        return ThoughtLink::query()
            ->where(function ($query) use ($thoughtAId, $thoughtBId) {
                $query->where('source_thought_id', $thoughtAId)
                    ->where('target_thought_id', $thoughtBId);
            })
            ->orWhere(function ($query) use ($thoughtAId, $thoughtBId) {
                $query->where('source_thought_id', $thoughtBId)
                    ->where('target_thought_id', $thoughtAId);
            })
            ->exists();
    }
}
