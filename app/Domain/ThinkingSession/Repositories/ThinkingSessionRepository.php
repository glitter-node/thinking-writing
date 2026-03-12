<?php

namespace App\Domain\ThinkingSession\Repositories;

use App\Domain\ThinkingSession\Models\ThinkingSession;
use Carbon\CarbonImmutable;
use Illuminate\Support\Collection;

class ThinkingSessionRepository
{
    public function findForUserOnDay(int $userId, CarbonImmutable $day): ?ThinkingSession
    {
        return ThinkingSession::query()
            ->where('user_id', $userId)
            ->whereDate('started_at', $day->toDateString())
            ->first();
    }

    public function createForUser(int $userId, CarbonImmutable $timestamp): ThinkingSession
    {
        return ThinkingSession::query()->create([
            'user_id' => $userId,
            'started_at' => $timestamp,
            'thought_count' => 0,
        ]);
    }

    public function incrementThoughtCount(ThinkingSession $session): ThinkingSession
    {
        $session->increment('thought_count');

        return $session->fresh();
    }

    public function getDaysWithThoughts(int $userId): Collection
    {
        return ThinkingSession::query()
            ->where('user_id', $userId)
            ->where('thought_count', '>', 0)
            ->orderByDesc('started_at')
            ->get(['started_at', 'thought_count']);
    }
}
