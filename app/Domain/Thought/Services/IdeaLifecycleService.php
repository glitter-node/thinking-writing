<?php

namespace App\Domain\Thought\Services;

use App\Domain\Project\Models\Project;
use App\Domain\Project\Repositories\ProjectRepository;
use App\Domain\Task\Models\Task;
use App\Domain\Task\Repositories\TaskRepository;
use App\Domain\Thought\Models\Thought;
use App\Domain\Thought\Repositories\ThoughtRepository;
use App\Domain\ThoughtEvent\Services\ThoughtEventService;
use Illuminate\Support\Facades\DB;

class IdeaLifecycleService
{
    public function __construct(
        private readonly ThoughtRepository $thoughtRepository,
        private readonly ProjectRepository $projectRepository,
        private readonly TaskRepository $taskRepository,
        private readonly ThoughtEventService $thoughtEventService,
    ) {
    }

    public function promoteThoughtToConcept(Thought $thought): Thought
    {
        return $this->updateStage($thought, 'concept');
    }

    public function createProjectFromThought(Thought $thought): Project
    {
        return DB::transaction(function () use ($thought): Project {
            $thought = $this->updateStage($thought, 'project');

            if ($thought->project) {
                return $thought->project()->with('tasks')->firstOrFail();
            }

            return $this->projectRepository->createForThought($thought, [
                'title' => $this->titleFromThought($thought->content),
                'description' => $thought->content,
                'status' => 'active',
                'created_at' => now(),
            ])->fresh(['thought', 'tasks']);
        });
    }

    public function createTasksFromProject(Project $project): Project
    {
        return DB::transaction(function () use ($project): Project {
            if ($project->tasks()->exists()) {
                return $project->fresh(['thought', 'tasks']);
            }

            $this->taskRepository->createMany($project, [
                [
                    'title' => 'Define scope',
                    'status' => 'todo',
                    'priority' => 'high',
                    'created_at' => now(),
                ],
                [
                    'title' => 'Build first version',
                    'status' => 'in_progress',
                    'priority' => 'high',
                    'created_at' => now(),
                ],
                [
                    'title' => 'Review outcome',
                    'status' => 'todo',
                    'priority' => 'medium',
                    'created_at' => now(),
                ],
            ]);

            $this->updateStage($project->thought, 'task');

            return $project->fresh(['thought', 'tasks']);
        });
    }

    public function completeTask(Task $task): Task
    {
        return DB::transaction(function () use ($task): Task {
            $task->update(['status' => 'done']);
            $project = $task->project()->with('tasks', 'thought')->firstOrFail();

            if ($project->tasks->every(fn (Task $item) => $item->status === 'done')) {
                $project->update(['status' => 'complete']);
                $this->updateStage($project->thought, 'outcome');
            }

            return $task->fresh(['project.thought']);
        });
    }

    private function updateStage(Thought $thought, string $stage): Thought
    {
        $fromStage = $thought->stage ?? 'thought';

        $updatedThought = $this->thoughtRepository->update($thought, [
            'content' => $thought->content,
            'priority' => $thought->priority,
            'tags' => $thought->tags ?? [],
            'stage' => $stage,
        ]);

        $this->thoughtEventService->recordEvent($updatedThought, 'ThoughtPromoted', [
            'from' => $fromStage,
            'to' => $stage,
        ]);

        return $updatedThought;
    }

    private function titleFromThought(string $content): string
    {
        $singleLine = trim((string) preg_replace('/\s+/', ' ', $content));

        return mb_strimwidth($singleLine, 0, 60, '...');
    }
}
