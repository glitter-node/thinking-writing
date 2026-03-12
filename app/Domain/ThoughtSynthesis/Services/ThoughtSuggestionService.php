<?php

namespace App\Domain\ThoughtSynthesis\Services;

use App\Domain\Space\Models\Space;
use App\Domain\Thought\Repositories\ThoughtRepository;
use App\Domain\ThoughtReview\Services\ThoughtReviewService;
use Illuminate\Support\Collection;

class ThoughtSuggestionService
{
    public function __construct(
        private readonly ThoughtRepository $thoughtRepository,
        private readonly ThoughtReviewService $thoughtReviewService,
    ) {
    }

    public function getSynthesisSuggestions(int $userId, Space $space): array
    {
        $tagPairs = $this->thoughtRepository->getThoughtsWithTagsWithinSpace($space, 8)
            ->groupBy(fn ($thought) => $thought->tags[0] ?? 'misc')
            ->map(fn (Collection $group) => $group->take(3))
            ->filter(fn (Collection $group) => $group->count() >= 2)
            ->take(2);

        $graphPair = $this->thoughtRepository->getLinkedThoughtPairsWithinSpace($space, 2);
        $reviewSet = $this->thoughtReviewService->getDailyReviewSet($userId, $space->id)->take(3);

        $suggestions = collect();

        foreach ($tagPairs as $tag => $group) {
            $suggestions->push([
                'label' => "Shared tag: #{$tag}",
                'thought_ids' => $group->pluck('id')->all(),
                'thoughts' => $group->map(fn ($thought) => [
                    'id' => $thought->id,
                    'content' => $thought->content,
                ])->all(),
            ]);
        }

        foreach ($graphPair as $pair) {
            $suggestions->push([
                'label' => 'Connected in your graph',
                'thought_ids' => [$pair['source']->id, $pair['target']->id],
                'thoughts' => [
                    ['id' => $pair['source']->id, 'content' => $pair['source']->content],
                    ['id' => $pair['target']->id, 'content' => $pair['target']->content],
                ],
            ]);
        }

        if ($reviewSet->count() >= 2) {
            $suggestions->push([
                'label' => 'Recent review candidates',
                'thought_ids' => $reviewSet->take(3)->pluck('id')->all(),
                'thoughts' => $reviewSet->take(3)->map(fn ($thought) => [
                    'id' => $thought->id,
                    'content' => $thought->content,
                ])->all(),
            ]);
        }

        return $suggestions->take(3)->values()->all();
    }
}
