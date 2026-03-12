<?php

namespace App\Domain\Task\Repositories;

use App\Domain\Project\Models\Project;
use App\Domain\Task\Models\Task;

class TaskRepository
{
    public function createMany(Project $project, array $rows): void
    {
        $project->tasks()->createMany($rows);
    }

    public function update(Task $task, array $data): Task
    {
        $task->update($data);

        return $task->fresh(['project.thought']);
    }
}
