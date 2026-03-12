<?php

namespace App\Modules\LifecycleModule;

use App\Core\Contracts\ThinkingModuleInterface;
use App\Domain\Thought\Models\Thought;

class LifecycleModule implements ThinkingModuleInterface
{
    public function register(): void
    {
    }

    public function boot(): void
    {
    }

    public function processThought(Thought $thought, array $context = []): void
    {
    }
}
