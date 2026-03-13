<?php

namespace App\Services;

use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Process;
use Throwable;

class AppVersionService
{
    public function getVersion(): string
    {
        try {
            return Cache::remember('app.version.current', now()->addHour(), function (): string {
                return $this->resolveVersion();
            });
        } catch (Throwable) {
            return $this->fallbackVersion();
        }
    }

    private function resolveVersion(): string
    {
        $configuredVersion = (string) config('version.version', 'dev');

        if ($configuredVersion !== '' && $configuredVersion !== 'dev') {
            return $configuredVersion;
        }

        $tag = $this->latestGitTag();

        if ($tag !== null) {
            return $tag;
        }

        $commit = $this->commitHash();

        if ($commit !== null) {
            return $commit;
        }

        return $configuredVersion;
    }

    private function latestGitTag(): ?string
    {
        try {
            $result = Process::path(base_path())->run([
                'git',
                'describe',
                '--tags',
                '--abbrev=0',
            ]);
        } catch (Throwable) {
            return null;
        }

        if ($result->failed()) {
            return null;
        }

        $output = trim($result->output());

        return $output !== '' ? $output : null;
    }

    private function commitHash(): ?string
    {
        try {
            $result = Process::path(base_path())->run([
                'git',
                'rev-parse',
                '--short',
                'HEAD',
            ]);
        } catch (Throwable) {
            return null;
        }

        if ($result->failed()) {
            return null;
        }

        $output = trim($result->output());

        return $output !== '' ? $output : null;
    }

    private function fallbackVersion(): string
    {
        return (string) config('version.version', 'dev');
    }
}
