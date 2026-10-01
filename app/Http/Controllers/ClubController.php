<?php

namespace App\Http\Controllers;

use App\Enums\JoinRequestStatus;
use App\Enums\UserRole;
use App\Http\Controllers\Concerns\ResolvesClubModels;
use App\Http\Requests\UpdateClubRequest;
use App\Services\ClubService;
use App\Support\Records;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Inertia\Inertia;
use Inertia\Response;

class ClubController extends Controller
{
    use ResolvesClubModels;

    public function edit(Request $request): Response
    {
        $club = $this->club($request);

        return Inertia::render('Settings/Club', [
            'club' => Records::club($club),
            'inviteUrl' => $request->user()->canManageClub()
                ? route('join.show', ['token' => $club->invite_token])
                : null,
        ]);
    }

    public function manage(Request $request): Response
    {
        abort_unless($request->user()->canManageClub(), 403);

        $club = $this->club($request);

        return Inertia::render('Manage/Index', [
            'clubName' => $club->name,
            'counts' => [
                'members' => $club->members()->count(),
                'pending_requests' => $club->joinRequests()->where('status', JoinRequestStatus::Pending)->count(),
                'courts' => $club->courts()->count(),
                'admins' => $club->memberships()->where('role', UserRole::Admin)->count(),
            ],
        ]);
    }

    public function update(UpdateClubRequest $request): RedirectResponse
    {
        $club = $this->club($request);
        $data = $request->validated();

        if (isset($data['name'])) {
            $data['slug'] = Str::slug($data['name']) ?: 'club';
        }

        $club->update($data);

        return to_route('settings');
    }

    public function invite(Request $request, ClubService $clubs): Response
    {
        abort_unless($request->user()->canManageClub(), 403);

        $club = $clubs->ensureInvite($this->club($request));

        return Inertia::render('Clubs/Invite', [
            'clubName' => $club->name,
            'inviteUrl' => route('join.show', ['token' => $club->invite_token]),
        ]);
    }

    public function rotateInvite(Request $request, ClubService $clubs): RedirectResponse
    {
        abort_unless($request->user()->isOwner(), 403);

        $clubs->rotateInvite($this->club($request));

        return to_route('invite');
    }
}
