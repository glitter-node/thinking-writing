<?php

namespace Tests\Feature;

use App\Domain\Space\Models\Space;
use App\Domain\Stream\Models\Stream;
use App\Domain\Thought\Models\Thought;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ThoughtEventLogTest extends TestCase
{
    use RefreshDatabase;

    public function test_thought_actions_are_recorded_in_the_event_log(): void
    {
        $user = User::factory()->create();
        $space = Space::factory()->for($user)->create();
        $stream = Stream::factory()->for($space)->create(['position' => 1]);

        $this->actingAs($user)
            ->post(route('streams.thoughts.store', $stream), [
                'content' => 'Connect this to [[Linked concept]]',
                'priority' => 'medium',
                'tags' => 'events',
            ])
            ->assertRedirect(route('spaces.show', $space));

        $thought = Thought::query()
            ->where('user_id', $user->id)
            ->where('content', 'Connect this to [[Linked concept]]')
            ->firstOrFail();

        $this->actingAs($user)
            ->post(route('thoughts.promote', $thought))
            ->assertRedirect(route('spaces.show', $space));

        $this->assertDatabaseHas('thought_events', [
            'thought_id' => $thought->id,
            'event_type' => 'ThoughtCreated',
        ]);

        $this->assertDatabaseHas('thought_events', [
            'thought_id' => $thought->id,
            'event_type' => 'ThoughtLinked',
        ]);

        $this->assertDatabaseHas('thought_events', [
            'thought_id' => $thought->id,
            'event_type' => 'ThoughtPromoted',
        ]);
    }
}
