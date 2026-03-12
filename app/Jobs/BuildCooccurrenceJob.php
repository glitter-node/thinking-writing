<?php

namespace App\Jobs;

use App\Domain\ThoughtEmergence\Services\ThoughtEmergenceService;
use App\Models\User;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;

class BuildCooccurrenceJob implements ShouldQueue
{
    use Queueable;

    public function handle(ThoughtEmergenceService $thoughtEmergenceService): void
    {
        User::query()->pluck('id')->each(
            fn (int $userId) => $thoughtEmergenceService->calculateCooccurrence($userId),
        );
    }
}
