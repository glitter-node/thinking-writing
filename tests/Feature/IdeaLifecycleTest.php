<?php

namespace Tests\Feature;

use App\Domain\Space\Models\Space;
use App\Domain\Stream\Models\Stream;
use App\Domain\Thought\Models\Thought;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class IdeaLifecycleTest extends TestCase
{
    use RefreshDatabase;

    public function test_thought_can_be_promoted_to_concept(): void
    {
        $user = User::factory()->create();
        $space = Space::factory()->for($user)->create();
        $stream = Stream::factory()->for($space)->create(['position' => 1]);
        $thought = Thought::factory()->for($user)->for($stream)->create([
            'stage' => 'thought',
        ]);

        $this->actingAs($user)
            ->post(route('thoughts.promote', $thought))
            ->assertRedirect(route('spaces.show', $space));

        $this->assertDatabaseHas('thoughts', [
            'id' => $thought->id,
            'stage' => 'concept',
        ]);
    }
}
