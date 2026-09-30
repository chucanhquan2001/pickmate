<?php

namespace App\Services\Auth;

use App\Enums\ClubStatus;
use App\Enums\UserRole;
use App\Enums\UserStatus;
use App\Models\Club;
use App\Models\Member;
use App\Models\SocialAccount;
use App\Models\User;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Support\Facades\DB;
use RuntimeException;

class SocialAuthService
{
    public function login(SocialIdentity $identity): User
    {
        $club = Club::query()->where('status', ClubStatus::Active)->orderBy('id')->first();

        if ($club === null) {
            throw new RuntimeException('No club is configured. Run the club seeder.');
        }

        $user = DB::transaction(function () use ($identity, $club) {
            $account = SocialAccount::query()
                ->where('provider', $identity->provider)
                ->where('provider_user_id', $identity->providerUserId)
                ->first();

            $user = $account?->user;

            if ($user === null && $identity->email && $identity->emailVerified) {
                $user = User::query()
                    ->whereRaw('lower(email) = ?', [mb_strtolower($identity->email)])
                    ->first();
            }

            if ($user === null) {
                $user = User::query()->create([
                    'club_id' => $club->id,
                    'name' => $identity->name,
                    'email' => $identity->emailVerified ? $identity->email : null,
                    'avatar' => $identity->avatar,
                    'role' => $this->isOwnerEmail($identity) ? UserRole::Owner : UserRole::Member,
                    'status' => UserStatus::Active,
                ]);
            } else {
                $user->name = $identity->name;
                $user->avatar = $identity->avatar ?? $user->avatar;
                $user->club_id ??= $club->id;

                if ($identity->email && $identity->emailVerified && $user->email === null) {
                    $emailTaken = User::query()
                        ->whereRaw('lower(email) = ?', [mb_strtolower($identity->email)])
                        ->whereKeyNot($user->id)
                        ->exists();

                    if (! $emailTaken) {
                        $user->email = $identity->email;
                    }
                }

                if ($this->isOwnerEmail($identity)) {
                    $user->role = UserRole::Owner;
                }

                $user->save();
            }

            if ($account === null) {
                $user->socialAccounts()->create([
                    'provider' => $identity->provider,
                    'provider_user_id' => $identity->providerUserId,
                    'email' => $identity->email,
                ]);
            } else {
                $account->update(['email' => $identity->email]);
            }

            $this->linkMember($user, $identity);

            return $user->refresh();
        });

        if ($user->status !== UserStatus::Active) {
            throw new AuthorizationException('This account is inactive.');
        }

        return $user;
    }

    private function isOwnerEmail(SocialIdentity $identity): bool
    {
        $ownerEmail = config('pickmate.owner_email');

        return is_string($ownerEmail)
            && $ownerEmail !== ''
            && $identity->email !== null
            && $identity->emailVerified
            && strcasecmp($identity->email, $ownerEmail) === 0;
    }

    private function linkMember(User $user, SocialIdentity $identity): void
    {
        if ($user->club_id === null || ! $identity->email || ! $identity->emailVerified) {
            return;
        }

        $member = Member::query()
            ->where('club_id', $user->club_id)
            ->whereNull('user_id')
            ->whereRaw('lower(email) = ?', [mb_strtolower($identity->email)])
            ->orderBy('id')
            ->first();

        $member?->update(['user_id' => $user->id]);
    }
}
