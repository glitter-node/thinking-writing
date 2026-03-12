<?php

namespace App\Core\Index;

use App\Domain\Thought\Services\ThoughtGraphIndexService;

class IndexKernel
{
    public function __construct(
        private readonly ThoughtGraphIndexService $thoughtGraphIndexService,
    ) {
    }

    public function updateThoughtGraph(int $thoughtId): void
    {
        $this->thoughtGraphIndexService->updateGraphIndex($thoughtId);
    }

    public function rebuildThoughtGraph(): void
    {
        $this->thoughtGraphIndexService->rebuildGraphIndex();
    }
}
