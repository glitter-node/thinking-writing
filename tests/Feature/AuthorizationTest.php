<?php

namespace Tests\Feature;

use App\Domain\Space\Models\Space;
use App\Domain\Stream\Models\Stream;
use App\Domain\Thought\Models\Thought;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AuthorizationTest extends TestCase
{
    use RefreshDatabase;

    public function test_user_cannot_view_or_mutate_another_users_space(): void
    {
        $owner = User::factory()->create();
        $intruder = User::factory()->create();
        $space = Space::factory()->for($owner)->create();

        $this->actingAs($intruder)
            ->get(route('spaces.show', $space))
            ->assertForbidden();

        $this->actingAs($intruder)
            ->patch(route('spaces.update', $space), [
                'title' => 'Nope',
                'description' => 'Blocked',
            ])
            ->assertForbidden();

        $this->actingAs($intruder)
            ->delete(route('spaces.destroy', $space))
            ->assertForbidden();
    }

    public function test_user_cannot_move_another_users_thought(): void
    {
        $owner = User::factory()->create();
        $intruder = User::factory()->create();
        $space = Space::factory()->for($owner)->create();
        $stream = Stream::factory()->for($space)->create();
        $thought = Thought::factory()->for($owner)->for($stream)->create();

        $this->actingAs($intruder)
            ->patchJson(route('thoughts.move', $thought), [
                'stream_id' => $stream->id,
                'position' => 1,
            ])
            ->assertForbidden();
    }
}
