<?php

namespace App\Domain\Space\Services;

use App\Domain\Space\Models\Space;
use App\Domain\Space\Repositories\SpaceRepository;
use App\Domain\Stream\Repositories\StreamRepository;
use App\Domain\ThinkingPrompt\Services\ThinkingPromptService;
use App\Domain\ThinkingSession\Services\ThinkingSessionService;
use App\Domain\Thought\Repositories\ThoughtRepository;
use App\Domain\ThoughtEmergence\Services\ThoughtEmergenceService;
use App\Domain\ThoughtReview\Services\ThoughtReviewService;
use App\Domain\ThoughtSynthesis\Services\ThoughtSuggestionService;
use Carbon\CarbonImmutable;
use App\Models\User;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Facades\DB;

class SpaceService
{
    public function __construct(
        private readonly SpaceRepository $spaceRepository,
        private readonly StreamRepository $streamRepository,
        private readonly ThoughtRepository $thoughtRepository,
        private readonly ThoughtReviewService $thoughtReviewService,
        private readonly ThinkingPromptService $thinkingPromptService,
        private readonly ThinkingSessionService $thinkingSessionService,
        private readonly ThoughtSuggestionService $thoughtSuggestionService,
        private readonly ThoughtEmergenceService $thoughtEmergenceService,
    ) {
    }

    public function listForUser(User $user): Collection
    {
        return $this->spaceRepository->getForUserWithCounts($user);
    }

    public function getBoard(Space $space, User $user, ?string $search = null): array
    {
        $space = $this->spaceRepository->loadBoard($space, $search);

        return [
            'space' => $space,
            'promptPack' => $this->thinkingPromptService->getBoardPromptPack($user->id),
            'streak' => $this->thinkingSessionService->getStreak($user->id),
            'synthesisSuggestions' => $this->thoughtSuggestionService->getSynthesisSuggestions($user->id, $space),
            'emergenceSuggestions' => $this->getEmergenceSuggestions($space),
            'streamOptions' => $space->streams->map(fn ($stream) => [
                'id' => $stream->id,
                'title' => $stream->title,
            ]),
        ];
    }

    public function create(User $user, array $data): Space
    {
        return DB::transaction(function () use ($user, $data): Space {
            $space = $this->spaceRepository->createForUser($user, $data);

            $this->streamRepository->createManyForSpace($space, [
                ['title' => 'Inbox', 'position' => 1],
                ['title' => 'In Progress', 'position' => 2],
                ['title' => 'Done', 'position' => 3],
            ]);

            return $space->fresh('streams');
        });
    }

    public function update(Space $space, array $data): Space
    {
        return DB::transaction(fn (): Space => $this->spaceRepository->update($space, $data));
    }

    public function delete(Space $space): void
    {
        DB::transaction(fn () => $this->spaceRepository->delete($space));
    }

    public function searchThoughts(Space $space, string $search): array
    {
        $term = trim($search);

        if ($term === '') {
            return [];
        }

        return $this->thoughtRepository
            ->searchWithinSpace($space, $term)
            ->map(fn ($thought) => [
                'id' => $thought->id,
                'stream_id' => $thought->stream_id,
                'stream_title' => $thought->stream->title,
                'highlighted_content' => $this->highlightMatch($thought->content, $term),
            ])
            ->all();
    }

    public function rediscover(Space $space): array
    {
        $now = CarbonImmutable::now();

        return [
            $this->formatRediscoveryEntry('Today', $this->thoughtRepository->latestForDay($space, $now)),
            $this->formatRediscoveryEntry('7 days ago', $this->thoughtRepository->latestForDay($space, $now->subDays(7))),
            $this->formatRediscoveryEntry('30 days ago', $this->thoughtRepository->latestForDay($space, $now->subDays(30))),
            $this->formatRediscoveryEntry('Random past thought', $this->thoughtRepository->randomPastThought($space)),
        ];
    }

    public function getReviewSuggestions(int $userId, Space $space): array
    {
        return $this->thoughtReviewService
            ->getDailyReviewSet($userId, $space->id)
            ->map(fn ($thought) => [
                'id' => $thought->id,
                'stream_id' => $thought->stream_id,
                'stream_title' => $thought->stream->title,
                'content' => $thought->content,
                'priority' => $thought->priority,
            ])
            ->all();
    }

    private function formatRediscoveryEntry(string $label, mixed $thought): array
    {
        return [
            'label' => $label,
            'thought' => $thought ? [
                'id' => $thought->id,
                'stream_id' => $thought->stream_id,
                'stream_title' => $thought->stream->title,
                'content' => $thought->content,
                'created_at' => $thought->created_at->toIso8601String(),
                'created_at_human' => $thought->created_at->diffForHumans(),
            ] : null,
        ];
    }

    private function getEmergenceSuggestions(Space $space): array
    {
        $seedThought = $this->thoughtRepository
            ->getLatestWithinSpace($space, 1)
            ->first();

        if (! $seedThought) {
            return [
                'thought' => null,
                'related_thoughts' => [],
                'emerging_themes' => [],
            ];
        }

        return $this->thoughtEmergenceService->suggestConnections($seedThought);
    }

    private function highlightMatch(string $content, string $term): string
    {
        $pattern = '/('.preg_quote($term, '/').')/i';
        $parts = preg_split($pattern, $content, -1, PREG_SPLIT_DELIM_CAPTURE | PREG_SPLIT_NO_EMPTY);

        if ($parts === false) {
            return e($content);
        }

        return collect($parts)
            ->map(function (string $part) use ($term): string {
                if (mb_strtolower($part) === mb_strtolower($term)) {
                    return '<mark class="rounded bg-orange-300/30 px-1 text-orange-100">'.e($part).'</mark>';
                }

                return e($part);
            })
            ->implode('');
    }
}
