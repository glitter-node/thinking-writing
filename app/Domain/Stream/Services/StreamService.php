<?php

namespace App\Domain\Stream\Services;

use App\Domain\Space\Models\Space;
use App\Domain\Stream\Models\Stream;
use App\Domain\Stream\Repositories\StreamRepository;
use Illuminate\Support\Facades\DB;

class StreamService
{
    public function __construct(private readonly StreamRepository $streamRepository)
    {
    }

    public function create(Space $space, array $data): Stream
    {
        return DB::transaction(function () use ($space, $data): Stream {
            return $this->streamRepository->createForSpace($space, [
                'title' => $data['title'],
                'position' => $this->streamRepository->nextPositionForSpace($space),
            ]);
        });
    }

    public function update(Stream $stream, array $data): Stream
    {
        return DB::transaction(fn (): Stream => $this->streamRepository->update($stream, $data));
    }

    public function delete(Stream $stream): void
    {
        DB::transaction(function () use ($stream): void {
            $space = $stream->space;
            $deletedPosition = $stream->position;

            $this->streamRepository->delete($stream);
            $this->streamRepository->decrementPositionsAfter($space, $deletedPosition);
        });
    }
}
