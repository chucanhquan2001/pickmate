<?php

use App\Enums\JoinRequestStatus;
use App\Enums\UserRole;
use App\Models\Club;
use App\Models\ClubJoinRequest;
use App\Models\ClubMembership;
use App\Models\Member;
use App\Models\Minigame;
use App\Models\User;
use Inertia\Testing\AssertableInertia as Assert;
use Laravel\Socialite\Facades\Socialite;
use Laravel\Socialite\Two\User as SocialiteUser;

it('rejects a skill rating that is not one decimal place', function () {
    $user = User::factory()->create([
        'club_id' => null,
        'role' => UserRole::Member,
    ]);

    $this->actingAs($user)->post('/clubs', [
        'name' => 'CLB Sai',
        'gender' => 'male',
        'dupr_rating' => '2',
        'spcn_rating' => '2.50',
    ])->assertSessionHasErrors(['dupr_rating', 'spcn_rating']);

    expect(Club::query()->where('name', 'CLB Sai')->exists())->toBeFalse();
});

it('creates a private club and makes the creator its owner and member', function () {
    $user = User::factory()->create([
        'club_id' => null,
        'role' => UserRole::Member,
    ]);

    $this->actingAs($user)->post('/clubs', [
        'name' => 'CLB Rieng',
        'nickname' => 'Ri',
        'gender' => 'female',
        'dupr_rating' => '5.0',
        'spcn_rating' => '4.5',
    ])->assertRedirect(route('dashboard'));

    $user->refresh();
    $club = Club::query()->findOrFail($user->club_id);

    $member = Member::query()->where('club_id', $club->id)->where('user_id', $user->id)->firstOrFail();

    expect($user->isOwner())->toBeTrue()
        ->and($club->name)->toBe('CLB Rieng')
        ->and($club->invite_token)->not->toBeEmpty()
        ->and($member->gender->value)->toBe('female')
        ->and($member->dupr_rating)->toBe('5.0')
        ->and($member->spcn_rating)->toBe('4.5');

    $this->actingAs($user)->get('/minigames/create')
        ->assertOk()
        ->assertInertia(fn (Assert $page) => $page
            ->component('Minigames/Create')
            ->where('club.name', 'CLB Rieng'));
});

it('keeps a join request pending until an owner approves it', function () {
    $owner = clubUser(UserRole::Owner);
    $club = Club::query()->firstOrFail();
    $applicant = User::factory()->create([
        'club_id' => null,
        'name' => 'Nguoi Xin',
        'email' => 'xin@example.com',
        'role' => UserRole::Member,
    ]);

    $this->actingAs($applicant)->post('/join/'.$club->invite_token, [
        'gender' => 'male',
        'nickname' => 'Xin',
        'dupr_rating' => '2.0',
        'spcn_rating' => '2.5',
    ])->assertRedirect(route('join.show', ['token' => $club->invite_token]));

    expect(Member::query()->where('user_id', $applicant->id)->exists())->toBeFalse()
        ->and(ClubMembership::query()->where('user_id', $applicant->id)->exists())->toBeFalse();

    $joinRequest = ClubJoinRequest::query()->where('user_id', $applicant->id)->firstOrFail();

    $this->actingAs($applicant)->get('/join/'.$club->invite_token)
        ->assertOk()
        ->assertInertia(fn (Assert $page) => $page
            ->component('Clubs/Join')
            ->where('state', 'pending'));

    $this->actingAs($owner)->get('/dashboard')
        ->assertOk()
        ->assertInertia(fn (Assert $page) => $page->where('pendingJoinRequests', 1));

    $this->actingAs($owner)->post("/join-requests/{$joinRequest->id}/approve")
        ->assertRedirect(route('members.index'));

    $applicant->refresh();
    $member = Member::query()->where('user_id', $applicant->id)->firstOrFail();

    expect($joinRequest->refresh()->status)->toBe(JoinRequestStatus::Approved)
        ->and($member->club_id)->toBe($club->id)
        ->and($member->name)->toBe('Nguoi Xin')
        ->and($member->nickname)->toBe('Xin')
        ->and($member->dupr_rating)->toBe('2.0')
        ->and($member->spcn_rating)->toBe('2.5')
        ->and($applicant->club_id)->toBe($club->id)
        ->and(ClubMembership::query()->where('user_id', $applicant->id)->where('club_id', $club->id)->first()->role)->toBe(UserRole::Member);
});

