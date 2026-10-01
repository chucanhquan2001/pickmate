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

        $membership = ClubMembership::query()
            ->where('club_id', $request->user()->club_id)
            ->where('user_id', $target->id)
            ->firstOrFail();

        if (! $request->user()->canChangeMembershipRole($membership)) {
            throw ValidationException::withMessages([
                'role' => match (true) {
                    $target->id === $request->user()->id => 'Bạn không thể đổi quyền của chính mình.',
                    $membership->role === UserRole::Owner => 'Không đổi được quyền của chủ CLB.',
                    default => 'Chỉ người tạo CLB đổi được quyền quản trị viên.',
                },
            ]);
        }

        $membership->role = UserRole::from($request->validated('role'));
        $membership->save();

        return to_route('members.index');
    }
}
