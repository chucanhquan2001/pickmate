<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Concerns\ResolvesClubModels;
use App\Http\Requests\UpdateClubRequest;
use App\Models\ClubMembership;
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
        $users = $request->user()->isOwner()
            ? ClubMembership::query()
                ->where('club_id', $club->id)
                ->with('user')
                ->orderBy('id')
                ->get()
                ->map(fn (ClubMembership $membership) => Records::user($membership->user, $membership->role))
                ->values()
            : [];

        return Inertia::render('Settings/Club', [
            'club' => Records::club($club),
            'users' => $users,
            'inviteUrl' => $request->user()->canManageClub()
                ? route('join.show', ['token' => $club->invite_token])
                : null,
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

    public function rotateInvite(Request $request, ClubService $clubs): RedirectResponse
    {
        abort_unless($request->user()->isOwner(), 403);

        $clubs->rotateInvite($this->club($request));

        return to_route('settings');
    }
}
