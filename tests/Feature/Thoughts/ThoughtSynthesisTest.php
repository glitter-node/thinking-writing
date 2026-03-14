<?php

namespace Tests\Feature;

use App\Domain\Space\Models\Space;
use App\Domain\Stream\Models\Stream;
use App\Domain\Thought\Models\Thought;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ThoughtSynthesisTest extends TestCase
{
    use RefreshDatabase;

    public function test_user_can_synthesize_multiple_thoughts_into_a_new_thought(): void
    {
        $user = User::factory()->create();
        $space = Space::factory()->for($user)->create();
        $stream = Stream::factory()->for($space)->create(['position' => 1]);
        $thoughtA = Thought::factory()->for($user)->for($stream)->create([
            'content' => 'Idea A',
            'position' => 1,
            'tags' => ['systems'],
        ]);
        $thoughtB = Thought::factory()->for($user)->for($stream)->create([
            'content' => 'Idea B',
            'position' => 2,
            'tags' => ['systems', 'review'],
        ]);

        $response = $this->actingAs($user)
            ->postJson(route('spaces.syntheses.store', $space), [
                'content' => 'Synthesized idea',
                'thought_ids' => [$thoughtA->id, $thoughtB->id],
            ]);

        $response
            ->assertCreated()
            ->assertJsonPath('thought.stream_id', $stream->id);

        $synthesizedThought = Thought::query()->where('content', 'Synthesized idea')->firstOrFail();

        $this->assertDatabaseHas('thought_syntheses', [
            'user_id' => $user->id,
            'synthesized_thought_id' => $synthesizedThought->id,
        ]);
        $this->assertDatabaseHas('thought_synthesis_items', [
            'thought_id' => $thoughtA->id,
        ]);
        $this->assertDatabaseHas('thought_synthesis_items', [
            'thought_id' => $thoughtB->id,
        ]);
        $this->assertDatabaseHas('thought_versions', [
            'thought_id' => $synthesizedThought->id,
            'version' => 1,
            'content' => 'Synthesized idea',
        ]);
        $this->assertDatabaseHas('thought_events', [
            'thought_id' => $synthesizedThought->id,
            'event_type' => 'ThoughtCreated',
        ]);
        $this->assertDatabaseHas('thought_events', [
            'thought_id' => $synthesizedThought->id,
            'event_type' => 'ThoughtSynthesized',
        ]);
        $this->assertDatabaseHas('thought_graph_index', [
            'thought_id' => $synthesizedThought->id,
            'linked_thought_id' => $thoughtA->id,
            'link_type' => 'synthesis',
            'depth' => 1,
        ]);
        $this->assertDatabaseHas('thought_graph_index', [
            'thought_id' => $thoughtA->id,
            'linked_thought_id' => $synthesizedThought->id,
            'link_type' => 'synthesis',
            'depth' => 1,
        ]);
        $this->assertDatabaseHas('thought_tag_index', [
            'thought_id' => $synthesizedThought->id,
            'tag' => 'systems',
        ]);
        $this->assertDatabaseHas('thought_tag_index', [
            'thought_id' => $synthesizedThought->id,
            'tag' => 'review',
        ]);

        $this->actingAs($user)
            ->get(route('spaces.show', $space))
            ->assertOk()
            ->assertSee('Synthesized From')
            ->assertSee('Idea A')
            ->assertSee('Idea B');
    }
}
