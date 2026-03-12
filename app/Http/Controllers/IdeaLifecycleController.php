<?php

namespace App\Http\Controllers;

use App\Core\Thought\ThoughtKernel;
use App\Domain\Project\Models\Project;
use App\Domain\Task\Models\Task;
use App\Domain\Thought\Models\Thought;
use Illuminate\Http\RedirectResponse;

class IdeaLifecycleController extends Controller
{
    public function __construct(
        private readonly ThoughtKernel $thoughtKernel,
    ) {
    }

    public function promote(Thought $thought): RedirectResponse
    {
        $this->authorize('update', $thought);
        $this->thoughtKernel->promoteToConcept($thought);

        return redirect()
            ->route('spaces.show', $thought->stream->space)
            ->with('status', 'Thought promoted to concept.');
    }

    public function createProject(Thought $thought): RedirectResponse
    {
        $this->authorize('update', $thought);
        $this->thoughtKernel->createProject($thought);

        return redirect()
            ->route('projects.index')
            ->with('status', 'Project created from thought.');
    }

    public function createTasks(Project $project): RedirectResponse
    {
        $this->authorize('update', $project->thought);
        $this->thoughtKernel->createTasks($project);

        return redirect()
            ->route('projects.index')
            ->with('status', 'Tasks created from project.');
    }

    public function completeTask(Task $task): RedirectResponse
    {
        $this->authorize('update', $task->project->thought);
        $this->thoughtKernel->completeTask($task);

        return redirect()
            ->route('projects.index')
            ->with('status', 'Task completed.');
    }
}
