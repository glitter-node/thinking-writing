<?php

namespace Tests\Feature;

use App\Domain\Space\Models\Space;
use App\Domain\Stream\Models\Stream;
use App\Domain\Thought\Models\Thought;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ThoughtEvolutionTest extends TestCase
{
    use RefreshDatabase;

    public function test_user_can_create_an_evolved_thought_and_view_the_thread(): void
    {
        $user = User::factory()->create();
        $space = Space::factory()->for($user)->create();
        $stream = Stream::factory()->for($space)->create(['position' => 1]);
        $thought = Thought::factory()->for($user)->for($stream)->create([
            'content' => 'Base idea',
            'position' => 1,
        ]);

        $response = $this->actingAs($user)
            ->postJson(route('thoughts.evolve', $thought), [
                'content' => 'Refined idea',
                'priority' => 'high',
                'tags' => 'strategy, build',
            ]);

        $response
            ->assertCreated()
            ->assertJsonPath('thought.parent_id', $thought->id);

        $evolvedThought = Thought::query()->where('parent_id', $thought->id)->firstOrFail();
        $this->assertSame('Refined idea', $evolvedThought->content);
        $this->assertSame(['strategy', 'build'], $evolvedThought->tags);

        $threadResponse = $this->actingAs($user)
            ->getJson(route('thoughts.thread', $evolvedThought));

        $threadResponse
            ->assertOk()
            ->assertJsonCount(2, 'thread');
    }
}
