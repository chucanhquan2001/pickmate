<?php

use App\Enums\UserRole;
use App\Models\Member;
use App\Models\SocialAccount;
use App\Models\User;
use Illuminate\Support\Facades\RateLimiter;
use Laravel\Socialite\Facades\Socialite;
use Laravel\Socialite\Two\User as SocialiteUser;

it('signs in the owner from google and ends the session on logout', function () {
    Socialite::fake('google', SocialiteUser::fake([
        'id' => 'google-owner',
        'name' => 'Owner',
        'email' => 'owner@example.com',
        'email_verified' => true,
        'avatar' => 'https://example.com/avatar.png',
    ]));

    $this->get('/login')->assertInertia(fn ($page) => $page->component('Login'));
    $this->get('/auth/google')->assertRedirect('https://socialite.fake/google/authorize');
    $this->get('/auth/google/callback')->assertRedirect(route('clubs.index'));

    $user = User::query()->where('email', 'owner@example.com')->first();

    expect($user)->not->toBeNull()
        ->and($user->role)->toBe(UserRole::Member)
        ->and($user->club_id)->toBeNull()
        ->and($user->memberships)->toHaveCount(0);

    $this->assertAuthenticatedAs($user);

    $this->get('/dashboard')->assertRedirect(route('clubs.index'));
    $this->get('/clubs')->assertInertia(fn ($page) => $page
        ->component('Clubs/Index')
        ->where('auth.user.role', 'member'));

    $this->post('/logout')->assertRedirect(route('login'));
    $this->assertGuest();
    $this->get('/dashboard')->assertRedirect(route('login'));
});

it('links a second provider to the same verified email', function () {
    Socialite::fake('google', SocialiteUser::fake([
        'id' => 'google-1',
        'email' => 'owner@example.com',
        'name' => 'Owner',
        'email_verified' => true,
    ]));

    $this->get('/auth/google/callback')->assertRedirect(route('clubs.index'));

    Socialite::fake('facebook', SocialiteUser::fake([
        'id' => 'facebook-1',
        'email' => 'owner@example.com',
        'name' => 'Owner Facebook',
    ]));

    $this->get('/auth/facebook/callback')->assertRedirect(route('clubs.index'));

    expect(User::query()->count())->toBe(1)
        ->and(SocialAccount::query()->count())->toBe(2)
        ->and(User::query()->first()->role)->toBe(UserRole::Member);
});

it('does not merge an unverified email into an existing user', function () {
    Socialite::fake('google', SocialiteUser::fake([
        'id' => 'google-1',
        'email' => 'shared@example.com',
        'email_verified' => true,
    ]));
    $this->get('/auth/google/callback')->assertRedirect(route('clubs.index'));

    Socialite::fake('google', SocialiteUser::fake([
        'id' => 'google-2',
        'email' => 'shared@example.com',
        'name' => 'Other',
        'email_verified' => false,
    ]));
    $this->get('/auth/google/callback')->assertRedirect(route('clubs.index'));

    expect(User::query()->count())->toBe(2)
        ->and(User::query()->where('name', 'Other')->first()->role)->toBe(UserRole::Member);
});

it('does not join a club or link a member just by logging in', function () {
    $member = Member::factory()->create([
        'club_id' => 1,
        'email' => 'player@example.com',
        'name' => 'Player',
    ]);

    Socialite::fake('google', SocialiteUser::fake([
        'id' => 'google-player',
        'email' => 'player@example.com',
        'name' => 'Player',
        'email_verified' => true,
    ]));

    $this->get('/auth/google/callback')->assertRedirect(route('clubs.index'));

    $user = User::query()->where('email', 'player@example.com')->first();

    expect($user->club_id)->toBeNull()
        ->and($user->memberships)->toHaveCount(0)
        ->and($member->refresh()->user_id)->toBeNull();
});

it('rejects a social login that cannot be verified', function () {
    Socialite::fake('google', function () {
        throw new RuntimeException('Google token is invalid.');
    });

    $this->get('/auth/google/callback')
        ->assertRedirect(route('login'))
        ->assertSessionHasErrors('auth');
});

it('throttles repeated login attempts', function () {
    RateLimiter::clear('social-login');
    Socialite::fake('google', function () {
        throw new RuntimeException('Google token is invalid.');
    });

    for ($attempt = 0; $attempt < 5; $attempt++) {
        $this->get('/auth/google/callback')->assertRedirect(route('login'));
    }

    $this->get('/auth/google/callback')->assertTooManyRequests();
});
