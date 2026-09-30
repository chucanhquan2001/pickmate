<?php

namespace App\Http\Controllers;

use App\Enums\MinigameStatus;
use App\Http\Controllers\Concerns\ResolvesClubModels;
use App\Services\DashboardService;
use App\Support\CurrentMinigame;
use App\Support\Records;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class DashboardController extends Controller
{
    use ResolvesClubModels;

    public function __construct(private DashboardService $dashboard) {}

    public function index(Request $request, CurrentMinigame $current): Response
    {
        $club = $this->club($request);
        $selected = $current->resolve($request);

        if ($selected !== null && $selected->status === MinigameStatus::Active) {
            $selected->load('club');
            $payload = $this->dashboard->minigame($selected);
            $top = $selected->rankings()->with('member')->orderBy('rank')->limit(5)->get();

            return Inertia::render('Dashboard', [
                'minigames' => [],
                'selected' => [
                    'minigame' => Records::minigame($selected),
                    'roster_count' => $payload['roster_count'],
                    'matches_this_week' => $payload['matches_this_week'],
                    'matches_played' => $payload['matches_played'],
                    'today' => $payload['today']->map(fn ($match) => Records::match($match))->values(),
                    'upcoming' => $payload['upcoming']->map(fn ($match) => Records::match($match))->values(),
                    'top_rankings' => $top->map(fn ($ranking) => Records::ranking($ranking))->values(),
                    'recent_results' => $payload['recent_results']->map(fn ($match) => Records::match($match))->values(),
                ],
            ]);
        }

        return Inertia::render('Dashboard', [
            'minigames' => $this->dashboard->activeMinigames($club)
                ->map(fn ($minigame) => Records::minigame($minigame))
                ->values(),
            'selected' => null,
        ]);
    }

    public function clear(Request $request, CurrentMinigame $current): RedirectResponse
    {
        $current->forget($request);

        return to_route('dashboard');
    }
}
