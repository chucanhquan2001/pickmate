<?php

namespace App\Http\Middleware;

use App\Enums\JoinRequestStatus;
use App\Support\CurrentMinigame;
use App\Support\Records;
use Illuminate\Http\Request;
use Inertia\Middleware;

class HandleInertiaRequests extends Middleware
{
    /**
     * @var string
     */
    protected $rootView = 'app';

    /**
     * @return array<string, mixed>
     */
    public function share(Request $request): array
    {
        $user = $request->user();
        $minigame = $user ? app(CurrentMinigame::class)->resolve($request) : null;
        $club = $user?->club_id ? $user->club : null;

        return [
            ...parent::share($request),
            'auth' => [
                'user' => $user ? Records::user($user) : null,
            ],
            'club' => $club ? [
                'id' => $club->id,
                'name' => $club->name,
            ] : null,
            'currentMinigame' => $minigame ? Records::summary($minigame) : null,
            'pendingJoinRequests' => $user && $club && $user->canManageClub()
                ? $club->joinRequests()->where('status', JoinRequestStatus::Pending)->count()
                : 0,
        ];
    }
}
