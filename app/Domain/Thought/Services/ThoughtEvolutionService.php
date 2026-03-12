<?php

namespace App\Domain\Thought\Services;

use App\Domain\Thought\Models\Thought;
use App\Domain\Thought\Repositories\ThoughtEvolutionRepository;
use App\Domain\Thought\Repositories\ThoughtRepository;
use App\Domain\ThoughtEvent\Services\ThoughtEventService;
use App\Domain\ThoughtEmergence\Services\ThoughtEmergenceService;
use App\Domain\ThoughtVersion\Services\ThoughtVersionService;
use App\Domain\ThinkingSession\Services\ThinkingSessionService;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;

class ThoughtEvolutionService
{
    public function __construct(
        private readonly ThoughtRepository $thoughtRepository,
        private readonly ThoughtEvolutionRepository $thoughtEvolutionRepository,
        private readonly ThoughtLinkService $thoughtLinkService,
        private readonly ThinkingSessionService $thinkingSessionService,
        private readonly ThoughtGraphIndexService $thoughtGraphIndexService,
        private readonly ThoughtEmergenceService $thoughtEmergenceService,
        private readonly ThoughtVersionService $thoughtVersionService,
        private readonly ThoughtEventService $thoughtEventService,
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

            $thought = $this->thoughtLinkService->createLinks($thought);
            $this->thoughtVersionService->createInitialVersion($thought);
            $this->thoughtEventService->recordEvent($thought, 'ThoughtCreated', [
                'source' => 'evolution',
                'parent_id' => $parentThought->id,
            ]);
            $this->thoughtEventService->recordEvent($thought, 'ThoughtLinked', ['source' => 'evolution']);
            $this->thoughtEmergenceService->updateThoughtIndexes($thought);
            $this->thoughtEmergenceService->calculateCooccurrence($parentThought->user_id);
            $this->thoughtGraphIndexService->updateGraphIndex($parentThought->id);
            $this->thoughtGraphIndexService->updateGraphIndex($thought->id);
            $this->thinkingSessionService->recordThought($parentThought->user_id);

            return $thought;
        });
    }

    public function getThoughtThread(Thought $thought): Collection
    {
        return $this->thoughtEvolutionRepository->getThreadThoughts($thought);
    }
}
