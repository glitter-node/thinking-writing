<?php

namespace App\Domain\Thought\Services;

use App\Domain\Thought\Events\ThoughtEvolved;
use App\Domain\Thought\Models\Thought;
use App\Domain\Thought\Repositories\ThoughtEvolutionRepository;
use App\Domain\Thought\Repositories\ThoughtRepository;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;

class ThoughtEvolutionService
{
    public function __construct(
        private readonly ThoughtService $thoughtService,
        private readonly ThoughtRepository $thoughtRepository,
        private readonly ThoughtEvolutionRepository $thoughtEvolutionRepository,
    ) {
    }

    public function createEvolution(Thought $parentThought, array $data): Thought
    {
        return DB::transaction(function () use ($parentThought, $data): Thought {
            $parentThought->stream->thoughts()
                ->where('position', '>=', $parentThought->position + 1)
                ->increment('position');

            $thought = $this->thoughtRepository->createForStream($parentThought->stream, [
                'user_id' => $parentThought->user_id,
                'parent_id' => $parentThought->id,
                'content' => $data['content'],
                'priority' => $data['priority'] ?? $parentThought->priority,
                'tags' => $data['tags'] ?? $parentThought->tags ?? [],
                'position' => $parentThought->position + 1,
            ]);

            $thought = $this->thoughtService->syncLinks(
                $thought,
                'evolution',
                fn (Thought $sourceThought, string $label): Thought => $this->thoughtService->createPlaceholder(
                    $sourceThought->stream,
                    $sourceThought->user,
                    $label,
                ),
            );

            event(new ThoughtEvolved(
                $thought->id,
                $thought->stream->space_id,
                $thought->user_id,
                $thought->stream_id,
                $parentThought->id,
            ));
            $this->thoughtService->dispatchThoughtLinked($thought, 'evolution');

            return $thought;
        });
    }

    public function getThoughtThread(Thought $thought): Collection
    {
        return $this->thoughtEvolutionRepository->getThreadThoughts($thought);
    }
}
