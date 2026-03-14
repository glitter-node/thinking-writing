<?php

namespace Tests\Feature\Exports;

use App\Domain\Space\Models\Space;
use App\Domain\Stream\Models\Stream;
use App\Domain\Thought\Models\Thought;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ThoughtExportTest extends TestCase
{
    use RefreshDatabase;

    public function test_user_can_export_thoughts_as_json(): void
    {
        $user = User::factory()->create();
        $space = Space::factory()->for($user)->create(['title' => 'Export Space']);
        $stream = Stream::factory()->for($space)->create(['title' => 'Inbox', 'position' => 1]);
        Thought::factory()->for($user)->for($stream)->create([
            'content' => 'Exported thought',
            'position' => 1,
        ]);

        $this->actingAs($user)
            ->get(route('thoughts.export'))
            ->assertOk()
            ->assertHeader('content-type', 'application/json')
            ->assertHeader('content-disposition', 'attachment; filename="thinkwrite-thoughts.json"')
            ->assertSee('Exported thought')
            ->assertSee('Export Space');
    }
}
