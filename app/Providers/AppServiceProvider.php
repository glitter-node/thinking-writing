<?php

namespace App\Providers;

use App\Core\ModuleManager;
use App\Domain\Space\Models\Space;
use App\Domain\Stream\Models\Stream;
use App\Domain\Thought\Models\Thought;
use App\Policies\SpacePolicy;
use App\Policies\StreamPolicy;
use App\Policies\ThoughtPolicy;
use App\Services\AppVersionService;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        $this->app->singleton(ModuleManager::class, fn ($app) => new ModuleManager($app));
        $this->app->singleton(AppVersionService::class, fn () => new AppVersionService());
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        Gate::policy(Space::class, SpacePolicy::class);
        Gate::policy(Stream::class, StreamPolicy::class);
        Gate::policy(Thought::class, ThoughtPolicy::class);

        $moduleManager = $this->app->make(ModuleManager::class);
        $moduleManager->registerModules();
        $moduleManager->bootModules();
    }
}
