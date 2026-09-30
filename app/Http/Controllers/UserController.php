<?php

namespace App\Http\Controllers;

use App\Enums\UserRole;
use App\Http\Controllers\Concerns\ResolvesClubModels;
use App\Http\Requests\UpdateUserRoleRequest;
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

        $target->role = UserRole::from($request->validated('role'));
        $target->save();

        return to_route('settings');
    }
}
