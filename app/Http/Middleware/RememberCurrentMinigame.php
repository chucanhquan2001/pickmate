<?php

namespace App\Http\Middleware;

use App\Models\Minigame;
use App\Support\CurrentMinigame;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class RememberCurrentMinigame
{
    public function handle(Request $request, Closure $next): Response
    {
        $id = $request->route('minigame');
        $user = $request->user();

        if ($user !== null && is_numeric($id)) {
            $minigame = Minigame::query()
                ->where('club_id', $user->club_id)
                ->find((int) $id);

            if ($minigame !== null) {
                app(CurrentMinigame::class)->remember($request, $minigame);
            }
        }

        return $next($request);
    }
}
