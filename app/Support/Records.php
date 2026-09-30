<?php

namespace App\Support;

use App\Models\Club;
use App\Models\Court;
use App\Models\MatchGame;
use App\Models\MatchPlayer;
use App\Models\Member;
use App\Models\Minigame;
use App\Models\Ranking;
use App\Models\User;

class Records
{
    /**
     * @return array<string, mixed>
     */
    public static function user(User $user): array
    {
        return [
            'id' => $user->id,
            'name' => $user->name,
            'email' => $user->email,
            'avatar' => $user->avatar,
            'role' => $user->role->value,
            'club_id' => $user->club_id,
            'can_manage' => $user->canManageClub(),
            'is_owner' => $user->isOwner(),
        ];
    }

    /**
     * @return array<string, mixed>
     */
    public static function club(Club $club): array
    {
        return [
            'id' => $club->id,
            'name' => $club->name,
            'slug' => $club->slug,
            'logo' => $club->logo,
            'timezone' => $club->timezone,
            'language' => $club->language,
            'status' => $club->status->value,
            'default_score' => $club->default_score,
            'default_best_of' => $club->default_best_of,
            'default_participation_points' => $club->default_participation_points,
            'default_win_points' => $club->default_win_points,
            'default_loss_points' => $club->default_loss_points,
            'default_clean_win_bonus' => $club->default_clean_win_bonus,
        ];
    }

    /**
     * @return array<string, mixed>
     */
    public static function member(Member $member): array
    {
        $data = [
            'id' => $member->id,
            'club_id' => $member->club_id,
            'user_id' => $member->user_id,
            'name' => $member->name,
            'nickname' => $member->nickname,
            'avatar' => $member->avatar,
            'gender' => $member->gender->value,
            'birthday' => $member->birthday?->toDateString(),
            'phone' => $member->phone,
            'email' => $member->email,
            'level' => $member->level->value,
            'joined_at' => $member->joined_at?->toDateString(),
            'status' => $member->status->value,
        ];

        if (array_key_exists('minigames_count', $member->getAttributes())) {
            $data['minigames_count'] = (int) $member->minigames_count;
        }

        return $data;
    }

    /**
     * @return array<string, mixed>
     */
    public static function memberDetail(Member $member): array
    {
        $data = self::member($member);
        $data['minigames'] = $member->relationLoaded('rankings')
            ? $member->rankings->map(fn (Ranking $ranking) => [
                'id' => $ranking->minigame_id,
                'name' => $ranking->minigame->name,
                'format' => $ranking->minigame->format->value,
                'rank' => $ranking->rank,
                'points' => $ranking->points,
                'matches' => $ranking->matches,
                'wins' => $ranking->wins,
                'losses' => $ranking->losses,
                'win_rate' => $ranking->winRate(),
            ])->values()->all()
            : [];
        $history = $member->relationLoaded('history') ? $member->getRelation('history') : collect();
        $data['matches'] = $history->map(fn (MatchGame $match) => self::match($match))->values()->all();

        return $data;
    }

    /**
     * @return array<string, mixed>
     */
    public static function court(Court $court): array
    {
        return [
            'id' => $court->id,
            'club_id' => $court->club_id,
            'name' => $court->name,
            'code' => $court->code,
            'status' => $court->status->value,
            'note' => $court->note,
        ];
    }

    /**
     * @return array<string, mixed>
     */
    public static function minigame(Minigame $minigame): array
    {
        $attributes = $minigame->getAttributes();
        $data = [
            'id' => $minigame->id,
            'club_id' => $minigame->club_id,
            'name' => $minigame->name,
            'description' => $minigame->description,
            'format' => $minigame->format->value,
            'status' => $minigame->status->value,
            'starts_at' => $minigame->starts_at?->toIso8601String(),
            'ends_at' => $minigame->ends_at?->toIso8601String(),
            'default_score' => $minigame->default_score,
            'best_of' => $minigame->best_of,
            'participation_points' => $minigame->participation_points,
            'win_points' => $minigame->win_points,
            'loss_points' => $minigame->loss_points,
            'clean_win_bonus' => $minigame->clean_win_bonus,
            'players_per_team' => $minigame->format->playersPerTeam(),
        ];

        if (array_key_exists('members_count', $attributes)) {
            $data['roster_count'] = (int) $minigame->members_count;
        }

        if (array_key_exists('match_count', $attributes)) {
            $data['match_count'] = (int) $minigame->match_count;
        } elseif (array_key_exists('matches_count', $attributes)) {
            $data['match_count'] = (int) $minigame->matches_count;
        }

        if ($minigame->relationLoaded('members')) {
            $data['roster'] = $minigame->members
                ->map(fn (Member $member) => self::member($member))
                ->values()
                ->all();
        }

        return $data;
    }

    /**
     * @return array<string, mixed>
     */
    public static function summary(Minigame $minigame): array
    {
        return [
            'id' => $minigame->id,
            'name' => $minigame->name,
            'format' => $minigame->format->value,
            'status' => $minigame->status->value,
        ];
    }

    /**
     * @return array<string, mixed>
     */
    public static function match(MatchGame $match): array
    {
        return [
            'id' => $match->id,
            'minigame_id' => $match->minigame_id,
            'minigame' => $match->relationLoaded('minigame') ? [
                'id' => $match->minigame->id,
                'name' => $match->minigame->name,
            ] : null,
            'court_id' => $match->court_id,
            'court' => $match->relationLoaded('court') && $match->court !== null
                ? self::court($match->court)
                : null,
            'status' => $match->status->value,
            'scheduled_at' => $match->scheduled_at?->toIso8601String(),
            'started_at' => $match->started_at?->toIso8601String(),
            'completed_at' => $match->completed_at?->toIso8601String(),
            'winner_team' => $match->winnerTeam(),
            'team_1' => self::teamPlayers($match, 1),
            'team_2' => self::teamPlayers($match, 2),
            'sets' => $match->relationLoaded('sets')
                ? $match->sets->map(fn ($set) => [
                    'set_number' => $set->set_number,
                    'team_1_score' => $set->team_1_score,
                    'team_2_score' => $set->team_2_score,
                ])->values()->all()
                : [],
        ];
    }

    /**
     * @return array<string, mixed>
     */
    public static function ranking(Ranking $ranking): array
    {
        return [
            'rank' => $ranking->rank,
            'member_id' => $ranking->member_id,
            'member' => $ranking->member?->name,
            'matches' => $ranking->matches,
            'wins' => $ranking->wins,
            'losses' => $ranking->losses,
            'win_rate' => $ranking->winRate(),
            'points' => $ranking->points,
        ];
    }

    /**
     * @return list<array<string, mixed>>
     */
    private static function teamPlayers(MatchGame $match, int $team): array
    {
        if (! $match->relationLoaded('players')) {
            return [];
        }

        return $match->players
            ->where('team', $team)
            ->sortBy('position')
            ->map(fn (MatchPlayer $player) => [
                'member_id' => $player->member_id,
                'name' => $player->member?->name,
                'position' => $player->position,
            ])
            ->values()
            ->all();
    }
}
