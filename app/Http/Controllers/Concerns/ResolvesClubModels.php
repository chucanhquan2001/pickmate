<?php

namespace App\Http\Controllers\Concerns;

use App\Models\Club;
use App\Models\Court;
use App\Models\MatchGame;
use App\Models\Member;
use App\Models\Minigame;
use App\Models\User;
use Illuminate\Http\Request;

trait ResolvesClubModels
{
    protected function club(Request $request): Club
    {
        return Club::query()->findOrFail($request->user()->club_id);
    }

    protected function member(Request $request, int $member): Member
    {
        return Member::query()
            ->where('club_id', $request->user()->club_id)
            ->findOrFail($member);
    }

    protected function court(Request $request, int $court): Court
    {
        return Court::query()
            ->where('club_id', $request->user()->club_id)
            ->findOrFail($court);
    }

    protected function minigame(Request $request, int $minigame): Minigame
    {
        return Minigame::query()
            ->where('club_id', $request->user()->club_id)
            ->findOrFail($minigame);
    }

    protected function match(Request $request, int $minigame, int $match): MatchGame
    {
        $minigameModel = $this->minigame($request, $minigame);

        return MatchGame::query()
            ->where('minigame_id', $minigameModel->id)
            ->findOrFail($match);
    }

    protected function clubUser(Request $request, int $user): User
    {
        return User::query()
            ->where('club_id', $request->user()->club_id)
            ->findOrFail($user);
    }
}
