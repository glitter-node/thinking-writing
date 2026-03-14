<?php

namespace Tests\Feature\Endpoints\Concerns;

use App\Domain\Project\Models\Project;
use App\Domain\Space\Models\Space;
use App\Domain\Stream\Models\Stream;
use App\Domain\Task\Models\Task;
use App\Domain\Thought\Models\Thought;
use App\Models\User;

trait BuildsWorkspaceFixtures
{
    protected function createSpaceFor(User $user, array $attributes = []): Space
    {
        return Space::factory()->create($attributes + [
            'user_id' => $user->id,
        ]);
    }

    protected function createStreamFor(Space $space, array $attributes = []): Stream
    {
        return Stream::factory()->create($attributes + [
            'space_id' => $space->id,
        ]);
    }

    protected function createThoughtFor(User $user, ?Stream $stream = null, array $attributes = []): Thought
    {
        $stream ??= $this->createStreamFor($this->createSpaceFor($user));

        return Thought::factory()->create($attributes + [
            'stream_id' => $stream->id,
            'user_id' => $user->id,
        ]);
    }

    protected function createProjectForThought(Thought $thought, array $attributes = []): Project
    {
        return Project::query()->create($attributes + [
            'thought_id' => $thought->id,
            'title' => 'Project for thought '.$thought->id,
            'description' => 'Generated for endpoint coverage.',
            'status' => 'active',
            'created_at' => now(),
        ]);
    }

    protected function createTaskForProject(Project $project, array $attributes = []): Task
    {
        return Task::query()->create($attributes + [
            'project_id' => $project->id,
            'title' => 'Task for project '.$project->id,
            'status' => 'todo',
            'priority' => 'medium',
            'created_at' => now(),
        ]);
    }
}
