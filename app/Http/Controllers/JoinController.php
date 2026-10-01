<?php

namespace App\Http\Controllers;

use App\Enums\ClubStatus;
use App\Enums\JoinRequestStatus;
use App\Http\Requests\StoreJoinRequest;
use App\Models\Club;
use App\Models\ClubJoinRequest;
use App\Models\ClubMembership;
use App\Services\ClubService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class JoinController extends Controller
{
    public function show(Request $request, string $token): Response
    {
        $club = $this->clubForToken($token);
        $user = $request->user();

        $isMember = ClubMembership::query()
            ->where('club_id', $club->id)
            ->where('user_id', $user->id)
            ->exists();

        $pending = ClubJoinRequest::query()
            ->where('club_id', $club->id)
            ->where('user_id', $user->id)
            ->where('status', JoinRequestStatus::Pending)
            ->exists();

        return Inertia::render('Clubs/Join', [
            'target' => [
                'id' => $club->id,
                'name' => $club->name,
            ],
            'token' => $token,
            'state' => $isMember ? 'member' : ($pending ? 'pending' : 'open'),
        ]);
    }

    public function store(StoreJoinRequest $request, string $token, ClubService $clubs): RedirectResponse
    {
        $club = $this->clubForToken($token);
        $clubs->requestJoin($request->user(), $club, $request->validated());

        return to_route('join.show', ['token' => $token]);
    }

    private function clubForToken(string $token): Club
    {
        return Club::query()
            ->where('invite_token', $token)
            ->where('status', ClubStatus::Active)
            ->firstOrFail();
    }
}
