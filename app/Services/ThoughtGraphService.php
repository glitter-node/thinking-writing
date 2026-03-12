<?php

namespace App\Services;

use App\Domain\Space\Models\Space;
use App\Domain\Thought\Models\Thought;
use App\Domain\ThoughtGraphIndex\Repositories\ThoughtGraphIndexRepository;
use App\Domain\Thought\Services\ThoughtGraphIndexService;
use App\Domain\Thought\Services\ThoughtGraphService as DomainThoughtGraphService;

class ThoughtGraphService
{
    public function __construct(
        private readonly DomainThoughtGraphService $domainThoughtGraphService,
        private readonly ThoughtGraphIndexService $thoughtGraphIndexService,
        private readonly ThoughtGraphIndexRepository $thoughtGraphIndexRepository,
    ) {
    }

    public function getGraphData(Space $space, int $limit = 100): array
    {
        $graph = $this->domainThoughtGraphService->getSpaceGraph($space);

        $thoughtNodes = collect($graph['nodes'])
            ->filter(fn (array $node): bool => $node['kind'] === 'thought')
            ->take($limit)
            ->values();

        $nodeIds = $thoughtNodes->pluck('id')->all();
        $degreeMap = $this->buildDegreeMap(
            collect($graph['edges'])
                ->filter(fn (array $edge): bool => in_array($edge['source'], $nodeIds, true) && in_array($edge['target'], $nodeIds, true))
                ->values()
        );

        return [
            'nodes' => $thoughtNodes->map(fn (array $node): array => [
                'data' => [
                    'id' => (string) $node['resource_id'],
                    'label' => $node['label'],
                    'content' => $node['content'],
                    'href' => $node['href'],
                    'degree' => $degreeMap[$node['id']] ?? 0,
                ],
            ])->all(),
            'edges' => collect($graph['edges'])
                ->filter(fn (array $edge): bool => in_array($edge['source'], $nodeIds, true) && in_array($edge['target'], $nodeIds, true))
                ->values()
                ->map(fn (array $edge): array => [
                    'data' => [
                        'id' => (string) $edge['id'],
                        'source' => (string) str_replace('thought-', '', $edge['source']),
                        'target' => (string) str_replace('thought-', '', $edge['target']),
                        'type' => $edge['type'],
                    ],
                ])->all(),
        ];
    }

    public function getNeighbors(Thought $thought): array
    {
        $graph = $this->domainThoughtGraphService->getConnectedThoughts($thought);
        $edgeCollection = collect($graph['edges'])->values();
        $degreeMap = $this->buildDegreeMap(
            $edgeCollection->map(fn (array $edge): array => [
                'source' => 'thought-'.$edge['source'],
                'target' => 'thought-'.$edge['target'],
            ])
        );

        return [
            'nodes' => collect($graph['nodes'])->map(fn (array $node): array => [
                'data' => [
                    'id' => (string) $node['id'],
                    'label' => $this->summarize($node['content']),
                    'content' => $node['content'],
                    'href' => route('spaces.show', $thought->stream->space).'#thought-'.$node['id'],
                    'degree' => $degreeMap['thought-'.$node['id']] ?? 0,
                ],
            ])->all(),
            'edges' => $edgeCollection->map(fn (array $edge): array => [
                'data' => [
                    'id' => (string) ($edge['type'].'-'.$edge['source'].'-'.$edge['target']),
                    'source' => (string) $edge['source'],
                    'target' => (string) $edge['target'],
                    'type' => $edge['type'],
                ],
            ])->all(),
        ];
    }

    public function getFocusedGraph(Thought $thought, int $depth = 1, bool $showBacklinks = true, bool $showSyntheses = true, int $maxNodes = 50): array
    {
        $maxDepth = max(1, min($depth, 2));
        $connections = collect($this->thoughtGraphIndexService->getConnectedThoughts($thought->id, $maxDepth))
            ->filter(function (array $connection) use ($showSyntheses): bool {
                if (! $showSyntheses && $connection['link_type'] === 'synthesis') {
                    return false;
                }

                return true;
            })
            ->take(max(1, $maxNodes - 1))
            ->values();

        $includedThoughtIds = $connections
            ->pluck('linked_thought_id')
            ->prepend($thought->id)
            ->unique()
            ->values();

        $spaceGraph = $this->domainThoughtGraphService->getSpaceGraph($thought->stream->space);
        $includedNodeIds = $includedThoughtIds->map(fn (int $id): string => 'thought-'.$id)->all();

        $filteredEdges = collect($spaceGraph['edges'])
            ->filter(function (array $edge) use ($includedNodeIds, $showBacklinks, $showSyntheses, $thought): bool {
                if (! in_array($edge['source'], $includedNodeIds, true) || ! in_array($edge['target'], $includedNodeIds, true)) {
                    return false;
                }

                if (! $showSyntheses && $edge['type'] === 'synthesis') {
                    return false;
                }

                if (! $showBacklinks && $edge['type'] === 'link' && $edge['target'] === 'thought-'.$thought->id) {
                    return false;
                }

                return true;
            })
            ->values();

        $degreeMap = $this->buildDegreeMap($filteredEdges);

        $nodes = collect($spaceGraph['nodes'])
            ->filter(fn (array $node): bool => $node['kind'] === 'thought' && in_array($node['id'], $includedNodeIds, true))
            ->values()
            ->map(fn (array $node): array => [
                'data' => [
                    'id' => (string) $node['resource_id'],
                    'label' => $node['label'],
                    'content' => $node['content'],
                    'href' => $node['href'],
                    'degree' => $degreeMap[$node['id']] ?? 0,
                    'isCenter' => $node['resource_id'] === $thought->id,
                ],
            ])
            ->all();

        return [
            'center' => [
                'id' => (string) $thought->id,
                'label' => $this->summarize($thought->content),
                'content' => $thought->content,
            ],
            'nodes' => $nodes,
            'edges' => $filteredEdges->map(fn (array $edge): array => [
                'data' => [
                    'id' => (string) $edge['id'],
                    'source' => (string) str_replace('thought-', '', $edge['source']),
                    'target' => (string) str_replace('thought-', '', $edge['target']),
                    'type' => $edge['type'],
                ],
            ])->all(),
        ];
    }

