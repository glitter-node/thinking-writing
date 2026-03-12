<?php

namespace Tests\Feature;

use App\Domain\Space\Models\Space;
use App\Domain\Stream\Models\Stream;
use App\Domain\Thought\Models\Thought;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class QuickThoughtCreationTest extends TestCase
{
    use RefreshDatabase;

    public function test_user_can_quick_capture_into_the_first_stream_and_it_is_prepended(): void
    {
        $user = User::factory()->create();
        $space = Space::factory()->for($user)->create();
        $firstStream = Stream::factory()->for($space)->create(['position' => 1, 'title' => 'Inbox']);
        Stream::factory()->for($space)->create(['position' => 2, 'title' => 'Later']);
        $existingThought = Thought::factory()->for($user)->for($firstStream)->create([
            'position' => 1,
            'content' => 'Older thought',
        ]);

        $response = $this->actingAs($user)
            ->postJson(route('spaces.quick-thoughts.store', $space), [
                'content' => 'Capture this instantly',
            ]);

        $response
            ->assertCreated()
            ->assertJsonPath('thought.stream_id', $firstStream->id)
            ->assertJsonPath('thought.content', 'Capture this instantly');

        $newThought = Thought::query()->where('content', 'Capture this instantly')->firstOrFail();
        $existingThought->refresh();

        $this->assertSame(1, $newThought->position);
        $this->assertSame(2, $existingThought->position);
    }
}
