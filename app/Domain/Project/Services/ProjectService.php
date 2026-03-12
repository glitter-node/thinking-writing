<?php

namespace App\Domain\Project\Services;

use App\Domain\Project\Models\Project;
use App\Domain\Project\Repositories\ProjectRepository;
use Illuminate\Database\Eloquent\Collection;

class ProjectService
{
    public function __construct(
        private readonly ProjectRepository $projectRepository,
    ) {
    }

    public function getBoard(int $userId): Collection
    {
        return $this->projectRepository->forUser($userId);
    }

    public function refresh(Project $project): Project
    {
        return $project->fresh(['thought.stream.space', 'tasks']);
    }
}
