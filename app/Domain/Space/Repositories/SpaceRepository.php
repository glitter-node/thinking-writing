<?php

namespace App\Domain\Space\Repositories;

use App\Domain\Space\Models\Space;
use App\Domain\Thought\Repositories\ThoughtRepository;
use App\Models\User;
use Illuminate\Database\Eloquent\Collection;

class SpaceRepository
{
    public function __construct(private readonly ThoughtRepository $thoughtRepository)
    {
    }

    public function getForUserWithCounts(User $user): Collection
    {
        return $user->spaces()
            ->withCount(['streams', 'thoughts'])
            ->latest()
            ->get();
    }

    public function loadBoard(Space $space, ?string $search = null): Space
    {
        $space->load([
            'streams' => fn ($streamQuery) => $streamQuery
                ->orderBy('position')
                ->with([
                    'thoughts' => fn ($thoughtQuery) => $this->thoughtRepository
                        ->applySearch($thoughtQuery->orderBy('position'), $search)
                        ->with([
                            'outgoingLinks.targetThought.stream.space',
                            'incomingLinks.sourceThought.stream.space',
                            'project.tasks',
                            'synthesizedFrom.items.thought.stream.space',
                            'versions',
                            'events',
                        ]),
                ]),
        ]);

        return $space;
    }

    public function createForUser(User $user, array $data): Space
    {
        return $user->spaces()->create($data);
    }

    public function update(Space $space, array $data): Space
    {
        $space->update($data);

        return $space->fresh();
    }

    public function delete(Space $space): void
    {
        $space->delete();
    }
}
