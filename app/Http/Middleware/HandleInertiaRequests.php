<?php

namespace App\Http\Middleware;

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

        return [
            ...parent::share($request),
            'auth' => [
                'user' => $user ? Records::user($user) : null,
            ],
            'currentMinigame' => $minigame ? Records::summary($minigame) : null,
        ];
    }
}
