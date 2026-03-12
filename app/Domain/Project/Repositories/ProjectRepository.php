<?php

namespace App\Domain\Project\Repositories;

use App\Domain\Project\Models\Project;
use App\Domain\Thought\Models\Thought;
use Illuminate\Database\Eloquent\Collection;

class ProjectRepository
{
    public function createForThought(Thought $thought, array $data): Project
    {
        return $thought->project()->create($data);
    }

    public function update(Project $project, array $data): Project
    {
        $project->update($data);

        return $project->fresh(['thought', 'tasks']);
    }

    public function forUser(int $userId): Collection
    {
        return Project::query()
            ->with(['thought.stream.space', 'tasks'])
            ->whereHas('thought', fn ($query) => $query->where('user_id', $userId))
            ->orderBy('status')
            ->orderByDesc('id')
            ->get();
    }

    public function withinSpace(int $spaceId): Collection
    {
        return Project::query()
            ->with(['thought.stream.space', 'tasks'])
            ->whereHas('thought.stream', fn ($query) => $query->where('space_id', $spaceId))
            ->orderBy('id')
            ->get();
    }
}
