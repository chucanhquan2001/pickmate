<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Concerns\ResolvesClubModels;
use App\Http\Requests\StoreStakeMatchRequest;
use App\Http\Requests\UpdateStakeScoreRequest;
use App\Models\Court;
use App\Models\Member;
use App\Models\StakeMatch;
use App\Services\StakeMatchService;
use App\Support\Records;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class StakeMatchController extends Controller
{
    use ResolvesClubModels;

    public function __construct(private StakeMatchService $stakes) {}

    public function index(Request $request): Response
    {
        $memberId = $this->actorMemberId($request);

        $matches = StakeMatch::query()
            ->where('club_id', $request->user()->club_id)
            ->when(
                $memberId === null,
                fn ($query) => $query->whereRaw('0 = 1'),
                fn ($query) => $query->whereHas('players', fn ($players) => $players->where('member_id', $memberId)),
            )
            ->with(['players.member', 'court'])
            ->orderByDesc('scheduled_at')
            ->paginate(15)
            ->withQueryString()
            ->through(fn (StakeMatch $match) => Records::stakeMatch($match));

        return Inertia::render('Keo/Index', [
            'matches' => $matches,
        ]);
    }

    public function create(Request $request): Response
    {
        $club = $this->club($request);

        return Inertia::render('Keo/Create', [
            'members' => $club->members()->where('status', 'active')->orderBy('name')->get()
                ->map(fn (Member $member) => Records::member($member))
                ->values(),
            'courts' => $club->courts()->where('status', 'active')->orderBy('code')->get()
                ->map(fn (Court $court) => Records::court($court))
                ->values(),
        ]);
    }

    public function store(StoreStakeMatchRequest $request): RedirectResponse
    {
        $club = $this->club($request);
        $data = $request->validated();
        $match = $this->stakes->create($club, $request->user(), $data);
        $memberId = $this->actorMemberId($request);
        $playing = $memberId !== null && in_array($memberId, [
            ...array_map(intval(...), $data['team_1']),
            ...array_map(intval(...), $data['team_2']),
        ], true);

        return $playing
            ? to_route('keo.show', $match)
            : to_route('keo.index');
    }

    public function score(UpdateStakeScoreRequest $request, int $stakeMatch): RedirectResponse
    {
        $match = $this->visibleStake($request, $stakeMatch);
        $this->stakes->recordScore($match, $request->validated());

        return to_route('keo.show', $match);
    }

    public function show(Request $request, int $stakeMatch): Response
    {
        return Inertia::render('Keo/Show', [
            'match' => Records::stakeMatch($this->visibleStake($request, $stakeMatch)),
        ]);
    }

    private function visibleStake(Request $request, int $stakeMatch): StakeMatch
    {
        $memberId = $this->actorMemberId($request);

        return StakeMatch::query()
            ->where('club_id', $request->user()->club_id)
            ->whereHas('players', fn ($players) => $players->where('member_id', $memberId ?? 0))
            ->with(['players.member', 'court'])
            ->findOrFail($stakeMatch);
    }

    private function actorMemberId(Request $request): ?int
    {
        $id = Member::query()
            ->where('club_id', $request->user()->club_id)
            ->where('user_id', $request->user()->id)
            ->value('id');

        return $id === null ? null : (int) $id;
    }
}
