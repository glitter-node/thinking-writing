<?php

namespace App\Domain\ThoughtSynthesis\Services;

use App\Domain\Space\Models\Space;
use App\Domain\Thought\Events\ThoughtSynthesized;
use App\Domain\Thought\Models\Thought;
use App\Domain\Thought\Repositories\ThoughtRepository;
use App\Domain\Thought\Services\ThoughtService;
use App\Domain\ThoughtSynthesis\Repositories\ThoughtSynthesisRepository;
use App\Models\User;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class ThoughtSynthesisService
{
    public function __construct(
        private readonly ThoughtRepository $thoughtRepository,
        private readonly ThoughtSynthesisRepository $thoughtSynthesisRepository,
        private readonly ThoughtService $thoughtService,
    ) {
    }

    public function createSynthesis(Space $space, User $user, array $thoughtIds, string $content): Thought
    {
        return DB::transaction(function () use ($space, $user, $thoughtIds, $content): Thought {
            $sourceThoughts = $this->resolveSourceThoughts($space, $user->id, $thoughtIds);

            if ($sourceThoughts->count() < 2) {
                throw ValidationException::withMessages([
                    'thought_ids' => 'Select at least two thoughts to synthesize.',
                ]);
            }

            $stream = $sourceThoughts->first()->stream;
            $priority = $sourceThoughts
                ->pluck('priority')
                ->contains('high') ? 'high' : 'medium';
            $tags = $sourceThoughts
                ->pluck('tags')
                ->flatten(1)
                ->filter()
                ->unique()
                ->values()
                ->all();

            $thought = $this->thoughtRepository->createForStream($stream, [
                'user_id' => $user->id,
                'parent_id' => null,
                'content' => $content,
                'priority' => $priority,
                'tags' => $tags,
                'position' => $this->thoughtRepository->nextPositionForStream($stream),
            ]);

            $thought = $this->thoughtService->syncLinks(
                $thought,
                'synthesis',
                fn (Thought $sourceThought, string $label): Thought => $this->thoughtService->createPlaceholder(
                    $sourceThought->stream,
                    $sourceThought->user,
                    $label,
                ),
            );

            $synthesis = $this->thoughtSynthesisRepository->create($user->id, $thought);
            $this->thoughtSynthesisRepository->createItems($synthesis, $sourceThoughts->pluck('id')->all());
            event(new ThoughtSynthesized(
                $thought->id,
                $thought->stream->space_id,
                $thought->user_id,
                $thought->stream_id,
                $sourceThoughts->pluck('id')->map(fn ($id): int => (int) $id)->all(),
            ));
            $this->thoughtService->dispatchThoughtLinked($thought, 'synthesis');

            return $this->thoughtRepository->refreshWithRelations($thought, [
                'stream.space',
                'outgoingLinks.targetThought.stream.space',
                'incomingLinks.sourceThought.stream.space',
                'synthesizedFrom.items.thought.stream.space',
            ]);
        });
    }

    public function getSourceReferences(Thought $thought): Collection
    {
        return $this->thoughtSynthesisRepository->getSourceThoughtsForSynthesis($thought);
    }

    private function resolveSourceThoughts(Space $space, int $userId, array $thoughtIds): Collection
    {
        return Thought::query()
            ->with(['stream.space'])
            ->whereIn('id', collect($thoughtIds)->map(fn ($id) => (int) $id)->unique()->all())
            ->where('user_id', $userId)
            ->whereHas('stream', fn ($query) => $query->where('space_id', $space->id))
            ->orderBy('id')
            ->get();
    }
}
