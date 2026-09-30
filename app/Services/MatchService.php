<?php

namespace App\Services;

use App\Enums\MatchStatus;
use App\Enums\MemberGender;
use App\Enums\MemberStatus;
use App\Models\Court;
use App\Models\MatchGame;
use App\Models\Member;
use App\Models\Minigame;
use App\Models\User;
use App\Support\MatchScore;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class MatchService
{
    public function __construct(private RankingService $rankings) {}

    /**
     * @param  array{scheduled_at: string, court_id?: int|null, team_1: list<int>, team_2: list<int>}  $data
     */
    public function create(Minigame $minigame, User $actor, array $data): MatchGame
    {
        $this->assertPlayable($minigame);
        $court = $this->court($minigame, $data['court_id'] ?? null);
        $this->assertLineup($minigame, $data['team_1'], $data['team_2']);

        return DB::transaction(function () use ($minigame, $actor, $data, $court) {
            $match = MatchGame::query()->create([
                'minigame_id' => $minigame->id,
                'court_id' => $court?->id,
                'status' => MatchStatus::Scheduled,
                'scheduled_at' => $data['scheduled_at'],
                'created_by' => $actor->id,
            ]);

            $this->syncPlayers($match, $data['team_1'], $data['team_2']);

            return $match->load(['players.member', 'sets', 'court']);
        });
    }

    /**
     * @param  array{scheduled_at?: string, court_id?: int|null, team_1?: list<int>, team_2?: list<int>}  $data
     */
    public function update(Minigame $minigame, MatchGame $match, array $data): MatchGame
    {
        $this->assertPlayable($minigame);
        $this->assertMutable($match);

        if (array_key_exists('court_id', $data)) {
            $match->court_id = $this->court($minigame, $data['court_id'])?->id;
        }

        if (array_key_exists('scheduled_at', $data)) {
            $match->scheduled_at = $data['scheduled_at'];
        }

        if (isset($data['team_1'], $data['team_2'])) {
            $this->assertLineup($minigame, $data['team_1'], $data['team_2']);
        }

        DB::transaction(function () use ($match, $data) {
            $match->save();

            if (isset($data['team_1'], $data['team_2'])) {
                $this->syncPlayers($match, $data['team_1'], $data['team_2']);
            }
        });

        return $match->refresh()->load(['players.member', 'sets', 'court']);
    }

    public function start(Minigame $minigame, MatchGame $match): MatchGame
    {
        $this->assertPlayable($minigame);

        if ($match->status !== MatchStatus::Scheduled) {
            throw ValidationException::withMessages([
                'status' => 'Only a scheduled match can be started.',
            ]);
        }

        $match->status = MatchStatus::Playing;
        $match->started_at = now();
        $match->save();

        return $match->refresh()->load(['players.member', 'sets', 'court']);
    }

    public function cancel(Minigame $minigame, MatchGame $match): MatchGame
    {
        $this->assertPlayable($minigame);

        if (! in_array($match->status, [MatchStatus::Scheduled, MatchStatus::Playing], true)) {
            throw ValidationException::withMessages([
                'status' => 'Only a scheduled or playing match can be cancelled.',
            ]);
        }

        $match->status = MatchStatus::Cancelled;
        $match->save();

        return $match->refresh()->load(['players.member', 'sets', 'court']);
    }

    /**
     * @param  list<array{team_1_score: int, team_2_score: int}>  $sets
     */
    public function complete(Minigame $minigame, MatchGame $match, array $sets): MatchGame
    {
        $this->assertPlayable($minigame);

        if ($match->status === MatchStatus::Cancelled) {
            throw ValidationException::withMessages([
                'status' => 'A cancelled match cannot take a result.',
            ]);
        }

        $normalized = array_map(fn (array $set) => [
            'team_1_score' => (int) $set['team_1_score'],
            'team_2_score' => (int) $set['team_2_score'],
        ], $sets);

        MatchScore::winnerTeam($normalized, $minigame->default_score, $minigame->best_of);

        DB::transaction(function () use ($minigame, $match, $normalized) {
            $match->sets()->delete();

            foreach ($normalized as $index => $set) {
                $match->sets()->create([
                    'set_number' => $index + 1,
                    'team_1_score' => $set['team_1_score'],
                    'team_2_score' => $set['team_2_score'],
                ]);
            }

            $match->status = MatchStatus::Completed;
            $match->started_at ??= now();
            $match->completed_at = now();
            $match->save();

            $this->rankings->rebuild($minigame, $match->id);
        });

        return $match->refresh()->load(['players.member', 'sets', 'court']);
    }

    private function assertPlayable(Minigame $minigame): void
    {
        if (! $minigame->isActive()) {
            throw ValidationException::withMessages([
                'minigame' => 'Matches can only be changed while the minigame is active.',
            ]);
        }
    }

    private function assertMutable(MatchGame $match): void
    {
        if (! in_array($match->status, [MatchStatus::Scheduled, MatchStatus::Playing], true)) {
            throw ValidationException::withMessages([
                'status' => 'Only a scheduled or playing match can be edited.',
            ]);
        }
    }

    private function court(Minigame $minigame, mixed $courtId): ?Court
    {
        if ($courtId === null) {
            return null;
        }

        $court = Court::query()
            ->where('club_id', $minigame->club_id)
            ->whereKey($courtId)
            ->first();

        if ($court === null || $court->status->value !== 'active') {
            throw ValidationException::withMessages([
                'court_id' => 'Choose an active court from this club.',
            ]);
        }

        return $court;
    }

    /**
     * @param  list<int>  $teamOne
     * @param  list<int>  $teamTwo
     */
    private function assertLineup(Minigame $minigame, array $teamOne, array $teamTwo): void
    {
        $teamOne = array_map('intval', $teamOne);
        $teamTwo = array_map('intval', $teamTwo);
        $expected = $minigame->format->playersPerTeam();

        if (count($teamOne) !== $expected || count($teamTwo) !== $expected) {
            throw ValidationException::withMessages([
                'team_1' => "Each team needs {$expected} player(s) for this format.",
            ]);
        }

        if (count(array_unique(array_merge($teamOne, $teamTwo))) !== count($teamOne) + count($teamTwo)) {
            throw ValidationException::withMessages([
                'team_1' => 'A player cannot appear twice in the same match.',
            ]);
        }

        $members = Member::query()
            ->whereIn('id', array_merge($teamOne, $teamTwo))
            ->where('status', MemberStatus::Active)
            ->whereHas('minigames', fn ($query) => $query->whereKey($minigame->id))
            ->get()
            ->keyBy('id');

        if ($members->count() !== count($teamOne) + count($teamTwo)) {
            throw ValidationException::withMessages([
                'team_1' => 'Every player must be an active member of this minigame roster.',
            ]);
        }

        $this->assertTeamShape($minigame, $members->only($teamOne)->values());
        $this->assertTeamShape($minigame, $members->only($teamTwo)->values());
    }

    /**
     * @param  Collection<int, Member>  $team
     */
    private function assertTeamShape(Minigame $minigame, Collection $team): void
    {
        foreach ($team as $member) {
            if (! $minigame->format->allows($member->gender)) {
                throw ValidationException::withMessages([
                    'team_1' => 'A player does not match this minigame format.',
                ]);
            }
        }

        if ($minigame->format->value !== 'double_mixed') {
            return;
        }

        $males = $team->where('gender', MemberGender::Male)->count();
        $females = $team->where('gender', MemberGender::Female)->count();

        if ($males !== 1 || $females !== 1) {
            throw ValidationException::withMessages([
                'team_1' => 'Each mixed doubles team needs one male and one female player.',
            ]);
        }
    }

    /**
     * @param  list<int>  $teamOne
     * @param  list<int>  $teamTwo
     */
    private function syncPlayers(MatchGame $match, array $teamOne, array $teamTwo): void
    {
        $match->players()->delete();
        $timestamp = now();

        foreach ([[1, $teamOne], [2, $teamTwo]] as [$team, $memberIds]) {
            foreach (array_values($memberIds) as $position => $memberId) {
                $match->players()->create([
                    'member_id' => $memberId,
                    'team' => $team,
                    'position' => $position + 1,
                    'created_at' => $timestamp,
                ]);
            }
        }
    }
}
