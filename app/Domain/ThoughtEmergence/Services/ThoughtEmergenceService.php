<?php

namespace App\Domain\ThoughtEmergence\Services;

use App\Domain\Space\Models\Space;
use App\Domain\Thought\Models\Thought;
use App\Domain\Thought\Repositories\ThoughtRepository;
use App\Domain\ThoughtEmergence\Repositories\ThoughtCooccurrenceRepository;
use App\Domain\ThoughtEmergence\Repositories\ThoughtTagIndexRepository;
use App\Domain\ThoughtLink\Repositories\ThoughtLinkRepository;
use App\Domain\ThoughtSynthesis\Repositories\ThoughtSynthesisRepository;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;

class ThoughtEmergenceService
{
    public function __construct(
        private readonly ThoughtRepository $thoughtRepository,
        private readonly ThoughtTagIndexRepository $thoughtTagIndexRepository,
        private readonly ThoughtCooccurrenceRepository $thoughtCooccurrenceRepository,
        private readonly ThoughtLinkRepository $thoughtLinkRepository,
        private readonly ThoughtSynthesisRepository $thoughtSynthesisRepository,
    ) {
    }

    public function updateThoughtIndexes(Thought $thought): void
    {
        $this->thoughtTagIndexRepository->replaceForThought($thought, $thought->tags ?? []);
    }

    public function calculateTagClusters(int $userId): array
    {
        return $this->thoughtTagIndexRepository
            ->getTrendingTagsForUser($userId, 10)
            ->map(fn ($row): array => [
                'tag' => $row->tag,
                'usage_count' => (int) $row->usage_count,
            ])
            ->all();
    }

    public function calculateCooccurrence(int $userId): void
    {
        $thoughts = $this->thoughtRepository->getAllForUser($userId);
        $thoughtIds = $thoughts->pluck('id')->all();
        $linkedPairs = $this->thoughtLinkRepository->pairKeysForThoughtIds($thoughtIds);
        $synthesisPairs = $this->thoughtSynthesisRepository->pairKeysForThoughtIds($thoughtIds);
        $rows = [];

        foreach ($thoughts as $index => $thoughtA) {
            foreach ($thoughts->slice($index + 1) as $thoughtB) {
                $score = $this->scorePair($thoughtA, $thoughtB, $linkedPairs, $synthesisPairs);

                if ($score <= 0) {
                    continue;
                }

                $rows[] = [
                    'thought_a_id' => min($thoughtA->id, $thoughtB->id),
                    'thought_b_id' => max($thoughtA->id, $thoughtB->id),
                    'score' => $score,
                    'created_at' => now(),
                ];
            }
        }

        $this->thoughtCooccurrenceRepository->replaceForThoughtIds($thoughtIds, $rows);
    }

    public function suggestConnections(Thought $thought): array
    {
        $cooccurrences = $this->thoughtCooccurrenceRepository->topForThought($thought->id, 6);
        $relatedThoughts = $cooccurrences->map(function ($row) use ($thought): array {
            $related = $row->thought_a_id === $thought->id ? $row->thoughtB : $row->thoughtA;

            return [
                'id' => $related->id,
                'content' => $related->content,
                'stream_title' => $related->stream->title,
                'score' => (int) $row->score,
            ];
        })->all();

        $sharedTags = $this->thoughtTagIndexRepository
            ->getSharedTagsForThoughtIds(array_merge([$thought->id], collect($relatedThoughts)->pluck('id')->all()))
            ->groupBy('tag')
            ->filter(fn (Collection $rows) => $rows->pluck('thought_id')->unique()->count() >= 2)
            ->sortByDesc(fn (Collection $rows) => $rows->count())
            ->take(5)
            ->map(fn (Collection $rows, string $tag): array => [
                'tag' => $tag,
                'thought_count' => $rows->pluck('thought_id')->unique()->count(),
            ])
            ->values()
            ->all();

        return [
            'thought' => [
                'id' => $thought->id,
                'content' => $thought->content,
            ],
            'related_thoughts' => $relatedThoughts,
            'emerging_themes' => $sharedTags,
        ];
    }

    public function getDashboard(int $userId, ?Space $space = null): array
    {
        $trendingTags = $this->calculateTagClusters($userId);
        $strongPairs = $this->thoughtCooccurrenceRepository
            ->topForUser($userId, 8)
            ->filter(function ($row) use ($space) {
                if (! $space) {
                    return true;
                }

                return $row->thoughtA->stream->space_id === $space->id
                    && $row->thoughtB->stream->space_id === $space->id;
            })
            ->map(fn ($row): array => [
                'thought_a' => [
                    'id' => $row->thoughtA->id,
                    'content' => $row->thoughtA->content,
                ],
                'thought_b' => [
                    'id' => $row->thoughtB->id,
                    'content' => $row->thoughtB->content,
                ],
                'score' => (int) $row->score,
            ])
            ->values()
            ->all();

        return [
            'trending_tags' => $trendingTags,
            'thought_clusters' => collect($trendingTags)->take(5)->all(),
            'strong_connections' => $strongPairs,
        ];
    }

    public function rebuildForUser(int $userId): void
    {
        $thoughts = $this->thoughtRepository->getAllForUser($userId);

        DB::transaction(function () use ($userId, $thoughts): void {
            $this->thoughtTagIndexRepository->rebuildFromThoughts($thoughts, $userId);
            $this->calculateCooccurrence($userId);
        });
    }

    private function scorePair(Thought $thoughtA, Thought $thoughtB, array $linkedPairs, array $synthesisPairs): int
    {
        $score = 0;

        $sharedTags = collect($thoughtA->tags ?? [])
            ->intersect($thoughtB->tags ?? [])
            ->count();

        if ($sharedTags >= 3) {
            $score += 5;
        } elseif ($sharedTags > 0) {
            $score += $sharedTags;
        }

        if ($this->hasPair($linkedPairs, $thoughtA->id, $thoughtB->id)) {
            $score += 2;
        }

        if ($this->hasPair($synthesisPairs, $thoughtA->id, $thoughtB->id)) {
            $score += 4;
        }

        $hoursApart = abs($thoughtA->created_at->diffInHours($thoughtB->created_at));

        if ($hoursApart <= 24) {
            $score += 2;
        } elseif ($hoursApart <= 72) {
            $score += 1;
        }

        return $score;
    }

    private function hasPair(array $pairs, int $thoughtAId, int $thoughtBId): bool
    {
        return $pairs[$this->pairKey($thoughtAId, $thoughtBId)] ?? false;
    }

    private function pairKey(int $thoughtAId, int $thoughtBId): string
    {
        return min($thoughtAId, $thoughtBId).':'.max($thoughtAId, $thoughtBId);
    }
}
