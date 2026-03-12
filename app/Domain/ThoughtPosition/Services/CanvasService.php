<?php

namespace App\Domain\ThoughtPosition\Services;

use App\Domain\Project\Repositories\ProjectRepository;
use App\Domain\Space\Models\Space;
use App\Domain\Thought\Repositories\ThoughtRepository;
use App\Domain\Thought\Services\ThoughtGraphService;
use App\Domain\ThoughtPosition\Repositories\ThoughtPositionRepository;
use Illuminate\Support\Collection;

class CanvasService
{
    public function __construct(
        private readonly ThoughtRepository $thoughtRepository,
        private readonly ThoughtPositionRepository $thoughtPositionRepository,
        private readonly ThoughtGraphService $thoughtGraphService,
        private readonly ProjectRepository $projectRepository,
    ) {
    }

    public function getCanvas(Space $space, array $viewport = []): array
    {
        $graph = $this->thoughtGraphService->getSpaceGraph($space);
        $positions = $this->thoughtPositionRepository->getForSpace($space)->keyBy('thought_id');
        $bounds = $this->normalizeViewport($viewport);

        $thoughtNodes = collect($graph['nodes'])
            ->filter(fn (array $node) => $node['kind'] === 'thought')
            ->values();

        $positionedThoughts = $this->applyPositions($thoughtNodes, $positions);
        $visibleThoughtIds = $this->visibleThoughtIds($positionedThoughts, $bounds);

        $projectIds = $this->projectRepository
            ->withinSpace($space->id)
            ->pluck('id')
            ->map(fn ($id) => 'project-'.$id)
            ->all();

        $visibleNodeIds = collect($visibleThoughtIds)
            ->map(fn (int $id) => 'thought-'.$id)
            ->merge($projectIds)
            ->values();

        return [
            'viewport' => $bounds,
            'nodes' => collect($graph['nodes'])
                ->filter(function (array $node) use ($visibleNodeIds): bool {
                    return in_array($node['id'], $visibleNodeIds->all(), true)
                        || ($node['kind'] === 'task' && collect($visibleNodeIds)->contains(fn (string $id) => str_starts_with($id, 'project-')));
                })
                ->map(function (array $node) use ($positionedThoughts): array {
                    if ($node['kind'] !== 'thought') {
                        return $node;
                    }

                    $position = $positionedThoughts->firstWhere('resource_id', $node['resource_id']);

                    return array_merge($node, [
                        'x' => $position['x'],
                        'y' => $position['y'],
                    ]);
                })
                ->values()
                ->all(),
            'edges' => collect($graph['edges'])
                ->filter(fn (array $edge) => $visibleNodeIds->contains($edge['source']) || $visibleNodeIds->contains($edge['target']))
                ->values()
                ->all(),
            'clusters' => $this->buildClusterSuggestions($positionedThoughts),
        ];
    }

    private function applyPositions(Collection $thoughtNodes, Collection $positions): Collection
    {
        return $thoughtNodes->values()->map(function (array $node, int $index) use ($positions): array {
            $position = $positions->get($node['resource_id']);

            return array_merge($node, [
                'x' => $position?->x ?? (120 + (($index % 4) * 280)),
                'y' => $position?->y ?? (120 + (int) floor($index / 4) * 220),
            ]);
        });
    }

    private function normalizeViewport(array $viewport): array
    {
        return [
            'x' => (int) ($viewport['x'] ?? 0),
            'y' => (int) ($viewport['y'] ?? 0),
            'width' => max(600, (int) ($viewport['width'] ?? 1600)),
            'height' => max(400, (int) ($viewport['height'] ?? 900)),
        ];
    }

    private function visibleThoughtIds(Collection $thoughts, array $bounds): array
    {
        return $thoughts
            ->filter(function (array $thought) use ($bounds): bool {
                return $thought['x'] >= $bounds['x'] - 240
                    && $thought['x'] <= $bounds['x'] + $bounds['width'] + 240
                    && $thought['y'] >= $bounds['y'] - 180
                    && $thought['y'] <= $bounds['y'] + $bounds['height'] + 180;
            })
            ->pluck('resource_id')
            ->all();
    }

    private function buildClusterSuggestions(Collection $thoughts): array
    {
        return $thoughts
            ->groupBy(fn (array $thought) => $thought['stream_title'])
            ->map(fn (Collection $items, string $title): array => [
                'label' => $title,
                'thought_ids' => $items->pluck('resource_id')->all(),
            ])
            ->values()
            ->all();
    }
}
