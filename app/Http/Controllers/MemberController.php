<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Concerns\ResolvesClubModels;
use App\Http\Requests\UpdateMemberRequest;
use App\Models\ClubMembership;
use App\Models\MatchGame;
use App\Models\Member;
use App\Support\Records;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class MemberController extends Controller
{
    use ResolvesClubModels;

    public function index(Request $request): Response
    {
        $search = trim((string) $request->query('search', ''));
        $status = $request->query('status');

        $members = $this->club($request)->members()
            ->withCount('minigames')
            ->when($search !== '', function ($query) use ($search) {
                $query->where(function ($query) use ($search) {
                    $query->where('name', 'like', "%{$search}%")
                        ->orWhere('nickname', 'like', "%{$search}%")
                        ->orWhere('email', 'like', "%{$search}%")
                        ->orWhere('phone', 'like', "%{$search}%");
                });
            })
            ->when(is_string($status) && $status !== '', fn ($query) => $query->where('status', $status))
            ->orderBy('name')
            ->paginate(15)
            ->withQueryString();

        $actor = $request->user();
        $memberships = ClubMembership::query()
            ->where('club_id', $actor->club_id)
            ->whereIn('user_id', $members->getCollection()->pluck('user_id')->filter())
            ->get()
            ->keyBy('user_id');

        $members->through(function (Member $member) use ($memberships, $actor) {
            $data = Records::member($member);
            $membership = $member->user_id === null ? null : $memberships->get($member->user_id);
            $data['account_role'] = $membership?->role?->value;
            $data['can_change_role'] = $membership !== null && $actor->canChangeMembershipRole($membership);

            return $data;
        });

        return Inertia::render('Members/Index', [
            'members' => $members,
            'filters' => [
                'search' => $search,
            ],
        ]);
    }

    public function show(Request $request, int $member): Response
    {
        $record = $this->member($request, $member);
        $record->load(['rankings.minigame']);
        $record->setRelation('history', MatchGame::query()
            ->whereHas('players', fn ($query) => $query->where('member_id', $record->id))
            ->with(['players.member', 'sets', 'court', 'minigame'])
            ->orderByDesc('scheduled_at')
            ->limit(50)
            ->get());

        return Inertia::render('Members/Show', [
            'member' => Records::memberDetail($record),
        ]);
    }

    public function update(UpdateMemberRequest $request, int $member): RedirectResponse
    {
        $record = $this->member($request, $member);
        $record->update($request->validated());

        return to_route('members.show', $record);
    }
}
