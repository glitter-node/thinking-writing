<?php

namespace App\Modules\PromptModule;

use App\Core\Contracts\ThinkingModuleInterface;
use App\Domain\ThinkingPrompt\Services\ThinkingPromptService;
use App\Domain\Thought\Models\Thought;

class PromptModule implements ThinkingModuleInterface
{
    public function __construct(
        private readonly ThinkingPromptService $thinkingPromptService,
    ) {
    }

    public function register(): void
    {
    }

    public function boot(): void
    {
    }

    public function processThought(Thought $thought, array $context = []): void
    {
        $this->thinkingPromptService->getBoardPromptPack($thought->user_id);
    }
}
