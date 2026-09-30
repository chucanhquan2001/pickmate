<?php

namespace App\Services;

use App\Enums\MatchStatus;
use App\Enums\MinigameStatus;
use App\Models\Club;
use App\Models\MatchGame;
use App\Models\Minigame;
use Illuminate\Support\Collection;

class DashboardService
{
    /**
     * @return Collection<int, Minigame>
     */
    public function activeMinigames(Club $club): Collection
    {
        return $club->minigames()
            ->where('status', MinigameStatus::Active)
            ->withCount([
                'members',
                'matches as match_count' => fn ($query) => $query->where('status', '!=', MatchStatus::Cancelled->value),
            ])
            ->orderBy('name')
            ->get();
    }

    /**
     * @return array{roster_count: int, matches_this_week: int, matches_played: int, today: Collection<int, MatchGame>, upcoming: Collection<int, MatchGame>, recent_results: Collection<int, MatchGame>}
     */
    public function minigame(Minigame $minigame): array
    {
        $timezone = $minigame->club->timezone ?: 'Asia/Ho_Chi_Minh';
        $weekStart = now($timezone)->startOfWeek()->utc();
        $weekEnd = now($timezone)->endOfWeek()->utc();
        $todayStart = now($timezone)->startOfDay()->utc();
        $todayEnd = now($timezone)->endOfDay()->utc();

        $notCancelled = fn ($query) => $query->where('status', '!=', MatchStatus::Cancelled->value);

        return [
            'roster_count' => $minigame->members()->count(),
            'matches_this_week' => $minigame->matches()->where($notCancelled)->whereBetween('scheduled_at', [$weekStart, $weekEnd])->count(),
            'matches_played' => $minigame->matches()->where('status', MatchStatus::Completed)->count(),
            'today' => $minigame->matches()
                ->where($notCancelled)
                ->whereBetween('scheduled_at', [$todayStart, $todayEnd])
                ->with(['players.member', 'sets', 'court'])
                ->orderBy('scheduled_at')
                ->get(),
            'upcoming' => $minigame->matches()
                ->where('status', MatchStatus::Scheduled)
                ->where('scheduled_at', '>=', now())
                ->with(['players.member', 'sets', 'court'])
                ->orderBy('scheduled_at')
                ->limit(5)
                ->get(),
            'recent_results' => $minigame->matches()
                ->where('status', MatchStatus::Completed)
                ->with(['players.member', 'sets', 'court'])
                ->orderByDesc('completed_at')
                ->limit(5)
                ->get(),
        ];
    }
}
