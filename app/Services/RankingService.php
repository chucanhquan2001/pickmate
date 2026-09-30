<?php

namespace App\Services;

use App\Enums\MatchStatus;
use App\Models\Minigame;
use App\Models\Ranking;
use App\Models\RankingLog;
use App\Support\MatchScore;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class RankingService
{
    public function rebuild(Minigame $minigame, ?int $matchId = null): void
    {
        DB::transaction(function () use ($minigame, $matchId) {
            $roster = $minigame->members()->orderBy('name')->orderBy('id')->get();
            $stats = [];

            foreach ($roster as $member) {
                $stats[$member->id] = [
                    'member_id' => $member->id,
                    'name' => $member->name,
                    'matches' => 0,
                    'wins' => 0,
                    'losses' => 0,
                    'points' => 0,
                ];
            }

            $matches = $minigame->matches()
                ->where('status', MatchStatus::Completed)
                ->with(['players', 'sets'])
                ->get();

            foreach ($matches as $match) {
                $sets = $match->sets->map(fn ($set) => [
                    'team_1_score' => $set->team_1_score,
                    'team_2_score' => $set->team_2_score,
                ])->all();

                if ($sets === []) {
                    continue;
                }

                try {
                    $winner = MatchScore::winnerTeam($sets, $minigame->default_score, $minigame->best_of);
                } catch (ValidationException) {
                    continue;
                }

                $clean = MatchScore::isCleanWin($sets, $winner);

                foreach ($match->players as $player) {
                    if (! isset($stats[$player->member_id])) {
                        continue;
                    }

                    $won = $player->team === $winner;
                    $stats[$player->member_id]['matches']++;
                    $stats[$player->member_id][$won ? 'wins' : 'losses']++;
                    $stats[$player->member_id]['points'] += $this->pointsFor($minigame, $won, $clean);
                }
            }

            $ranked = collect($stats)
                ->map(function (array $row) {
                    $row['win_rate'] = $row['matches'] > 0 ? $row['wins'] / $row['matches'] : 0;

                    return $row;
                })
                ->sort(function (array $left, array $right) {
                    return [$right['points'], $right['wins'], $right['win_rate'], $left['name'], $left['member_id']]
                        <=> [$left['points'], $left['wins'], $left['win_rate'], $right['name'], $right['member_id']];
                })
                ->values();

            $existing = Ranking::query()
                ->where('minigame_id', $minigame->id)
                ->lockForUpdate()
                ->get()
                ->keyBy('member_id');

            $keep = [];

            foreach ($ranked as $index => $row) {
                $rank = $index + 1;
                $previous = $existing->get($row['member_id']);
                $oldPoints = $previous?->points ?? 0;
                $oldRank = $previous?->rank;
                $changed = $previous === null
                    ? $row['points'] !== 0
                    : ($previous->points !== $row['points'] || $previous->rank !== $rank);

                Ranking::query()->updateOrCreate(
                    [
                        'minigame_id' => $minigame->id,
                        'member_id' => $row['member_id'],
                    ],
                    [
                        'matches' => $row['matches'],
                        'wins' => $row['wins'],
                        'losses' => $row['losses'],
                        'points' => $row['points'],
                        'rank' => $rank,
                        'updated_at' => now(),
                    ],
                );

                if ($changed) {
                    RankingLog::query()->create([
                        'minigame_id' => $minigame->id,
                        'member_id' => $row['member_id'],
                        'match_id' => $matchId,
                        'old_point' => $oldPoints,
                        'change_point' => $row['points'] - $oldPoints,
                        'new_point' => $row['points'],
                        'old_rank' => $oldRank,
                        'new_rank' => $rank,
                        'created_at' => now(),
                    ]);
                }

                $keep[] = $row['member_id'];
            }

            Ranking::query()
                ->where('minigame_id', $minigame->id)
                ->when($keep !== [], fn ($query) => $query->whereNotIn('member_id', $keep))
                ->delete();
        });
    }

    private function pointsFor(Minigame $minigame, bool $won, bool $clean): int
    {
        $points = $minigame->participation_points;
        $points += $won ? $minigame->win_points : $minigame->loss_points;

        if ($won && $clean) {
            $points += $minigame->clean_win_bonus;
        }

        return $points;
    }
}
