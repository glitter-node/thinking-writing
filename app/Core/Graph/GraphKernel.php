<?php

namespace App\Core\Graph;

use App\Domain\Space\Models\Space;
use App\Domain\Thought\Models\Thought;
use App\Domain\Thought\Services\ThoughtGraphService;
use App\Domain\Thought\Services\ThoughtGraphTraversalService;

class GraphKernel
{
    public function __construct(
        private readonly ThoughtGraphService $thoughtGraphService,
        private readonly ThoughtGraphTraversalService $thoughtGraphTraversalService,
    ) {
    }

    public function getSpaceGraph(Space $space): array
    {
        return $this->thoughtGraphService->getSpaceGraph($space);
    }

    public function getConnectedThoughts(Thought $thought, int $depth = 2): array
    {
        return $this->thoughtGraphTraversalService->getConnectedThoughts($thought, $depth);
    }
}
