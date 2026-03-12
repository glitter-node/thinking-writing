<?php

namespace App\Domain\Stream\Repositories;

use App\Domain\Space\Models\Space;
use App\Domain\Stream\Models\Stream;
use Illuminate\Database\Eloquent\Collection;

class StreamRepository
{
    public function createForSpace(Space $space, array $data): Stream
    {
        return $space->streams()->create($data);
    }

    public function createManyForSpace(Space $space, array $rows): void
    {
        $space->streams()->createMany($rows);
    }

    public function update(Stream $stream, array $data): Stream
    {
        $stream->update($data);

        return $stream->fresh();
    }

    public function delete(Stream $stream): void
    {
        $stream->delete();
    }

    public function decrementPositionsAfter(Space $space, int $position): void
    {
        $space->streams()
            ->where('position', '>', $position)
            ->decrement('position');
    }

    public function nextPositionForSpace(Space $space): int
    {
        return (int) $space->streams()->max('position') + 1;
    }

    public function findById(int $streamId): Stream
    {
        return Stream::query()->findOrFail($streamId);
    }

    public function firstForSpace(Space $space): ?Stream
    {
        return $space->streams()->orderBy('position')->first();
    }

    public function findByIdOrNull(int $streamId): ?Stream
    {
        return Stream::query()->find($streamId);
    }

    public function findLockedById(int $streamId): Stream
    {
        return Stream::query()->lockForUpdate()->findOrFail($streamId);
    }

    public function getOrderedThoughts(Stream $stream): Collection
    {
        return $stream->thoughts()->orderBy('position')->lockForUpdate()->get();
    }

    public function incrementThoughtPositions(Stream $stream): void
    {
        $stream->thoughts()->increment('position');
    }
}
