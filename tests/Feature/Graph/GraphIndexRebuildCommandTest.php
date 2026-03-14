<?php

namespace Tests\Feature;

use App\Domain\Thought\Services\ThoughtGraphIndexService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Mockery;
use Tests\TestCase;

class GraphIndexRebuildCommandTest extends TestCase
{
    use RefreshDatabase;

    public function test_graph_index_rebuild_command_invokes_rebuild_service(): void
    {
        $service = Mockery::mock(ThoughtGraphIndexService::class);
        $service->shouldReceive('rebuildGraphIndex')->once();
        $this->app->instance(ThoughtGraphIndexService::class, $service);

        $this->artisan('graph:index:rebuild')
            ->expectsOutput('Thought graph index rebuilt.')
            ->assertSuccessful();
    }
}
