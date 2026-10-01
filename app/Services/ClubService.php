<?php

namespace App\Services;

use App\Enums\ClubStatus;
use App\Enums\JoinRequestStatus;
use App\Enums\MemberLevel;
use App\Enums\MemberStatus;
use App\Enums\UserRole;
use App\Models\Club;
use App\Models\ClubJoinRequest;
use App\Models\ClubMembership;
use App\Models\Member;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;

class ClubService
{
    /**
     * @param  array{name: string, nickname?: string|null, gender: string, level?: string|null}  $data
     */
    public function create(User $user, array $data): Club
    {
        return DB::transaction(function () use ($user, $data) {
            $club = Club::query()->create([
                'name' => $data['name'],
                'slug' => $this->uniqueSlug($data['name']),
                'timezone' => config('pickmate.club.timezone'),
                'language' => config('pickmate.club.language'),
                'status' => ClubStatus::Active,
                'default_score' => 11,
                'default_best_of' => 1,
                'default_participation_points' => 1,
                'default_win_points' => 3,
                'default_loss_points' => 0,
                'default_clean_win_bonus' => 1,
            ]);

            $club->memberships()->create([
                'user_id' => $user->id,
                'role' => UserRole::Owner,
            ]);

            $club->members()->create([
                'user_id' => $user->id,
                'name' => $user->name,
                'nickname' => $data['nickname'] ?? null,
                'avatar' => $user->avatar,
                'gender' => $data['gender'],
                'email' => $user->email,
                'level' => $data['level'] ?? MemberLevel::Beginner->value,
                'joined_at' => now()->toDateString(),
                'status' => MemberStatus::Active,
            ]);

            $user->club_id = $club->id;
            $user->save();

            return $club;
        });
    }

    public function switchTo(User $user, Club $club): void
    {
        $membership = ClubMembership::query()
            ->where('club_id', $club->id)
            ->where('user_id', $user->id)
            ->first();

        if ($membership === null) {
            abort(403);
        }

        $user->club_id = $club->id;
        $user->save();
    }

    public function rotateInvite(Club $club): Club
    {
        $club->invite_token = $this->token();
        $club->save();

        return $club;
    }

    /**
     * @param  array{gender: string, nickname?: string|null, level?: string|null}  $data
     */
    public function requestJoin(User $user, Club $club, array $data): ClubJoinRequest
    {
        return DB::transaction(function () use ($user, $club, $data) {
            $alreadyMember = ClubMembership::query()
                ->where('club_id', $club->id)
                ->where('user_id', $user->id)
                ->exists();

            if ($alreadyMember) {
                throw ValidationException::withMessages([
                    'join' => 'Bạn đã là thành viên của câu lạc bộ này.',
                ]);
            }

            $pending = ClubJoinRequest::query()
                ->where('club_id', $club->id)
                ->where('user_id', $user->id)
                ->where('status', JoinRequestStatus::Pending)
                ->first();

            if ($pending !== null) {
                return $pending;
            }

            return ClubJoinRequest::query()->create([
                'club_id' => $club->id,
                'user_id' => $user->id,
                'gender' => $data['gender'],
                'nickname' => $data['nickname'] ?? null,
                'level' => $data['level'] ?? MemberLevel::Beginner->value,
                'status' => JoinRequestStatus::Pending,
            ]);
        });
    }

    public function approve(User $reviewer, ClubJoinRequest $joinRequest): Member
    {
        $this->assertPending($reviewer, $joinRequest);

        return DB::transaction(function () use ($reviewer, $joinRequest) {
            $joinRequest->load('user');
            $applicant = $joinRequest->user;

            ClubMembership::query()->firstOrCreate(
                [
                    'club_id' => $joinRequest->club_id,
                    'user_id' => $applicant->id,
                ],
                [
                    'role' => UserRole::Member,
                ],
            );

            $member = $this->placeMember($applicant, $joinRequest);

            $joinRequest->update([
                'status' => JoinRequestStatus::Approved,
                'reviewed_by' => $reviewer->id,
                'reviewed_at' => now(),
            ]);

            if ($applicant->club_id === null) {
                $applicant->club_id = $joinRequest->club_id;
                $applicant->save();
            }

            return $member;
        });
    }

    public function reject(User $reviewer, ClubJoinRequest $joinRequest): void
    {
        $this->assertPending($reviewer, $joinRequest);

        $joinRequest->update([
            'status' => JoinRequestStatus::Rejected,
            'reviewed_by' => $reviewer->id,
            'reviewed_at' => now(),
        ]);
    }

    private function assertPending(User $reviewer, ClubJoinRequest $joinRequest): void
    {
        if ($joinRequest->club_id !== $reviewer->club_id) {
            abort(404);
        }

        if ($joinRequest->status !== JoinRequestStatus::Pending) {
            throw ValidationException::withMessages([
                'join' => 'Lời xin này đã được xử lý.',
            ]);
        }
    }

    private function placeMember(User $applicant, ClubJoinRequest $joinRequest): Member
    {
        $member = Member::query()
            ->where('club_id', $joinRequest->club_id)
            ->where('user_id', $applicant->id)
            ->first();

        if ($member === null && is_string($applicant->email) && $applicant->email !== '') {
            $member = Member::query()
                ->where('club_id', $joinRequest->club_id)
                ->whereRaw('lower(email) = ?', [mb_strtolower($applicant->email)])
                ->first();
        }

        if ($member !== null && $member->user_id !== null && $member->user_id !== $applicant->id) {
            throw ValidationException::withMessages([
                'join' => 'Email này đã thuộc một thành viên khác.',
            ]);
        }

        $attributes = [
            'user_id' => $applicant->id,
            'name' => $applicant->name,
            'nickname' => $joinRequest->nickname,
            'avatar' => $applicant->avatar,
            'gender' => $joinRequest->gender,
            'email' => $applicant->email,
            'level' => $joinRequest->level,
            'status' => MemberStatus::Active,
        ];

        if ($member === null) {
            return Member::query()->create([
                ...$attributes,
                'club_id' => $joinRequest->club_id,
                'joined_at' => now()->toDateString(),
            ]);
        }

        if ($member->joined_at === null) {
            $attributes['joined_at'] = now()->toDateString();
        }

        $member->fill($attributes);
        $member->save();

        return $member;
    }

    private function uniqueSlug(string $name): string
    {
        $base = Str::slug($name) ?: 'club';
        $slug = $base;
        $suffix = 2;

        while (Club::query()->where('slug', $slug)->exists()) {
            $slug = $base.'-'.$suffix;
            $suffix++;
        }

        return $slug;
    }

    private function token(): string
    {
        do {
            $token = Str::random(48);
        } while (Club::query()->where('invite_token', $token)->exists());

        return $token;
    }
}
