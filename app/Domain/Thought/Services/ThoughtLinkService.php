<?php

namespace App\Domain\Thought\Services;

use App\Domain\Thought\Models\Thought;
use App\Domain\Thought\Repositories\ThoughtRepository;
use App\Domain\ThoughtLink\Repositories\ThoughtLinkRepository;

class ThoughtLinkService
{
    public function __construct(
        private readonly ThoughtRepository $thoughtRepository,
        private readonly ThoughtLinkRepository $thoughtLinkRepository,
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

    public function createLinks(Thought $thought, ?callable $missingThoughtCreator = null): Thought
    {
        return $this->syncLinks($thought, $missingThoughtCreator);
    }

    public function updateLinks(Thought $thought, ?callable $missingThoughtCreator = null): Thought
    {
        return $this->syncLinks($thought, $missingThoughtCreator);
    }

    private function syncLinks(Thought $thought, ?callable $missingThoughtCreator = null): Thought
    {
        $labels = $this->parseLinks($thought->content);
        $targetThoughtIds = collect($labels)
            ->map(fn (string $label): ?int => $this->resolveTargetThoughtId($thought, $label, $missingThoughtCreator))
            ->filter()
            ->unique()
            ->reject(fn (int $targetThoughtId): bool => $targetThoughtId === $thought->id)
            ->values()
            ->all();

        $this->thoughtLinkRepository->deleteForSource($thought);
        $this->thoughtLinkRepository->createMany($thought, $targetThoughtIds);

        return $this->thoughtRepository->refreshWithRelations($thought, [
            'stream.space',
            'outgoingLinks.targetThought.stream.space',
            'incomingLinks.sourceThought.stream.space',
        ]);
    }

    private function resolveTargetThoughtId(Thought $sourceThought, string $label, ?callable $missingThoughtCreator = null): ?int
    {
        $space = $sourceThought->stream->space;

        $targetThought = $this->thoughtRepository->findByExactContentWithinSpace($space, $label);

        if ($targetThought) {
            return $targetThought->id;
        }

        if (! $missingThoughtCreator) {
            throw new \LogicException('A placeholder thought creator is required when syncing missing links.');
        }

        $placeholderThought = $missingThoughtCreator($sourceThought, $label);

        return $placeholderThought->id;
    }
}
