<?php

namespace App\Http\Controllers;

use App\Enums\UserRole;
use App\Http\Controllers\Concerns\ResolvesClubModels;
use App\Http\Requests\UpdateUserRoleRequest;
use App\Models\ClubMembership;
use Illuminate\Http\RedirectResponse;
use Illuminate\Validation\ValidationException;

class UserController extends Controller
{
    use ResolvesClubModels;

    public function update(UpdateUserRoleRequest $request, int $user): RedirectResponse
    {
        $target = $this->clubUser($request, $user);

        if ($target->id === $request->user()->id) {
            throw ValidationException::withMessages([
                'role' => 'You cannot change your own role.',
            ]);
        }

        $membership = ClubMembership::query()
            ->where('club_id', $request->user()->club_id)
            ->where('user_id', $target->id)
            ->firstOrFail();

        $role = UserRole::from($request->validated('role'));

        if ($membership->role === UserRole::Owner) {
            $owners = ClubMembership::query()
                ->where('club_id', $membership->club_id)
                ->where('role', UserRole::Owner)
                ->count();

            if ($owners <= 1) {
                throw ValidationException::withMessages([
                    'role' => 'Câu lạc bộ cần ít nhất một chủ.',
                ]);
            }
        }

        $membership->role = $role;
        $membership->save();

        return to_route('settings');
    }
}
