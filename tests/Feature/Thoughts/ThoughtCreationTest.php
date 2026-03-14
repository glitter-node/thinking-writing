<?php

namespace Tests\Feature;

use App\Domain\Space\Models\Space;
use App\Domain\Stream\Models\Stream;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ThoughtCreationTest extends TestCase
{
    use RefreshDatabase;

    public function test_user_can_create_a_thought_for_a_stream(): void
    {
        $user = User::factory()->create();
        $space = Space::factory()->for($user)->create();
        $stream = Stream::factory()->for($space)->create(['position' => 1]);

        $this->actingAs($user)
            ->post(route('streams.thoughts.store', $stream), [
                'content' => 'Ship a voice memo capture flow',
                'priority' => 'high',
                'tags' => 'product, mobile',
            ])
            ->assertRedirect(route('spaces.show', $space));

        $this->assertDatabaseHas('thoughts', [
            'stream_id' => $stream->id,
            'user_id' => $user->id,
            'priority' => 'high',
            'position' => 1,
        ]);
    }
}
