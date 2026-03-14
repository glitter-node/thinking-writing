<?php

namespace Tests\Feature;

use App\Domain\Space\Models\Space;
use App\Domain\Stream\Models\Stream;
use App\Domain\Thought\Models\Thought;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ThoughtSearchTest extends TestCase
{
    use RefreshDatabase;

    public function test_space_search_returns_content_matches_and_highlight_markup(): void
    {
        $user = User::factory()->create();
        $space = Space::factory()->for($user)->create();
        $stream = Stream::factory()->for($space)->create(['position' => 1]);
        Thought::factory()->for($user)->for($stream)->create(['content' => 'Implementation idea for review engine']);
        Thought::factory()->for($user)->for($stream)->create(['content' => 'Different concept']);

        $response = $this->actingAs($user)
            ->getJson(route('spaces.search', $space).'?q=review');

        $response
            ->assertOk()
            ->assertJsonCount(1, 'thoughts');

        $this->assertStringContainsString('<mark', $response->json('thoughts.0.highlighted_content'));
    }
}
