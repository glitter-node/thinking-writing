<?php

namespace Tests\Feature;

use App\Domain\Space\Models\Space;
use App\Domain\Stream\Models\Stream;
use App\Domain\Thought\Models\Thought;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ProjectCreationTest extends TestCase
{
    use RefreshDatabase;

    public function test_project_can_be_created_from_thought_and_visible_on_projects_board(): void
    {
        $user = User::factory()->create();
        $space = Space::factory()->for($user)->create();
        $stream = Stream::factory()->for($space)->create(['position' => 1]);
        $thought = Thought::factory()->for($user)->for($stream)->create([
            'content' => 'Build a launch checklist',
            'stage' => 'concept',
        ]);

        $this->actingAs($user)
            ->post(route('thoughts.projects.store', $thought))
            ->assertRedirect(route('projects.index'));

        $this->assertDatabaseHas('projects', [
            'thought_id' => $thought->id,
            'status' => 'active',
        ]);

        $this->actingAs($user)
            ->get(route('projects.index'))
            ->assertOk()
            ->assertSee('Project board')
            ->assertSee('Build a launch checklist');
    }
}
