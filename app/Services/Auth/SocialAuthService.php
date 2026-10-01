<?php

namespace App\Services\Auth;

use App\Enums\UserRole;
use App\Enums\UserStatus;
use App\Models\SocialAccount;
use App\Models\User;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Support\Facades\DB;

class SocialAuthService
{
    public function login(SocialIdentity $identity): User
    {
        $user = DB::transaction(function () use ($identity) {
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
                    'name' => $identity->name,
                    'email' => $identity->emailVerified ? $identity->email : null,
                    'avatar' => $identity->avatar,
                    'role' => UserRole::Member,
                    'status' => UserStatus::Active,
                ]);
            } else {
                $user->name = $identity->name;
                $user->avatar = $identity->avatar ?? $user->avatar;

                if ($identity->email && $identity->emailVerified && $user->email === null) {
                    $emailTaken = User::query()
                        ->whereRaw('lower(email) = ?', [mb_strtolower($identity->email)])
                        ->whereKeyNot($user->id)
                        ->exists();

                    if (! $emailTaken) {
                        $user->email = $identity->email;
                    }
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

            return $user->refresh();
        });

        if ($user->status !== UserStatus::Active) {
            throw new AuthorizationException('This account is inactive.');
        }

        return $user;
    }
}
