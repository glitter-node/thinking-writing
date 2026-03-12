<?php

namespace App\Domain\ThinkingPrompt\Repositories;

use App\Domain\ThinkingPrompt\Models\ThinkingPrompt;
use Illuminate\Support\Collection;

class ThinkingPromptRepository
{
    public function all(): Collection
    {
        return ThinkingPrompt::query()
            ->orderBy('category')
            ->orderBy('id')
            ->get();
    }

    public function random(): ?ThinkingPrompt
    {
        return ThinkingPrompt::query()->inRandomOrder()->first();
    }

    public function randomByCategory(string $category): ?ThinkingPrompt
    {
        return ThinkingPrompt::query()
            ->where('category', $category)
            ->inRandomOrder()
            ->first();
    }
}
