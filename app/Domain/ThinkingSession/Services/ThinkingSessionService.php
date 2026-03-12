<?php

namespace App\Domain\ThinkingSession\Services;

use App\Domain\ThinkingSession\Repositories\ThinkingSessionRepository;
use Carbon\CarbonImmutable;
use Illuminate\Support\Facades\DB;

class ThinkingSessionService
{
    public function __construct(
        private readonly ThinkingSessionRepository $thinkingSessionRepository,
    ) {
    }

    public function recordThought(int $userId): void
    {
        DB::transaction(function () use ($userId): void {
            $today = CarbonImmutable::now()->startOfDay();
            $session = $this->thinkingSessionRepository->findForUserOnDay($userId, $today)
                ?? $this->thinkingSessionRepository->createForUser($userId, $today);

            $this->thinkingSessionRepository->incrementThoughtCount($session);
        });
    }

    public function getStreak(int $userId): array
    {
        $days = $this->thinkingSessionRepository->getDaysWithThoughts($userId)
            ->map(fn ($session) => CarbonImmutable::parse($session->started_at)->startOfDay())
            ->unique(fn (CarbonImmutable $day) => $day->toDateString())
            ->values();

        $streak = 0;
        $cursor = CarbonImmutable::now()->startOfDay();

        foreach ($days as $day) {
            if (! $day->equalTo($cursor)) {
                if ($streak === 0 && $day->equalTo($cursor->subDay())) {
                    $streak++;
                    $cursor = $day->subDay();
                    continue;
                }

                break;
            }

            $streak++;
            $cursor = $cursor->subDay();
        }

        return [
            'days' => $streak,
            'label' => $streak > 0
                ? "You captured thoughts {$streak} day".($streak === 1 ? '' : 's')." in a row."
                : 'Start your first thinking streak today.',
        ];
    }
}
