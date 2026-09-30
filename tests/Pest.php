<?php

use App\Enums\SocialProvider;
use App\Enums\UserRole;
use App\Models\Club;
use App\Models\User;
use App\Services\Auth\SocialIdentity;
use Database\Seeders\ClubSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

pest()->extend(TestCase::class)
    ->use(RefreshDatabase::class)
    ->beforeEach(function () {
        $this->seed(ClubSeeder::class);
    })
    ->in('Feature');

pest()->extend(TestCase::class)->in('Unit');

function clubUser(UserRole $role = UserRole::Admin): User
{
    return User::factory()->create([
        'club_id' => Club::query()->firstOrFail()->id,
        'role' => $role,
    ]);
}

function socialIdentity(
    SocialProvider $provider,
    string $id,
    ?string $email,
    bool $verified = true,
    string $name = 'Nguyen Van A',
): SocialIdentity {
    return new SocialIdentity(
        provider: $provider,
        providerUserId: $id,
        email: $email,
        emailVerified: $verified,
        name: $name,
        avatar: 'https://example.com/avatar.png',
    );
}
