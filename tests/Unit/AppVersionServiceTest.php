<?php

namespace Tests\Unit;

use App\Services\AppVersionService;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Process;
use RuntimeException;
use Tests\TestCase;

class AppVersionServiceTest extends TestCase
{
    public function test_it_falls_back_to_configured_version_when_git_process_fails(): void
    {
        Cache::flush();
        config(['version.version' => 'dev']);

        Process::shouldReceive('path')->twice()->andReturnSelf();
        Process::shouldReceive('run')->twice()->andThrow(new RuntimeException('git unavailable'));

        $service = app(AppVersionService::class);

        $this->assertSame('dev', $service->getVersion());
    }
}
