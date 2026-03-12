<?php

namespace App\Core;

use App\Core\Contracts\ThinkingModuleInterface;
use App\Domain\Thought\Models\Thought;
use Illuminate\Contracts\Container\Container;
use Illuminate\Support\Collection;

class ModuleManager
{
    /**
     * @var array<class-string<ThinkingModuleInterface>>
     */
    private array $moduleClasses = [
        \App\Modules\PromptModule\PromptModule::class,
        \App\Modules\ReviewModule\ReviewModule::class,
        \App\Modules\EvolutionModule\EvolutionModule::class,
        \App\Modules\SynthesisModule\SynthesisModule::class,
        \App\Modules\EmergenceModule\EmergenceModule::class,
        \App\Modules\LifecycleModule\LifecycleModule::class,
    ];

    /**
     * @var Collection<int, ThinkingModuleInterface>|null
     */
    private ?Collection $modules = null;

    public function __construct(
        private readonly Container $container,
    ) {
    }

    /**
     * @return Collection<int, ThinkingModuleInterface>
     */
    public function discoverModules(): Collection
    {
        if ($this->modules !== null) {
            return $this->modules;
        }

        $this->modules = collect($this->moduleClasses)
            ->filter(fn (string $class): bool => class_exists($class))
            ->map(fn (string $class): ThinkingModuleInterface => $this->container->make($class));

        return $this->modules;
    }

    public function registerModules(): void
    {
        $this->discoverModules()->each(fn (ThinkingModuleInterface $module) => $module->register());
    }

    public function bootModules(): void
    {
        $this->discoverModules()->each(fn (ThinkingModuleInterface $module) => $module->boot());
    }

    public function processThought(Thought $thought, array $context = []): void
    {
        $this->discoverModules()->each(fn (ThinkingModuleInterface $module) => $module->processThought($thought, $context));
    }
}
