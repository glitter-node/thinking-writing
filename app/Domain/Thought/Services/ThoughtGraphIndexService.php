<?php

namespace App\Domain\Thought\Services;

use App\Domain\Thought\Models\Thought;
use App\Domain\Thought\Repositories\ThoughtRepository;
use App\Domain\ThoughtGraphIndex\Repositories\ThoughtGraphIndexRepository;
use App\Domain\ThoughtLink\Repositories\ThoughtLinkRepository;
use App\Domain\ThoughtSynthesis\Repositories\ThoughtSynthesisRepository;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Throwable;

class ThoughtGraphIndexService
{
    private const MAX_DEPTH = 3;

    public function __construct(
        private readonly ThoughtGraphIndexRepository $thoughtGraphIndexRepository,
        private readonly ThoughtRepository $thoughtRepository,
        private readonly ThoughtLinkRepository $thoughtLinkRepository,
        private readonly ThoughtSynthesisRepository $thoughtSynthesisRepository,
    ) {
    }

    public function updateGraphIndex(int $thoughtId): void
    {
        $adjacencyMap = $this->buildAdjacencyMap();
        $impactedThoughtIds = collect([$thoughtId])
            ->merge(collect($adjacencyMap[$thoughtId] ?? [])->pluck('id'))
            ->unique()
            ->values()
            ->all();

        DB::transaction(function () use ($adjacencyMap, $impactedThoughtIds): void {
            $this->thoughtGraphIndexRepository->deleteForThoughtIds($impactedThoughtIds);
            $this->thoughtGraphIndexRepository->insertRows($this->buildRowsForThoughtIds($adjacencyMap, $impactedThoughtIds));
        });

        $this->flushGraphCache($impactedThoughtIds);
    }

    public function rebuildGraphIndex(): void
    {
        $adjacencyMap = $this->buildAdjacencyMap();
        $thoughtIds = $this->thoughtRepository->getAllThoughtIds()->all();

        DB::transaction(function () use ($adjacencyMap, $thoughtIds): void {
            $this->thoughtGraphIndexRepository->truncate();
            $this->thoughtGraphIndexRepository->insertRows($this->buildRowsForThoughtIds($adjacencyMap, $thoughtIds));
        });

        $this->flushGraphCache($thoughtIds);
    }

    public function getConnectedThoughts(int $thoughtId, int $maxDepth = 2): Collection
    {
        $depth = max(1, min($maxDepth, self::MAX_DEPTH));
        $cacheKey = "thought_graph:{$thoughtId}:{$depth}";

        try {
            return collect(Cache::store('redis')->remember($cacheKey, now()->addHour(), function () use ($thoughtId, $depth) {
                $rows = $this->thoughtGraphIndexRepository->neighborsForThought($thoughtId, $depth);

                if ($rows->isEmpty()) {
                    $this->updateGraphIndex($thoughtId);
                    $rows = $this->thoughtGraphIndexRepository->neighborsForThought($thoughtId, $depth);
                }

                return $this->formatNeighbors($rows);
            }));
        } catch (Throwable) {
            return collect(Cache::remember($cacheKey.':fallback', now()->addMinutes(15), function () use ($thoughtId, $depth) {
                $rows = $this->thoughtGraphIndexRepository->neighborsForThought($thoughtId, $depth);

                if ($rows->isEmpty()) {
                    $this->updateGraphIndex($thoughtId);
                    $rows = $this->thoughtGraphIndexRepository->neighborsForThought($thoughtId, $depth);
                }

                return $this->formatNeighbors($rows);
            }));
        }
    }

    private function buildRowsForThoughtIds(array $adjacencyMap, array $thoughtIds): array
    {
        $rows = [];

        foreach ($thoughtIds as $thoughtId) {
            $rows = [...$rows, ...$this->buildTraversalRows($adjacencyMap, (int) $thoughtId)];
        }

        return $rows;
    }

    private function buildTraversalRows(array $adjacencyMap, int $originThoughtId): array
    {
        $visitedDepths = [];
        $queue = collect($adjacencyMap[$originThoughtId] ?? [])
            ->map(fn (array $edge): array => [
                'id' => $edge['id'],
                'type' => $edge['type'],
                'depth' => 1,
            ]);
        $rows = [];

        while ($queue->isNotEmpty()) {
            $current = $queue->shift();
            $neighborId = (int) $current['id'];
            $depth = (int) $current['depth'];

            if ($neighborId === $originThoughtId || $depth > self::MAX_DEPTH) {
                continue;
            }

            if (isset($visitedDepths[$neighborId]) && $visitedDepths[$neighborId] <= $depth) {
                continue;
            }

            $visitedDepths[$neighborId] = $depth;
            $rows[] = [
                'thought_id' => $originThoughtId,
                'linked_thought_id' => $neighborId,
                'link_type' => $current['type'],
                'depth' => $depth,
                'created_at' => now(),
            ];

            foreach ($adjacencyMap[$neighborId] ?? [] as $edge) {
                $queue->push([
                    'id' => $edge['id'],
                    'type' => $current['type'],
                    'depth' => $depth + 1,
                ]);
            }
        }

        return $rows;
    }

    private function buildAdjacencyMap(): array
    {
        $adjacencyMap = [];

        foreach ($this->thoughtLinkRepository->getAdjacencyPairs() as $pair) {
            $adjacencyMap[$pair['source']][] = ['id' => $pair['target'], 'type' => 'direct'];
        }

        foreach ($this->thoughtRepository->getEvolutionPairs() as $pair) {
            $adjacencyMap[$pair['source']][] = ['id' => $pair['target'], 'type' => 'evolution'];
        }

        foreach ($this->thoughtSynthesisRepository->getAdjacencyPairs() as $pair) {
            $adjacencyMap[$pair['source']][] = ['id' => $pair['target'], 'type' => 'synthesis'];
        }

        return $adjacencyMap;
    }

    private function formatNeighbors(Collection $rows): array
    {
        return $rows->map(fn ($row): array => [
            'thought_id' => $row->thought_id,
            'linked_thought_id' => $row->linked_thought_id,
            'link_type' => $row->link_type,
            'depth' => $row->depth,
            'linked_thought' => [
                'id' => $row->linkedThought->id,
                'content' => $row->linkedThought->content,
                'stream_title' => $row->linkedThought->stream->title,
            ],
        ])->all();
    }

    private function flushGraphCache(array $thoughtIds): void
    {
        foreach (collect($thoughtIds)->unique() as $thoughtId) {
            foreach (range(1, self::MAX_DEPTH) as $depth) {
                try {
                    Cache::store('redis')->forget("thought_graph:{$thoughtId}:{$depth}");
                } catch (Throwable) {
                    // Ignore fallback cache invalidation failures.
                }

                Cache::forget("thought_graph:{$thoughtId}:{$depth}:fallback");
            }
        }
    }
}
