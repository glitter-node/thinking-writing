<?php

namespace Tests\Feature;

use App\Domain\Space\Models\Space;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class PromptDisplayTest extends TestCase
{
    use RefreshDatabase;

    public function test_board_displays_guided_prompt_templates_and_streak_modules(): void
    {
        $user = User::factory()->create();
        $space = Space::factory()->for($user)->create();

        $this->actingAs($user)
            ->get(route('spaces.show', $space))
            ->assertOk()
            ->assertSee('Guided prompt')
            ->assertSee('Thought templates')
            ->assertSee('Thinking streak');
    }
}
