<?php

namespace App\Core\Contracts;

use App\Domain\Thought\Models\Thought;

interface ThinkingModuleInterface
{
    public function register(): void;

    public function boot(): void;

    public function processThought(Thought $thought, array $context = []): void;
}
