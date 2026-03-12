<?php

namespace Tests\Feature;

use App\Domain\Space\Models\Space;
use App\Domain\Stream\Models\Stream;
use App\Domain\ThinkingPrompt\Services\ThinkingPromptService;
use App\Domain\Thought\Models\Thought;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ThinkingPromptTest extends TestCase
{
    use RefreshDatabase;

    public function test_prompt_service_returns_daily_prompt_and_systems_suggestion_from_recent_thoughts(): void
    {
        $user = User::factory()->create();
        $space = Space::factory()->for($user)->create();
        $stream = Stream::factory()->for($space)->create(['position' => 1]);

        Thought::factory()->for($user)->for($stream)->create([
            'content' => 'Distributed systems are forcing a new scalability tradeoff',
            'position' => 1,
        ]);

        /** @var ThinkingPromptService $service */
        $service = app(ThinkingPromptService::class);

        $dailyPrompt = $service->getDailyPrompt();
        $suggestedPrompt = $service->getSmartSuggestionForUser($user->id);

        $this->assertNotNull($dailyPrompt);
        $this->assertNotNull($suggestedPrompt);
        $this->assertSame('systems', $suggestedPrompt['category']);
    }
}
