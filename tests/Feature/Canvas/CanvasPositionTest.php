<?php

namespace Tests\Feature;

use App\Domain\Space\Models\Space;
use App\Domain\Stream\Models\Stream;
use App\Domain\Thought\Models\Thought;
use App\Domain\ThoughtPosition\Models\ThoughtPosition;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class CanvasPositionTest extends TestCase
{
    use RefreshDatabase;

    public function test_user_can_store_canvas_position_for_a_thought(): void
    {
        $user = User::factory()->create();
        $space = Space::factory()->for($user)->create();
        $stream = Stream::factory()->for($space)->create(['position' => 1]);
        $thought = Thought::factory()->for($user)->for($stream)->create([
            'content' => 'Spatial thought',
            'position' => 1,
        ]);

        $this->actingAs($user)
            ->postJson(route('thoughts.position.store', $thought), [
                'x' => 420,
                'y' => 315,
            ])
            ->assertOk()
            ->assertJsonPath('position.thought_id', $thought->id)
            ->assertJsonPath('position.x', 420)
            ->assertJsonPath('position.y', 315);

        $this->assertDatabaseHas((new ThoughtPosition())->getTable(), [
            'thought_id' => $thought->id,
            'space_id' => $space->id,
            'x' => 420,
            'y' => 315,
        ]);
    }
}
