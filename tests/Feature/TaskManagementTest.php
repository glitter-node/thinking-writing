<?php

namespace Tests\Feature;

use App\Domain\Project\Models\Project;
use App\Domain\Space\Models\Space;
use App\Domain\Stream\Models\Stream;
use App\Domain\Thought\Models\Thought;
use App\Domain\Task\Models\Task;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class TaskManagementTest extends TestCase
{
    use RefreshDatabase;

    public function test_tasks_can_be_generated_from_project_and_completed(): void
    {
        $user = User::factory()->create();
        $space = Space::factory()->for($user)->create();
        $stream = Stream::factory()->for($space)->create(['position' => 1]);
        $thought = Thought::factory()->for($user)->for($stream)->create([
            'stage' => 'project',
        ]);
        $project = Project::query()->create([
            'thought_id' => $thought->id,
            'title' => 'Execution project',
            'description' => 'Execution project',
            'status' => 'active',
            'created_at' => now(),
        ]);

        $this->actingAs($user)
            ->post(route('projects.tasks.store', $project))
            ->assertRedirect(route('projects.index'));

        $this->assertDatabaseCount('tasks', 3);

        Task::query()->get()->each(function (Task $task) use ($user): void {
            $this->actingAs($user)
                ->patch(route('tasks.complete', $task))
                ->assertRedirect(route('projects.index'));
        });

        $this->assertDatabaseHas('projects', [
            'id' => $project->id,
            'status' => 'complete',
        ]);
        $this->assertDatabaseHas('thoughts', [
            'id' => $thought->id,
            'stage' => 'outcome',
        ]);
    }
}
