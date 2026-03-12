<?php

namespace Tests\Feature;

use App\Services\AppVersionService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Mockery;
use Tests\TestCase;

class VersionCommandTest extends TestCase
{
    use RefreshDatabase;

    public function test_version_command_outputs_current_application_version(): void
    {
        $service = Mockery::mock(AppVersionService::class);
        $service->shouldReceive('getVersion')->once()->andReturn('v0.1.0');
        $this->app->instance(AppVersionService::class, $service);

        $this->artisan('app:version')
            ->expectsOutput('Application Version: v0.1.0')
            ->assertSuccessful();
    }
}
