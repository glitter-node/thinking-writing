<?php

namespace Tests\Feature;

use App\Domain\Space\Models\Space;
use App\Domain\Stream\Models\Stream;
use App\Domain\Thought\Models\Thought;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ThoughtVersionTest extends TestCase
{
    use RefreshDatabase;

    public function test_editing_a_thought_creates_new_versions_and_renders_history(): void
    {
        $user = User::factory()->create();
        $space = Space::factory()->for($user)->create();
        $stream = Stream::factory()->for($space)->create(['position' => 1]);

        $this->actingAs($user)
            ->post(route('streams.thoughts.store', $stream), [
                'content' => 'Original framing',
                'priority' => 'medium',
                'tags' => 'history',
            ])
            ->assertRedirect(route('spaces.show', $space));

        $thought = Thought::query()->firstOrFail();

        $this->actingAs($user)
            ->patch(route('thoughts.update', $thought), [
                'content' => 'Refined framing',
                'priority' => 'high',
                'tags' => 'history, refined',
            ])
            ->assertRedirect(route('spaces.show', $space));

        $this->assertDatabaseHas('thought_versions', [
            'thought_id' => $thought->id,
            'version' => 1,
            'content' => 'Original framing',
        ]);

        $this->assertDatabaseHas('thought_versions', [
            'thought_id' => $thought->id,
            'version' => 2,
            'content' => 'Refined framing',
        ]);

        $this->actingAs($user)
            ->get(route('spaces.show', $space))
            ->assertOk()
            ->assertSee('Version history')
            ->assertSee('Refined framing')
            ->assertSee('Original framing');
    }
}
