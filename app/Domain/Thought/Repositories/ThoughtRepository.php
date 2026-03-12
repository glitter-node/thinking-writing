<?php

namespace App\Domain\Thought\Repositories;

use App\Domain\Space\Models\Space;
use App\Domain\Stream\Models\Stream;
use App\Domain\Thought\Models\Thought;
use App\Domain\ThoughtLink\Models\ThoughtLink;
use App\Domain\ThoughtSynthesis\Models\ThoughtSynthesisItem;
use Carbon\CarbonImmutable;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Relations\Relation;
use Illuminate\Support\Collection as SupportCollection;
use Illuminate\Support\Facades\DB;

class ThoughtRepository
{
    public function createForStream(Stream $stream, array $data): Thought
    {
        return $stream->thoughts()->create($data);
    }

    public function update(Thought $thought, array $data): Thought
    {
        $thought->update($data);

        return $thought->fresh();
    }

    public function delete(Thought $thought): void
    {
        $thought->delete();
    }

    public function decrementPositionsAfter(Stream $stream, int $position): void
    {
        $stream->thoughts()
            ->where('position', '>', $position)
            ->decrement('position');
    }

    public function nextPositionForStream(Stream $stream): int
    {
        return (int) $stream->thoughts()->max('position') + 1;
    }

    public function applySearch(Builder|Relation $query, ?string $search): Builder|Relation
    {
        $term = trim((string) $search);

        if ($term === '') {
            return $query;
        }

        return $this->applyContentSearch($query, $term);
    }

    public function refreshWithRelations(Thought $thought, array $relations = []): Thought
    {
        return $thought->fresh($relations);
    }

    public function findByExactContentWithinSpace(Space $space, string $content): ?Thought
    {
        return Thought::query()
            ->with(['stream.space'])
            ->where('content', trim($content))
            ->whereHas('stream', fn (Builder $query) => $query->where('space_id', $space->id))
            ->orderBy('id')
            ->first();
    }

    public function searchWithinSpace(Space $space, string $search): Collection
    {
        $query = Thought::query()
            ->select(['id', 'stream_id', 'content', 'created_at'])
            ->with(['stream:id,space_id,title'])
            ->whereHas('stream', fn (Builder $query) => $query->where('space_id', $space->id));

        return $this->applyContentSearch($query, $search)
            ->orderByDesc('created_at')
            ->orderByDesc('id')
            ->get();
    }

    public function getLatestWithinSpace(Space $space, int $limit = 10): Collection
    {
        return Thought::query()
            ->with(['stream:id,space_id,title'])
            ->whereHas('stream', fn (Builder $query) => $query->where('space_id', $space->id))
            ->latest()
            ->limit($limit)
            ->get();
    }

    public function latestForDay(Space $space, CarbonImmutable $day): ?Thought
    {
        return Thought::query()
            ->select(['id', 'stream_id', 'content', 'created_at'])
            ->with(['stream:id,space_id,title'])
            ->whereHas('stream', fn (Builder $query) => $query->where('space_id', $space->id))
            ->whereDate('created_at', $day->toDateString())
            ->latest('created_at')
            ->first();
    }

    public function randomPastThought(Space $space): ?Thought
    {
        return Thought::query()
            ->select(['id', 'stream_id', 'content', 'created_at'])
            ->with(['stream:id,space_id,title'])
            ->whereHas('stream', fn (Builder $query) => $query->where('space_id', $space->id))
            ->inRandomOrder()
            ->first();
    }

    public function getGraphThoughtsWithinSpace(Space $space): Collection
    {
        return Thought::query()
            ->select(['id', 'stream_id', 'content'])
            ->with(['stream:id,space_id,title'])
            ->whereHas('stream', fn (Builder $query) => $query->where('space_id', $space->id))
            ->orderBy('id')
            ->get();
    }

    public function getGraphLinksWithinSpace(Space $space): Collection
    {
        return ThoughtLink::query()
            ->whereHas('sourceThought.stream', fn (Builder $query) => $query->where('space_id', $space->id))
            ->whereHas('targetThought.stream', fn (Builder $query) => $query->where('space_id', $space->id))
            ->orderBy('id')
            ->get(['id', 'source_thought_id', 'target_thought_id']);
    }

    public function getEvolutionEdgesWithinSpace(Space $space): Collection
    {
        return Thought::query()
            ->whereNotNull('parent_id')
            ->whereHas('stream', fn (Builder $query) => $query->where('space_id', $space->id))
            ->orderBy('id')
            ->get(['id', 'parent_id']);
    }

    public function getThoughtsWithTagsWithinSpace(Space $space, int $limit = 8): Collection
    {
        return Thought::query()
            ->with(['stream:id,space_id,title'])
            ->whereHas('stream', fn (Builder $query) => $query->where('space_id', $space->id))
            ->whereNotNull('tags')
            ->latest('created_at')
            ->limit($limit)
            ->get()
            ->filter(fn (Thought $thought) => ! empty($thought->tags))
            ->values();
    }

    public function getLinkedThoughtPairsWithinSpace(Space $space, int $limit = 2): SupportCollection
    {
        return ThoughtLink::query()
            ->with(['sourceThought.stream:id,space_id,title', 'targetThought.stream:id,space_id,title'])
            ->whereHas('sourceThought.stream', fn (Builder $query) => $query->where('space_id', $space->id))
            ->whereHas('targetThought.stream', fn (Builder $query) => $query->where('space_id', $space->id))
            ->latest('id')
            ->limit($limit)
            ->get()
            ->map(fn (ThoughtLink $link) => [
                'source' => $link->sourceThought,
                'target' => $link->targetThought,
            ]);
    }

    public function getConnectedSynthesisItemsForThought(Thought $thought): Collection
    {
        return ThoughtSynthesisItem::query()
            ->with(['thought.stream:id,space_id,title', 'synthesis.synthesizedThought.stream:id,space_id,title'])
            ->where('thought_id', $thought->id)
            ->orWhereHas('synthesis', fn (Builder $query) => $query->where('synthesized_thought_id', $thought->id))
            ->get();
    }

    public function getAllThoughtIds(): SupportCollection
    {
        return Thought::query()->orderBy('id')->pluck('id');
    }

    public function getAllForUser(int $userId): Collection
    {
        return Thought::query()
            ->with(['stream:id,space_id,title'])
            ->where('user_id', $userId)
            ->orderBy('id')
            ->get();
    }

    public function getEvolutionPairs(): SupportCollection
    {
        return Thought::query()
            ->whereNotNull('parent_id')
            ->get(['id', 'parent_id'])
            ->flatMap(fn (Thought $thought) => [
                ['source' => $thought->parent_id, 'target' => $thought->id],
                ['source' => $thought->id, 'target' => $thought->parent_id],
            ])
            ->values();
    }

    private function applyContentSearch(Builder|Relation $query, string $term): Builder|Relation
    {
        $driver = DB::connection()->getDriverName();

        if ($driver === 'mysql') {
            return $query
                ->whereRaw('MATCH(content) AGAINST(? IN BOOLEAN MODE)', [$term.'*'])
                ->orderByRaw('MATCH(content) AGAINST(? IN BOOLEAN MODE) DESC', [$term.'*']);
        }

        return $query->where('content', 'like', '%'.$term.'%');
    }
}
