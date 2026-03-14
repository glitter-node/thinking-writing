<?php

namespace App\Modules\EmergenceModule;

use App\Core\Contracts\ThinkingModuleInterface;
use App\Domain\Thought\Models\Thought;

class EmergenceModule implements ThinkingModuleInterface
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
