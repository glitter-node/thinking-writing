<?php

namespace Tests\Feature;

use App\Domain\Space\Models\Space;
use App\Domain\Stream\Models\Stream;
use App\Domain\ThinkingSession\Models\ThinkingSession;
use App\Domain\ThinkingSession\Services\ThinkingSessionService;
use App\Models\User;
use Carbon\CarbonImmutable;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ThinkingSessionTest extends TestCase
{
    use RefreshDatabase;

    public function test_capturing_thoughts_updates_the_daily_thinking_session(): void
    {
        $user = User::factory()->create();
        $space = Space::factory()->for($user)->create();
        $stream = Stream::factory()->for($space)->create(['position' => 1]);

        $this->actingAs($user)
            ->post(route('streams.thoughts.store', $stream), [
                'content' => 'A first guided thought',
                'priority' => 'medium',
                'tags' => '',
            ])
            ->assertRedirect(route('spaces.show', $space));

        $this->assertDatabaseHas('thinking_sessions', [
            'user_id' => $user->id,
            'thought_count' => 1,
        ]);
    }

    public function test_streak_counts_consecutive_days_with_sessions(): void
    {
        $user = User::factory()->create();

        ThinkingSession::query()->create([
            'user_id' => $user->id,
            'started_at' => CarbonImmutable::now()->startOfDay(),
            'thought_count' => 1,
        ]);
        ThinkingSession::query()->create([
            'user_id' => $user->id,
            'started_at' => CarbonImmutable::now()->subDay()->startOfDay(),
            'thought_count' => 2,
        ]);
        ThinkingSession::query()->create([
            'user_id' => $user->id,
            'started_at' => CarbonImmutable::now()->subDays(2)->startOfDay(),
            'thought_count' => 1,
        ]);

        /** @var ThinkingSessionService $service */
        $service = app(ThinkingSessionService::class);
        $streak = $service->getStreak($user->id);

        $this->assertSame(3, $streak['days']);
    }
}
