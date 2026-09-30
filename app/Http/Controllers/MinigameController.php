<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Concerns\ResolvesClubModels;
use App\Http\Requests\StoreMinigameRequest;
use App\Http\Requests\SyncRosterRequest;
use App\Http\Requests\UpdateMinigameRequest;
use App\Http\Requests\UpdateMinigameRulesRequest;
use App\Models\Member;
use App\Models\Minigame;
use App\Services\MinigameService;
use App\Support\CurrentMinigame;
use App\Support\Records;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class MinigameController extends Controller
{
    use ResolvesClubModels;

    public function __construct(private MinigameService $minigames) {}

    public function index(Request $request): Response
    {
        $status = $request->query('status');

        $records = $this->club($request)->minigames()
            ->withCount(['members', 'matches'])
            ->when(is_string($status) && $status !== '', fn ($query) => $query->where('status', $status))
            ->orderByDesc('id')
            ->paginate(15)
            ->withQueryString()
            ->through(fn (Minigame $minigame) => Records::minigame($minigame));

        return Inertia::render('Minigames/Index', [
            'minigames' => $records,
        ]);
    }

    public function create(Request $request): Response
    {
        abort_unless($request->user()->canManageClub(), 403);

        return Inertia::render('Minigames/Create');
    }

    public function store(StoreMinigameRequest $request, CurrentMinigame $current): RedirectResponse
    {
        $minigame = $this->minigames->create($this->club($request), $request->user(), $request->validated());
        $current->remember($request, $minigame);

        return to_route('minigames.show', $minigame);
    }

    public function show(Request $request, int $minigame): Response
    {
        $record = $this->minigame($request, $minigame);
        $record->load('members')->loadCount(['members', 'matches']);

        return Inertia::render('Minigames/Show', [
            'minigame' => Records::minigame($record),
        ]);
    }

    public function update(UpdateMinigameRequest $request, int $minigame): RedirectResponse
    {
        $record = $this->minigames->update($this->minigame($request, $minigame), $request->validated());

        return to_route('minigames.show', $record);
    }

    public function editRoster(Request $request, int $minigame): Response
    {
        abort_unless($request->user()->canManageClub(), 403);

        $record = $this->minigame($request, $minigame);
        $record->load('members');
        $members = $this->club($request)->members()->orderBy('name')->get();

        return Inertia::render('Minigames/Roster', [
            'minigame' => Records::minigame($record),
            'members' => $members->map(fn (Member $member) => Records::member($member))->values(),
        ]);
    }

    public function roster(SyncRosterRequest $request, int $minigame): RedirectResponse
    {
        $record = $this->minigames->syncRoster(
            $this->minigame($request, $minigame),
            $request->validated('member_ids'),
        );

        return to_route('minigames.roster', $record);
    }

    public function editRules(Request $request, int $minigame): Response
    {
        abort_unless($request->user()->canManageClub(), 403);

        return Inertia::render('Minigames/Rules', [
            'minigame' => Records::minigame($this->minigame($request, $minigame)),
        ]);
    }

    public function rules(UpdateMinigameRulesRequest $request, int $minigame): RedirectResponse
    {
        $record = $this->minigames->updateRules($this->minigame($request, $minigame), $request->validated());

        return to_route('minigames.rules', $record);
    }

    public function activate(Request $request, int $minigame): RedirectResponse
    {
        abort_unless($request->user()->canManageClub(), 403);

        $record = $this->minigames->activate($this->minigame($request, $minigame));

        return to_route('minigames.show', $record);
    }

    public function close(Request $request, int $minigame): RedirectResponse
    {
        abort_unless($request->user()->canManageClub(), 403);

        $record = $this->minigames->close($this->minigame($request, $minigame));

        return to_route('minigames.show', $record);
    }

    public function select(Request $request, int $minigame, CurrentMinigame $current): RedirectResponse
    {
        $record = $this->minigame($request, $minigame);
        $current->remember($request, $record);

        return to_route('dashboard');
    }
}
