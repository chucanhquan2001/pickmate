<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Concerns\ResolvesClubModels;
use App\Models\Ranking;
use App\Support\Records;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class RankingController extends Controller
{
    use ResolvesClubModels;

    public function index(Request $request, int $minigame): Response
    {
        $record = $this->minigame($request, $minigame);
        $rankings = $record->rankings()->with('member')->orderBy('rank')->get();

        return Inertia::render('Rankings/Index', [
            'minigame' => Records::minigame($record),
            'rankings' => $rankings->map(fn (Ranking $ranking) => Records::ranking($ranking))->values(),
        ]);
    }
}
