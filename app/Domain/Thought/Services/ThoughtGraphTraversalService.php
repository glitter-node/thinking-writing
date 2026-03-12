<?php

namespace App\Domain\Thought\Services;

use App\Domain\Thought\Models\Thought;

class ThoughtGraphTraversalService
{
    public function __construct(
        private readonly ThoughtGraphIndexService $thoughtGraphIndexService,
    ) {
    }

    public function getConnectedThoughts(Thought $thought, int $maxDepth = 2): array
    {
        $connections = $this->thoughtGraphIndexService->getConnectedThoughts($thought->id, $maxDepth);

        return [
            'thought' => [
                'id' => $thought->id,
                'content' => $thought->content,
            ],
            'nodes' => collect([$thought])
                ->map(fn (Thought $item): array => [
                    'id' => $item->id,
                    'content' => $item->content,
                    'stream_title' => $item->stream->title,
                ])
                ->concat(
                    $connections->map(fn (array $connection): array => [
                        'id' => $connection['linked_thought']['id'],
                        'content' => $connection['linked_thought']['content'],
                        'stream_title' => $connection['linked_thought']['stream_title'],
                    ])
                )
                ->unique('id')
                ->values()
                ->all(),
            'edges' => $connections->map(fn (array $connection): array => $this->formatEdge($thought, $connection))->values()->all(),
        ];
    }

    private function formatEdge(Thought $thought, array $connection): array
    {
        $source = $thought->id;
        $target = $connection['linked_thought_id'];

        if (
            $connection['link_type'] === 'synthesis'
            && $thought->synthesizedFrom
            && $thought->synthesizedFrom->items->contains('thought_id', $connection['linked_thought_id'])
        ) {
            $source = $connection['linked_thought_id'];
            $target = $thought->id;
        }

        if (
            $connection['link_type'] === 'evolution'
            && $thought->parent_id === $connection['linked_thought_id']
        ) {
            $source = $connection['linked_thought_id'];
            $target = $thought->id;
        }

        return [
            'source' => $source,
            'target' => $target,
            'type' => $connection['link_type'],
            'depth' => $connection['depth'],
        ];
    }
}
