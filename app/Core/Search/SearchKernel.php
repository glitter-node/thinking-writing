<?php

namespace App\Core\Search;

use App\Domain\Space\Models\Space;
use App\Domain\Space\Services\SpaceService;

class SearchKernel
{
    public function __construct(
        private readonly SpaceService $spaceService,
    ) {
    }

    public function searchThoughts(Space $space, string $query): array
    {
        return $this->spaceService->searchThoughts($space, $query);
    }
}
