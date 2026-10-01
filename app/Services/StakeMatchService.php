<?php

namespace App\Services;

use App\Enums\CourtStatus;
use App\Enums\MemberStatus;
use App\Enums\StakeFormat;
use App\Models\Club;
use App\Models\Court;
use App\Models\Member;
use App\Models\StakeMatch;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class StakeMatchService
{
    /**
     * @param  array{
     *     format: string,
     *     scoring_type: string,
     *     scheduled_at: string,
     *     court_id: int,
     *     item: string,
     *     quantity: int,
     *     expected_amount: int,
     *     team_1: list<int>,
     *     team_2: list<int>
     * }  $data
     */
    public function create(Club $club, User $actor, array $data): StakeMatch
    {
        $format = StakeFormat::from($data['format']);
        $team1 = array_map(intval(...), array_values($data['team_1']));
        $team2 = array_map(intval(...), array_values($data['team_2']));
        $perTeam = $format->playersPerTeam();

        if (count($team1) !== $perTeam || count($team2) !== $perTeam) {
            throw ValidationException::withMessages([
                'team_1' => $format === StakeFormat::Single
                    ? 'Đơn cần đúng 1 người mỗi đội.'
                    : 'Đôi cần đúng 2 người mỗi đội.',
            ]);
        }

        $ids = [...$team1, ...$team2];

        if (count($ids) !== count(array_unique($ids))) {
            throw ValidationException::withMessages([
                'team_1' => 'Một người không thể ở cả hai đội.',
            ]);
        }

        $court = Court::query()
            ->where('club_id', $club->id)
            ->where('status', CourtStatus::Active)
            ->find($data['court_id']);

        if ($court === null) {
            throw ValidationException::withMessages([
                'court_id' => 'Chọn sân đang mở của câu lạc bộ.',
            ]);
        }

        $members = Member::query()
            ->where('club_id', $club->id)
            ->where('status', MemberStatus::Active)
            ->whereIn('id', $ids)
            ->pluck('id');

        if ($members->count() !== count($ids)) {
            throw ValidationException::withMessages([
                'team_1' => 'Chỉ chọn thành viên đang chơi của câu lạc bộ.',
            ]);
        }

        return DB::transaction(function () use ($club, $actor, $data, $court, $format, $team1, $team2) {
            $match = StakeMatch::query()->create([
                'club_id' => $club->id,
                'court_id' => $court->id,
                'scheduled_at' => $data['scheduled_at'],
                'format' => $format,
                'scoring_type' => $data['scoring_type'],
                'item' => $data['item'],
                'quantity' => $data['quantity'],
                'expected_amount' => $data['expected_amount'],
                'created_by' => $actor->id,
            ]);

            $this->attach($match, $team1, 1);
            $this->attach($match, $team2, 2);

            return $match->load(['players.member', 'court']);
        });
    }

    /**
     * @param  array{team_1_score: int, team_2_score: int}  $data
     */
    public function recordScore(StakeMatch $match, array $data): StakeMatch
    {
        $match->team_1_score = (int) $data['team_1_score'];
        $match->team_2_score = (int) $data['team_2_score'];
        $match->save();

        return $match->load(['players.member', 'court']);
    }

    /**
     * @param  list<int>  $memberIds
     */
    private function attach(StakeMatch $match, array $memberIds, int $team): void
    {
        foreach (array_values($memberIds) as $index => $memberId) {
            $match->players()->create([
                'member_id' => $memberId,
                'team' => $team,
                'position' => $index + 1,
            ]);
        }
    }
}
