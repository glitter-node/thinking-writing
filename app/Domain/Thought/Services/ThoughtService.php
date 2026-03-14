<?php

namespace App\Domain\Thought\Services;

use App\Domain\Thought\Events\ThoughtCreated;
use App\Domain\Thought\Events\ThoughtDeleted;
use App\Domain\Thought\Events\ThoughtLinked;
use App\Domain\Thought\Events\ThoughtPlaceholderCreated;
use App\Domain\Space\Models\Space;
use App\Domain\Stream\Models\Stream;
use App\Domain\Stream\Repositories\StreamRepository;
use App\Domain\Thought\Models\Thought;
use App\Domain\Thought\Repositories\ThoughtRepository;
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

            $thought = $this->syncLinks($thought, 'standard');
            event(new ThoughtCreated(
                $thought->id,
                $thought->stream->space_id,
                $thought->user_id,
                $thought->stream_id,
                'standard',
            ));
            $this->dispatchThoughtLinked($thought, 'standard');

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

            $thought = $this->syncLinks($thought, 'update');
            $this->dispatchThoughtLinked($thought, 'update');

            return $thought;
        });
    }

    public function delete(Thought $thought): void
    {
        DB::transaction(function () use ($thought): void {
            $stream = $thought->stream;
            $deletedPosition = $thought->position;

            $this->thoughtRepository->delete($thought);
            $this->thoughtRepository->decrementPositionsAfter($stream, $deletedPosition);
            event(new ThoughtDeleted(
                $thought->id,
                $stream->space_id,
                $thought->user_id,
                $stream->id,
                $deletedPosition,
            ));
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

            $thought = $this->syncLinks($thought, 'quick');
            event(new ThoughtCreated(
                $thought->id,
                $thought->stream->space_id,
                $thought->user_id,
                $thought->stream_id,
                'quick',
            ));
            $this->dispatchThoughtLinked($thought, 'quick');

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

    public function createPlaceholder(Stream $stream, User $user, string $content): Thought
    {
        return DB::transaction(function () use ($stream, $user, $content): Thought {
            $thought = $this->thoughtRepository->createForStream($stream, [
                'user_id' => $user->id,
                'parent_id' => null,
                'content' => $content,
                'priority' => 'low',
                'tags' => ['placeholder'],
                'position' => $this->thoughtRepository->nextPositionForStream($stream),
            ]);

            $thought = $this->syncLinks($thought, 'placeholder');
            event(new ThoughtPlaceholderCreated(
                $thought->id,
                $thought->stream->space_id,
                $thought->user_id,
                $thought->stream_id,
            ));
            $this->dispatchThoughtLinked($thought, 'placeholder');

            return $thought;
        });
    }

    public function syncLinks(Thought $thought, string $source, ?callable $missingThoughtCreator = null): Thought
    {
        return $source === 'update'
            ? $this->thoughtLinkService->updateLinks($thought, $missingThoughtCreator ?? $this->placeholderCreator())
            : $this->thoughtLinkService->createLinks($thought, $missingThoughtCreator ?? $this->placeholderCreator());
    }

    private function placeholderCreator(): callable
    {
        return fn (Thought $sourceThought, string $label): Thought => $this->createPlaceholder(
            $sourceThought->stream,
            $sourceThought->user,
            $label,
        );
    }

    public function dispatchThoughtLinked(Thought $thought, string $source): void
    {
        event(new ThoughtLinked(
            $thought->id,
            $thought->stream->space_id,
            $thought->user_id,
            $thought->stream_id,
            $thought->outgoingLinks->pluck('target_thought_id')->map(fn ($id): int => (int) $id)->values()->all(),
            $source,
        ));
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
