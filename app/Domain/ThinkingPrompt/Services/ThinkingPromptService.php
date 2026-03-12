<?php

namespace App\Domain\ThinkingPrompt\Services;

use App\Domain\ThinkingPrompt\Repositories\ThinkingPromptRepository;
use App\Domain\Thought\Models\Thought;
use Illuminate\Support\Carbon;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Cache;
use Throwable;

class ThinkingPromptService
{
    public function __construct(
        private readonly ThinkingPromptRepository $thinkingPromptRepository,
    ) {
    }

    public function getDailyPrompt(): ?array
    {
        $prompts = $this->cachedPrompts();

        if ($prompts->isEmpty()) {
            return null;
        }

        $index = Carbon::now()->dayOfYear % $prompts->count();

        return $this->formatPrompt($prompts->values()->get($index));
    }

    public function getRandomPrompt(): ?array
    {
        return $this->formatPrompt($this->thinkingPromptRepository->random());
    }

    public function getCategoryPrompt(?string $category): ?array
    {
        if (! $category) {
            return $this->getRandomPrompt();
        }

        return $this->formatPrompt(
            $this->thinkingPromptRepository->randomByCategory($category)
                ?? $this->thinkingPromptRepository->random(),
        );
    }

    public function getSmartSuggestionForUser(int $userId): ?array
    {
        $recentThoughts = Thought::query()
            ->where('user_id', $userId)
            ->latest('created_at')
            ->limit(8)
            ->pluck('content')
            ->implode(' ');

        if ($recentThoughts === '') {
            return $this->getDailyPrompt();
        }

        $category = $this->inferCategory($recentThoughts);

        return $this->getCategoryPrompt($category);
    }

    public function getBoardPromptPack(int $userId): array
    {
        return [
            'daily' => $this->getDailyPrompt(),
            'suggested' => $this->getSmartSuggestionForUser($userId),
            'templates' => $this->templates(),
        ];
    }

    public function templates(): array
    {
        return [
            ['label' => 'Idea', 'content' => "Idea:\nWhat if we could..."],
            ['label' => 'Observation', 'content' => "Observation:\nI noticed that..."],
            ['label' => 'Problem', 'content' => "Problem:\nThe friction is..."],
            ['label' => 'Question', 'content' => "Question:\nWhy does..."],
            ['label' => 'Insight', 'content' => "Insight:\nThe pattern here is..."],
        ];
    }

    private function cachedPrompts(): Collection
    {
        try {
            return Cache::store('redis')->remember('thinking-prompts:all', now()->addHours(6), function (): Collection {
                return $this->thinkingPromptRepository->all();
            });
        } catch (Throwable) {
            return Cache::remember('thinking-prompts:all:fallback', now()->addHours(1), function (): Collection {
                return $this->thinkingPromptRepository->all();
            });
        }
    }

    private function inferCategory(string $content): ?string
    {
        $content = mb_strtolower($content);
        $keywordMap = [
            'systems' => ['distributed', 'scalability', 'latency', 'throughput', 'queue', 'consistency'],
            'learning' => ['learn', 'lesson', 'read', 'study', 'research', 'book'],
            'workflow' => ['workflow', 'process', 'automation', 'routine', 'team', 'meeting'],
            'product' => ['user', 'feature', 'product', 'customer', 'onboarding', 'experience'],
            'reflection' => ['today', 'felt', 'noticed', 'thinking', 'idea', 'improve'],
        ];

        foreach ($keywordMap as $category => $keywords) {
            foreach ($keywords as $keyword) {
                if (str_contains($content, $keyword)) {
                    return $category;
                }
            }
        }

        return null;
    }

    private function formatPrompt(mixed $prompt): ?array
    {
        if (! $prompt) {
            return null;
        }

        return [
            'id' => $prompt->id,
            'prompt' => $prompt->prompt,
            'category' => $prompt->category,
        ];
    }
}
