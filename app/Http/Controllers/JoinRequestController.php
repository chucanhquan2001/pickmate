<?php

namespace App\Http\Controllers;

use App\Enums\JoinRequestStatus;
use App\Models\ClubJoinRequest;
use App\Services\ClubService;
use App\Support\Records;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class JoinRequestController extends Controller
{
    public function index(Request $request): Response
    {
        abort_unless($request->user()->canManageClub(), 403);

        $requests = ClubJoinRequest::query()
            ->where('club_id', $request->user()->club_id)
            ->where('status', JoinRequestStatus::Pending)
            ->with('user')
            ->orderBy('id')
            ->get()
            ->map(fn (ClubJoinRequest $joinRequest) => Records::joinRequest($joinRequest))
            ->values();

        return Inertia::render('Clubs/Requests', [
            'requests' => $requests,
        ]);
    }

    public function approve(Request $request, ClubJoinRequest $joinRequest, ClubService $clubs): RedirectResponse
    {
        abort_unless($request->user()->canManageClub(), 403);
        $clubs->approve($request->user(), $joinRequest);

        return to_route('members.index');
    }

    public function reject(Request $request, ClubJoinRequest $joinRequest, ClubService $clubs): RedirectResponse
    {
        abort_unless($request->user()->canManageClub(), 403);
        $clubs->reject($request->user(), $joinRequest);

        return to_route('join-requests.index');
    }
}
