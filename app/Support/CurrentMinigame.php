<?php

namespace App\Support;

use App\Models\Minigame;
use Illuminate\Http\Request;

class CurrentMinigame
{
    public const SESSION_KEY = 'current_minigame_id';

    public function remember(Request $request, Minigame $minigame): void
    {
        $request->session()->put(self::SESSION_KEY, $minigame->id);
    }

    public function forget(Request $request): void
    {
        $request->session()->forget(self::SESSION_KEY);
    }

    public function resolve(Request $request): ?Minigame
    {
        $user = $request->user();
        $id = $request->session()->get(self::SESSION_KEY);

        if ($user === null || $user->club_id === null || ! is_numeric($id)) {
            return null;
        }

        return Minigame::query()
            ->where('club_id', $user->club_id)
            ->find((int) $id);
    }
}