it('rejects a join request without creating a member and allows another request', function () {
    $owner = clubUser(UserRole::Owner);
    $club = Club::query()->firstOrFail();
    $applicant = User::factory()->create([
        'club_id' => null,
        'role' => UserRole::Member,
    ]);

    $this->actingAs($applicant)->post('/join/'.$club->invite_token, [
        'gender' => 'male',
        'dupr_rating' => '3.0',
        'spcn_rating' => '3.0',
    ])->assertRedirect();

    $joinRequest = ClubJoinRequest::query()->where('user_id', $applicant->id)->firstOrFail();

    $this->actingAs($owner)->post("/join-requests/{$joinRequest->id}/reject")
        ->assertRedirect(route('join-requests.index'));

    expect($joinRequest->refresh()->status)->toBe(JoinRequestStatus::Rejected)
        ->and(Member::query()->where('user_id', $applicant->id)->exists())->toBeFalse();

    $this->actingAs($applicant)->post('/join/'.$club->invite_token, [
        'gender' => 'female',
        'dupr_rating' => '3.5',
        'spcn_rating' => '3.5',
    ])->assertRedirect();

    expect(ClubJoinRequest::query()->where('user_id', $applicant->id)->where('status', JoinRequestStatus::Pending)->exists())->toBeTrue()
        ->and(Member::query()->where('user_id', $applicant->id)->exists())->toBeFalse();
});

it('sends a guest who opens an invite back to that invite after login', function () {
    $club = Club::query()->firstOrFail();

    $this->get('/join/'.$club->invite_token)->assertRedirect(route('login'));

    Socialite::fake('google', SocialiteUser::fake([
        'id' => 'google-guest',
        'email' => 'guest@example.com',
        'name' => 'Guest',
        'email_verified' => true,
    ]));

    $this->get('/auth/google/callback')
        ->assertRedirect(route('join.show', ['token' => $club->invite_token]));
});

it('switches the active club and only offers that club\'s members to a minigame', function () {
    $owner = clubUser(UserRole::Owner);
    $home = Club::query()->firstOrFail();
    $homeMember = Member::factory()->create([
        'club_id' => $home->id,
        'name' => 'Nguoi Nha',
        'gender' => 'male',
    ]);
    $homeGame = Minigame::factory()->create([
        'club_id' => $home->id,
        'name' => 'Game Nha',
    ]);

    $this->actingAs($owner)->post('/clubs', [
        'name' => 'CLB Hai',
        'gender' => 'male',
        'dupr_rating' => '2.0',
        'spcn_rating' => '2.0',
    ])->assertRedirect(route('dashboard'));

    $owner->refresh();
    $away = Club::query()->findOrFail($owner->club_id);

    expect($away->id)->not->toBe($home->id);

    $this->actingAs($owner)->get("/minigames/{$homeGame->id}")->assertNotFound();
    $this->actingAs($owner)->get('/members?search=Nguoi Nha')
        ->assertOk()
        ->assertInertia(fn (Assert $page) => $page->where('members.data', []));

    $this->actingAs($owner)->post('/minigames', [
        'name' => 'Game Hai',
        'format' => 'double_male',
    ])->assertRedirect();

    $awayGame = Minigame::query()->where('name', 'Game Hai')->firstOrFail();

    expect($awayGame->club_id)->toBe($away->id);

    $this->actingAs($owner)->put("/minigames/{$awayGame->id}/roster", [
        'member_ids' => [$homeMember->id],
    ])->assertSessionHasErrors('member_ids');

    $this->actingAs($owner)->post("/clubs/{$home->id}/switch")
        ->assertRedirect(route('dashboard'));

    expect($owner->refresh()->club_id)->toBe($home->id);

    $this->actingAs($owner)->get("/minigames/{$homeGame->id}")->assertOk();
});

it('lets only the owner replace the invite token', function () {
    $owner = clubUser(UserRole::Owner);
    $member = clubUser(UserRole::Member);
    $club = Club::query()->firstOrFail();
    $previous = $club->invite_token;

    $this->actingAs($member)->get('/invite')->assertForbidden();
    $this->actingAs($owner)->get('/invite')
        ->assertOk()
        ->assertInertia(fn (Assert $page) => $page
            ->component('Clubs/Invite')
            ->where('inviteUrl', route('join.show', ['token' => $previous])));

    $this->actingAs($member)->post('/settings/invite')->assertForbidden();
    $this->actingAs($owner)->post('/settings/invite')->assertRedirect(route('invite'));

    expect($club->refresh()->invite_token)->not->toBe($previous);

    $this->get('/join/'.$previous)->assertNotFound();
});
