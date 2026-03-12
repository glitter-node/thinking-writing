<?php

namespace Tests\Feature;

use App\Domain\Space\Models\Space;
use App\Domain\Stream\Models\Stream;
use App\Domain\Thought\Models\Thought;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ThoughtMovementTest extends TestCase
{
    use RefreshDatabase;

    public function test_user_can_move_a_thought_between_streams_and_reorder_it(): void
    {
        $user = User::factory()->create();
        $space = Space::factory()->for($user)->create();
        $streamA = Stream::factory()->for($space)->create(['position' => 1]);
        $streamB = Stream::factory()->for($space)->create(['position' => 2]);

        $thoughtA = Thought::factory()->for($user)->for($streamA)->create(['position' => 1]);
        $thoughtB = Thought::factory()->for($user)->for($streamB)->create(['position' => 1]);

        $this->actingAs($user)
            ->patchJson(route('thoughts.move', $thoughtA), [
                'stream_id' => $streamB->id,
                'position' => 1,
            ])
            ->assertOk()
            ->assertJson([
                'thought_id' => $thoughtA->id,
                'stream_id' => $streamB->id,
                'position' => 1,
            ]);

        $thoughtA->refresh();
        $thoughtB->refresh();

        $this->assertSame($streamB->id, $thoughtA->stream_id);
        $this->assertSame(1, $thoughtA->position);
        $this->assertSame(2, $thoughtB->position);
    }
}
