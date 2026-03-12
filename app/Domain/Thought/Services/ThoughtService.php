<?php

namespace App\Domain\Thought\Services;

use App\Domain\Space\Models\Space;
use App\Domain\Stream\Models\Stream;
use App\Domain\ThoughtEmergence\Services\ThoughtEmergenceService;
use App\Domain\Stream\Repositories\StreamRepository;
use App\Domain\ThinkingSession\Services\ThinkingSessionService;
use App\Domain\Thought\Models\Thought;
use App\Domain\Thought\Repositories\ThoughtRepository;
use App\Domain\ThoughtEvent\Services\ThoughtEventService;
use App\Domain\ThoughtVersion\Services\ThoughtVersionService;
use App\Models\User;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class ThoughtService
{
    public function __construct(
        private readonly ThoughtRepository $thoughtRepository,
        private readonly StreamRepository $streamRepository,
        private readonly ThoughtLinkService $thoughtLinkService,
        private readonly ThinkingSessionService $thinkingSessionService,
        private readonly ThoughtEmergenceService $thoughtEmergenceService,
        private readonly ThoughtVersionService $thoughtVersionService,
        private readonly ThoughtEventService $thoughtEventService,
    ) {
    }

    public function create(Stream $stream, User $user, array $data): Thought
    {
        return DB::transaction(function () use ($stream, $user, $data): Thought {
            $thought = $this->thoughtRepository->createForStream($stream, [
                'user_id' => $user->id,
                'parent_id' => null,
                'content' => $data['content'],
                'priority' => $data['priority'],
                'tags' => $this->normalizeTags($data['tags'] ?? ''),
                'position' => $this->thoughtRepository->nextPositionForStream($stream),
            ]);

            $thought = $this->thoughtLinkService->createLinks($thought);
            $this->thoughtVersionService->createInitialVersion($thought);
            $this->thoughtEventService->recordEvent($thought, 'ThoughtCreated', ['source' => 'standard']);
            $this->thoughtEventService->recordEvent($thought, 'ThoughtLinked', ['source' => 'standard']);
            $this->thoughtEmergenceService->updateThoughtIndexes($thought);
            $this->thoughtEmergenceService->calculateCooccurrence($user->id);
            $this->thinkingSessionService->recordThought($user->id);

            return $thought;
        });
    }

    public function update(Thought $thought, array $data): Thought
    {
        return DB::transaction(function () use ($thought, $data): Thought {
            $thought = $this->thoughtRepository->update($thought, [
                'content' => $data['content'],
                'priority' => $data['priority'],
                'tags' => $this->normalizeTags($data['tags'] ?? ''),
            ]);

            $thought = $this->thoughtLinkService->updateLinks($thought);
            $this->thoughtVersionService->createInitialVersion($thought);
            $this->thoughtVersionService->createVersion($thought, $data['content']);
            $this->thoughtEventService->recordEvent($thought, 'ThoughtEdited', [
                'priority' => $data['priority'],
                'tags' => $this->normalizeTags($data['tags'] ?? ''),
            ]);
            $this->thoughtEventService->recordEvent($thought, 'ThoughtLinked', ['source' => 'update']);
            $this->thoughtEmergenceService->updateThoughtIndexes($thought);
            $this->thoughtEmergenceService->calculateCooccurrence($thought->user_id);

            return $thought;
        });
    }

    public function delete(Thought $thought): void
    {
        DB::transaction(function () use ($thought): void {
            $stream = $thought->stream;
            $deletedPosition = $thought->position;

            $this->thoughtRepository->delete($thought);
            $this->thoughtEventService->recordEvent($thought, 'ThoughtArchived', ['stream_id' => $stream->id]);
            $this->thoughtRepository->decrementPositionsAfter($stream, $deletedPosition);
        });
    }

    public function createQuick(Space $space, User $user, array $data): Thought
    {
        return DB::transaction(function () use ($space, $user, $data): Thought {
            $stream = $this->streamRepository->firstForSpace($space);

            if (! $stream) {
                throw ValidationException::withMessages([
                    'content' => 'Create a stream before capturing thoughts.',
                ]);
            }

            $this->streamRepository->incrementThoughtPositions($stream);

            $thought = $this->thoughtRepository->createForStream($stream, [
                'user_id' => $user->id,
                'parent_id' => null,
                'content' => $data['content'],
                'priority' => 'medium',
                'tags' => [],
                'position' => 1,
            ]);

            $thought = $this->thoughtLinkService->createLinks($thought);
            $this->thoughtVersionService->createInitialVersion($thought);
            $this->thoughtEventService->recordEvent($thought, 'ThoughtCreated', ['source' => 'quick']);
            $this->thoughtEventService->recordEvent($thought, 'ThoughtLinked', ['source' => 'quick']);
            $this->thoughtEmergenceService->updateThoughtIndexes($thought);
            $this->thoughtEmergenceService->calculateCooccurrence($user->id);
            $this->thinkingSessionService->recordThought($user->id);

            return $thought;
        });
    }

    public function move(Thought $thought, int $targetStreamId, int $targetPosition): Thought
    {
        return DB::transaction(function () use ($thought, $targetStreamId, $targetPosition): Thought {
            $sourceStream = $thought->stream()->lockForUpdate()->firstOrFail();
            $targetStream = $this->streamRepository->findLockedById($targetStreamId);

            $sourceThoughts = $this->streamRepository->getOrderedThoughts($sourceStream);
            $targetThoughts = $this->streamRepository->getOrderedThoughts($targetStream);

            $sourceSequence = $sourceThoughts
                ->reject(fn (Thought $item): bool => $item->id === $thought->id)
                ->values();

            $targetSequence = $sourceStream->is($targetStream)
                ? $sourceSequence
                : $targetThoughts->values();

            $insertIndex = max(0, min($targetPosition - 1, $targetSequence->count()));
            $targetSequence->splice($insertIndex, 0, [$thought]);

            if ($sourceStream->is($targetStream)) {
                $this->persistSequence($sourceStream, $targetSequence);
            } else {
                $this->persistSequence($sourceStream, $sourceSequence);
                $thought->stream_id = $targetStream->id;
                $thought->save();
                $this->persistSequence($targetStream, $targetSequence);
            }

            return $this->thoughtRepository->refreshWithRelations($thought, ['stream.space']);
        });
    }

    public function normalizeTags(array|string|null $tags): array
    {
        $items = match (true) {
            is_array($tags) => $tags,
            is_string($tags) => explode(',', $tags),
            default => [],
        };

        return collect($items)
            ->map(function (mixed $tag): string {
                return trim((string) $tag);
            })
            ->filter()
            ->unique()
            ->values()
            ->all();
    }

    public function recordThinkingMomentum(int $userId): void
    {
        $this->thinkingSessionService->recordThought($userId);
    }

    private function persistSequence(Stream $stream, Collection $thoughts): void
    {
        $thoughts->values()->each(function (Thought $thought, int $index) use ($stream): void {
            $thought->stream_id = $stream->id;
            $thought->position = $index + 1;
            $thought->save();
        });
    }
}
