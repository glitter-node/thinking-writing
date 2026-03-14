<?php

namespace Tests\Feature;

use App\Domain\Space\Models\Space;
use App\Domain\Stream\Models\Stream;
use App\Domain\Thought\Models\Thought;
use App\Models\User;
use Carbon\CarbonImmutable;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class RediscoverEndpointTest extends TestCase
{
    use RefreshDatabase;

    public function test_rediscover_endpoint_returns_today_historic_and_random_entries(): void
    {
        CarbonImmutable::setTestNow('2026-03-13 09:00:00');

        $user = User::factory()->create();
        $space = Space::factory()->for($user)->create();
        $stream = Stream::factory()->for($space)->create(['position' => 1, 'title' => 'Inbox']);

        Thought::factory()->for($user)->for($stream)->create([
            'content' => 'Today thought',
            'created_at' => CarbonImmutable::now(),
            'updated_at' => CarbonImmutable::now(),
        ]);
        Thought::factory()->for($user)->for($stream)->create([
            'content' => 'Seven days back',
            'created_at' => CarbonImmutable::now()->subDays(7),
            'updated_at' => CarbonImmutable::now()->subDays(7),
        ]);
        Thought::factory()->for($user)->for($stream)->create([
            'content' => 'Thirty days back',
            'created_at' => CarbonImmutable::now()->subDays(30),
            'updated_at' => CarbonImmutable::now()->subDays(30),
        ]);

        $response = $this->actingAs($user)
            ->getJson(route('spaces.rediscover', $space));

        $response->assertOk()->assertJsonCount(4, 'entries');
        $this->assertSame('Today', $response->json('entries.0.label'));
        $this->assertSame('Today thought', $response->json('entries.0.thought.content'));
        $this->assertSame('Seven days back', $response->json('entries.1.thought.content'));
        $this->assertSame('Thirty days back', $response->json('entries.2.thought.content'));
        $this->assertNotNull($response->json('entries.3.thought'));

        CarbonImmutable::setTestNow();
    }
}
