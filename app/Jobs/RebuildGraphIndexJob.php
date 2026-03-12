<?php

namespace App\Jobs;

use App\Domain\Thought\Services\ThoughtGraphIndexService;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;

class RebuildGraphIndexJob implements ShouldQueue
{
    use Queueable;

    public function handle(ThoughtGraphIndexService $thoughtGraphIndexService): void
    {
        $thoughtGraphIndexService->rebuildGraphIndex();
    }
}
