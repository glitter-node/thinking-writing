<?php

namespace App\Domain\ThoughtVersion\Services;

use App\Domain\Thought\Models\Thought;
use App\Domain\ThoughtVersion\Repositories\ThoughtVersionRepository;
use Illuminate\Database\Eloquent\Collection;

class ThoughtVersionService
{
    public function __construct(
        private readonly ThoughtVersionRepository $thoughtVersionRepository,
    ) {
    }

    public function createInitialVersion(Thought $thought): void
    {
        if ($this->thoughtVersionRepository->latestVersionNumber($thought) > 0) {
            return;
        }

        $this->thoughtVersionRepository->create($thought, 1, $thought->content);
    }

    public function createVersion(Thought $thought, string $content): void
    {
        $latestVersion = $this->thoughtVersionRepository->latestVersionNumber($thought);

        if ($latestVersion === 0) {
            $this->thoughtVersionRepository->create($thought, 1, $content);

            return;
        }

        $latestContent = $thought->versions()->where('version', $latestVersion)->value('content');

        if ($latestContent === $content) {
            return;
        }

        $this->thoughtVersionRepository->create($thought, $latestVersion + 1, $content);
    }

    public function getVersionHistory(Thought $thought): Collection
    {
        return $this->thoughtVersionRepository->historyForThought($thought);
    }
}
