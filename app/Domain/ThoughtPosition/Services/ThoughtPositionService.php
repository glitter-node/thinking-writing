<?php

namespace App\Domain\ThoughtPosition\Services;

use App\Domain\Space\Models\Space;
use App\Domain\Thought\Models\Thought;
use App\Domain\ThoughtPosition\Repositories\ThoughtPositionRepository;
use Illuminate\Support\Facades\DB;

class ThoughtPositionService
{
    public function __construct(
        private readonly ThoughtPositionRepository $thoughtPositionRepository,
    ) {
    }

    public function store(Thought $thought, array $data): array
    {
        $space = $thought->stream->space;

        $position = DB::transaction(fn () => $this->thoughtPositionRepository->upsertForThought(
            $thought,
            $space,
            (int) $data['x'],
            (int) $data['y'],
        ));

        return [
            'thought_id' => $position->thought_id,
            'space_id' => $position->space_id,
            'x' => $position->x,
            'y' => $position->y,
        ];
    }
}