    public function findPath(Thought $fromThought, Thought $toThought, int $maxDepth = 6, int $maxVisited = 500): array
    {
        if ($fromThought->id === $toThought->id) {
            return [
                'nodes' => [[
                    'data' => [
                        'id' => (string) $fromThought->id,
                        'label' => $this->summarize($fromThought->content),
                        'content' => $fromThought->content,
                        'href' => route('spaces.show', $fromThought->stream->space).'#thought-'.$fromThought->id,
                    ],
                ]],
                'edges' => [],
                'path' => [$fromThought->id],
            ];
        }

        $queue = collect([['id' => $fromThought->id, 'depth' => 0]]);
        $visited = [$fromThought->id => true];
        $parents = [];
        $found = false;
        $visitedCount = 1;

        while ($queue->isNotEmpty() && $visitedCount <= $maxVisited) {
            $current = $queue->shift();

            if ($current['depth'] >= $maxDepth) {
                continue;
            }

            $neighbors = $this->thoughtGraphIndexRepository->directNeighborsForThought($current['id']);

            foreach ($neighbors as $neighbor) {
                $neighborId = $neighbor->linked_thought_id;

                if (isset($visited[$neighborId])) {
                    continue;
                }

                $visited[$neighborId] = true;
                $visitedCount++;
                $parents[$neighborId] = [
                    'id' => $current['id'],
                    'type' => $neighbor->link_type,
                ];

                if ($neighborId === $toThought->id) {
                    $found = true;
                    break 2;
                }

                if ($visitedCount >= $maxVisited) {
                    break 2;
                }

                $queue->push([
                    'id' => $neighborId,
                    'depth' => $current['depth'] + 1,
                ]);
            }
        }

        if (! $found) {
            return [
                'nodes' => [],
                'edges' => [],
                'path' => [],
            ];
        }

        $path = [$toThought->id];
        $cursor = $toThought->id;

        while (isset($parents[$cursor])) {
            $cursor = $parents[$cursor]['id'];
            array_unshift($path, $cursor);
        }

        $thoughts = Thought::query()
            ->with(['stream.space'])
            ->whereIn('id', $path)
            ->orderBy('id')
            ->get()
            ->keyBy('id');

        $edges = collect($path)
            ->values()
            ->slice(0, -1)
            ->map(function (int $sourceId, int $index) use ($path, $parents): array {
                $targetId = $path[$index + 1];

                return [
                    'data' => [
                        'id' => 'path-'.$sourceId.'-'.$targetId,
                        'source' => (string) $sourceId,
                        'target' => (string) $targetId,
                        'type' => $parents[$targetId]['type'] ?? 'direct',
                    ],
                ];
            })
            ->all();

        return [
            'nodes' => collect($path)->map(function (int $thoughtId) use ($thoughts): array {
                $thought = $thoughts[$thoughtId];

                return [
                    'data' => [
                        'id' => (string) $thoughtId,
                        'label' => $this->summarize($thought->content),
                        'content' => $thought->content,
                        'href' => route('spaces.show', $thought->stream->space).'#thought-'.$thoughtId,
                    ],
                ];
            })->all(),
            'edges' => $edges,
            'path' => $path,
        ];
    }

    private function buildDegreeMap(iterable $edges): array
    {
        $degrees = [];

        foreach ($edges as $edge) {
            $degrees[$edge['source']] = ($degrees[$edge['source']] ?? 0) + 1;
            $degrees[$edge['target']] = ($degrees[$edge['target']] ?? 0) + 1;
        }

        return $degrees;
    }

    private function summarize(string $content): string
    {
        $singleLine = trim((string) preg_replace('/\s+/', ' ', $content));

        return mb_strimwidth($singleLine, 0, 36, '...');
    }
}
