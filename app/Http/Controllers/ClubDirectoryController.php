<?php

namespace App\Http\Controllers;

use App\Enums\JoinRequestStatus;
use App\Enums\UserStatus;
use App\Http\Requests\StoreClubRequest;
use App\Models\Club;
use App\Models\ClubJoinRequest;
use App\Models\ClubMembership;
use App\Services\ClubService;
use App\Support\CurrentMinigame;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class ClubDirectoryController extends Controller
{
    public function index(Request $request): Response
    {
        $user = $request->user();

        $memberships = ClubMembership::query()
            ->where('user_id', $user->id)
            ->with('club')
            ->orderBy('club_id')
            ->get()
            ->map(fn (ClubMembership $membership) => [
                'club_id' => $membership->club_id,
                'name' => $membership->club->name,
                'role' => $membership->role->value,
                'current' => $membership->club_id === $user->club_id,
            ])
            ->values();

        $pendingRequests = ClubJoinRequest::query()
            ->where('user_id', $user->id)
            ->where('status', JoinRequestStatus::Pending)
            ->with('club')
            ->orderByDesc('id')
            ->get()
            ->map(fn (ClubJoinRequest $joinRequest) => [
                'id' => $joinRequest->id,
                'club_name' => $joinRequest->club->name,
            ])
            ->values();

        return Inertia::render('Clubs/Index', [
            'memberships' => $memberships,
            'pendingRequests' => $pendingRequests,
        ]);
    }

    public function store(StoreClubRequest $request, ClubService $clubs, CurrentMinigame $current): RedirectResponse
    {
        $clubs->create($request->user(), $request->validated());
        $current->forget($request);

        return to_route('dashboard');
    }

    public function switch(Request $request, Club $club, ClubService $clubs, CurrentMinigame $current): RedirectResponse
    {
        abort_unless($request->user()->status === UserStatus::Active, 403);

        $clubs->switchTo($request->user(), $club);
        $current->forget($request);

        return to_route('dashboard');
    }
}
