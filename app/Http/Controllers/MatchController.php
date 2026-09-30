<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Concerns\ResolvesClubModels;
use App\Http\Requests\StoreMatchRequest;
use App\Http\Requests\StoreMatchResultRequest;
use App\Http\Requests\UpdateMatchRequest;
use App\Models\Court;
use App\Models\MatchGame;
use App\Models\Member;
use App\Services\MatchService;
use App\Support\Records;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class MatchController extends Controller
{
    use ResolvesClubModels;

    public function __construct(private MatchService $matches) {}

    public function index(Request $request, int $minigame): Response
    {
        $record = $this->minigame($request, $minigame);
        $status = $request->query('status');

        $matches = $record->matches()
            ->with(['players.member', 'sets', 'court'])
            ->when(is_string($status) && $status !== '', fn ($query) => $query->where('status', $status))
            ->orderByDesc('scheduled_at')
            ->paginate(15)
            ->withQueryString()
            ->through(fn (MatchGame $match) => Records::match($match));

        return Inertia::render('Matches/Index', [
            'minigame' => Records::minigame($record),
            'matches' => $matches,
        ]);
    }

    public function create(Request $request, int $minigame): Response
    {
        abort_unless($request->user()->canManageClub(), 403);

        $record = $this->minigame($request, $minigame);

        return Inertia::render('Matches/Create', [
            'minigame' => Records::minigame($record),
            'roster' => $record->members()->orderBy('name')->get()->map(fn (Member $member) => Records::member($member))->values(),
            'courts' => $this->club($request)->courts()->where('status', 'active')->orderBy('code')->get()
                ->map(fn (Court $court) => Records::court($court))
                ->values(),
        ]);
    }

    public function store(StoreMatchRequest $request, int $minigame): RedirectResponse
    {
        $match = $this->matches->create(
            $this->minigame($request, $minigame),
            $request->user(),
            $request->validated(),
        );

        return to_route('matches.show', [$minigame, $match]);
    }

    public function show(Request $request, int $minigame, int $match): Response
    {
        $record = $this->minigame($request, $minigame);
        $game = $this->match($request, $minigame, $match)->load(['players.member', 'sets', 'court']);

        return Inertia::render('Matches/Show', [
            'minigame' => Records::minigame($record),
            'match' => Records::match($game),
            'courts' => $this->club($request)->courts()->where('status', 'active')->orderBy('code')->get()
                ->map(fn (Court $court) => Records::court($court))
                ->values(),
        ]);
    }

    public function update(UpdateMatchRequest $request, int $minigame, int $match): RedirectResponse
    {
        $this->matches->update(
            $this->minigame($request, $minigame),
            $this->match($request, $minigame, $match),
            $request->validated(),
        );

        return to_route('matches.show', [$minigame, $match]);
    }

    public function start(Request $request, int $minigame, int $match): RedirectResponse
    {
        abort_unless($request->user()->canManageClub(), 403);

        $this->matches->start(
            $this->minigame($request, $minigame),
            $this->match($request, $minigame, $match),
        );

        return to_route('matches.show', [$minigame, $match]);
    }

    public function cancel(Request $request, int $minigame, int $match): RedirectResponse
    {
        abort_unless($request->user()->canManageClub(), 403);

        $this->matches->cancel(
            $this->minigame($request, $minigame),
            $this->match($request, $minigame, $match),
        );

        return to_route('matches.show', [$minigame, $match]);
    }

    public function editResult(Request $request, int $minigame, int $match): Response
    {
        abort_unless($request->user()->canManageClub(), 403);

        $record = $this->minigame($request, $minigame);
        $game = $this->match($request, $minigame, $match)->load(['players.member', 'sets', 'court']);

        return Inertia::render('Matches/Result', [
            'minigame' => Records::minigame($record),
            'match' => Records::match($game),
        ]);
    }

    public function result(StoreMatchResultRequest $request, int $minigame, int $match): RedirectResponse
    {
        $this->matches->complete(
            $this->minigame($request, $minigame),
            $this->match($request, $minigame, $match),
            $request->validated('sets'),
        );

        return to_route('matches.show', [$minigame, $match]);
    }
}
