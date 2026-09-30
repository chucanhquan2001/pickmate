<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Concerns\ResolvesClubModels;
use App\Http\Requests\UpdateClubRequest;
use App\Models\User;
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
            ? User::query()->where('club_id', $club->id)->orderBy('name')->get()->map(fn (User $user) => Records::user($user))->values()
            : [];

        return Inertia::render('Settings/Club', [
            'club' => Records::club($club),
            'users' => $users,
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
}
