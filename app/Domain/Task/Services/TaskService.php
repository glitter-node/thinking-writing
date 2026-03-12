<?php

namespace App\Domain\Task\Services;

use App\Domain\Task\Models\Task;
use App\Domain\Task\Repositories\TaskRepository;

class TaskService
{
    public function __construct(
        private readonly TaskRepository $taskRepository,
    ) {
    }

    public function update(Task $task, array $data): Task
    {
        return $this->taskRepository->update($task, $data);
    }
}
