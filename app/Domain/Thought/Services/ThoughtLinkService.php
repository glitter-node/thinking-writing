<?php

namespace App\Domain\Thought\Services;

use App\Domain\Stream\Repositories\StreamRepository;
use App\Domain\Thought\Models\Thought;
use App\Domain\Thought\Repositories\ThoughtRepository;
use App\Domain\ThoughtLink\Repositories\ThoughtLinkRepository;
use Illuminate\Support\Collection;

class ThoughtLinkService
{
    public function __construct(
        private readonly ThoughtRepository $thoughtRepository,
        private readonly ThoughtLinkRepository $thoughtLinkRepository,
        private readonly StreamRepository $streamRepository,
        private readonly ThoughtGraphIndexService $thoughtGraphIndexService,
    ) {
    }

    public function parseLinks(string $content): array
    {
        preg_match_all('/\[\[([^\[\]]+)\]\]/', $content, $matches);

        return collect($matches[1] ?? [])
            ->map(fn (string $label): string => trim($label))
            ->filter()
            ->unique()
            ->values()
            ->all();
    }

    public function createLinks(Thought $thought): Thought
    {
        return $this->syncLinks($thought);
    }

    public function updateLinks(Thought $thought): Thought
    {
        return $this->syncLinks($thought);
    }

    private function syncLinks(Thought $thought): Thought
    {
        $labels = $this->parseLinks($thought->content);
        $targetThoughtIds = collect($labels)
            ->map(fn (string $label): ?int => $this->resolveTargetThoughtId($thought, $label))
            ->filter()
            ->unique()
            ->reject(fn (int $targetThoughtId): bool => $targetThoughtId === $thought->id)
            ->values()
            ->all();

        $this->thoughtLinkRepository->deleteForSource($thought);
        $this->thoughtLinkRepository->createMany($thought, $targetThoughtIds);
        $this->thoughtGraphIndexService->updateGraphIndex($thought->id);

        return $this->thoughtRepository->refreshWithRelations($thought, [
            'stream.space',
            'outgoingLinks.targetThought.stream.space',
            'incomingLinks.sourceThought.stream.space',
        ]);
    }

    private function resolveTargetThoughtId(Thought $sourceThought, string $label): ?int
    {
        $space = $sourceThought->stream->space;

        $targetThought = $this->thoughtRepository->findByExactContentWithinSpace($space, $label);

        if ($targetThought) {
            return $targetThought->id;
        }

        $stream = $this->streamRepository->findById($sourceThought->stream_id);

        return $this->thoughtRepository->createForStream($stream, [
            'user_id' => $sourceThought->user_id,
            'parent_id' => null,
            'content' => $label,
            'priority' => 'low',
            'tags' => ['placeholder'],
            'position' => $this->thoughtRepository->nextPositionForStream($stream),
        ])->id;
    }
}
