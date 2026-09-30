<?php

namespace App\Services;

use App\Enums\MatchStatus;
use App\Enums\MemberStatus;
use App\Enums\MinigameStatus;
use App\Models\Club;
use App\Models\Member;
use App\Models\Minigame;
use App\Models\User;
use Illuminate\Validation\ValidationException;

class MinigameService
{
    public function __construct(private RankingService $rankings) {}

    /**
     * @param  array{name: string, description?: string|null, format: string, starts_at?: string|null, ends_at?: string|null}  $data
     */
    public function create(Club $club, User $actor, array $data): Minigame
    {
        return Minigame::query()->create([
            'club_id' => $club->id,
            'name' => $data['name'],
            'description' => $data['description'] ?? null,
            'format' => $data['format'],
            'status' => MinigameStatus::Draft,
            'starts_at' => $data['starts_at'] ?? null,
            'ends_at' => $data['ends_at'] ?? null,
            'default_score' => $club->default_score,
            'best_of' => $club->default_best_of,
            'participation_points' => $club->default_participation_points,
            'win_points' => $club->default_win_points,
            'loss_points' => $club->default_loss_points,
            'clean_win_bonus' => $club->default_clean_win_bonus,
            'created_by' => $actor->id,
        ]);
    }

    /**
     * @param  array{name?: string, description?: string|null, format?: string, starts_at?: string|null, ends_at?: string|null}  $data
     */
    public function update(Minigame $minigame, array $data): Minigame
    {
        $this->assertOpen($minigame);

        if (array_key_exists('format', $data) && $data['format'] !== $minigame->format->value) {
            if ($minigame->status !== MinigameStatus::Draft || $minigame->matches()->exists()) {
                throw ValidationException::withMessages([
                    'format' => 'Format can only change on a draft minigame that has no matches.',
                ]);
            }

            $minigame->format = $data['format'];
            $this->assertRosterFitsFormat($minigame);
        }

        $minigame->fill(collect($data)->only(['name', 'description', 'starts_at', 'ends_at'])->all());
        $minigame->save();

        return $minigame->refresh();
    }

    /**
     * @param  array{default_score: int, best_of: int, participation_points: int, win_points: int, loss_points: int, clean_win_bonus: int}  $rules
     */
    public function updateRules(Minigame $minigame, array $rules): Minigame
    {
        $this->assertOpen($minigame);

        $minigame->fill($rules);
        $minigame->save();

        if ($minigame->matches()->where('status', MatchStatus::Completed)->exists()) {
            $this->rankings->rebuild($minigame);
        }

        return $minigame->refresh();
    }

    /**
     * @param  list<int>  $memberIds
     */
    public function syncRoster(Minigame $minigame, array $memberIds): Minigame
    {
        $this->assertOpen($minigame);

        $memberIds = array_values(array_unique(array_map('intval', $memberIds)));
        $members = Member::query()
            ->where('club_id', $minigame->club_id)
            ->whereIn('id', $memberIds)
            ->get();

        if ($members->count() !== count($memberIds)) {
            throw ValidationException::withMessages([
                'member_ids' => 'Every selected person must belong to this club.',
            ]);
        }

        foreach ($members as $member) {
            if ($member->status !== MemberStatus::Active) {
                throw ValidationException::withMessages([
                    'member_ids' => 'Only active members can join a roster.',
                ]);
            }

            if (! $minigame->format->allows($member->gender)) {
                throw ValidationException::withMessages([
                    'member_ids' => 'A selected member does not match this minigame format.',
                ]);
            }
        }

        $timestamp = now();
        $minigame->members()->sync(collect($memberIds)->mapWithKeys(
            fn (int $id) => [$id => ['created_at' => $timestamp]],
        )->all());

        if ($minigame->matches()->where('status', MatchStatus::Completed)->exists()) {
            $this->rankings->rebuild($minigame->refresh());
        }

        return $minigame->refresh();
    }

    public function activate(Minigame $minigame): Minigame
    {
        if ($minigame->isActive()) {
            throw ValidationException::withMessages([
                'status' => 'This minigame is already active.',
            ]);
        }

        $minigame->status = MinigameStatus::Active;
        $minigame->save();

        return $minigame->refresh();
    }

    public function close(Minigame $minigame): Minigame
    {
        if ($minigame->isClosed()) {
            throw ValidationException::withMessages([
                'status' => 'This minigame is already closed.',
            ]);
        }

        $minigame->status = MinigameStatus::Closed;
        $minigame->save();

        return $minigame->refresh();
    }

    private function assertOpen(Minigame $minigame): void
    {
        if ($minigame->isClosed()) {
            throw ValidationException::withMessages([
                'minigame' => 'A closed minigame cannot be changed.',
            ]);
        }
    }

    private function assertRosterFitsFormat(Minigame $minigame): void
    {
        $minigame->load('members');

        foreach ($minigame->members as $member) {
            if (! $minigame->format->allows($member->gender)) {
                throw ValidationException::withMessages([
                    'format' => 'The current roster does not match the new format.',
                ]);
            }
        }
    }
}
