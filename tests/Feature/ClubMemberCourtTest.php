<?php

use App\Enums\UserRole;
use App\Models\Club;
use App\Models\Court;
use App\Models\Member;
use App\Models\Minigame;
use Inertia\Testing\AssertableInertia as Assert;

it('lets an admin manage members and keeps club defaults from changing an existing minigame', function () {
    $admin = clubUser();
    $club = Club::query()->firstOrFail();
    $club->update(['default_win_points' => 9]);

    $this->actingAs($admin)->post('/members', [
        'name' => 'Nguyen Van A',
        'nickname' => 'A',
        'gender' => 'male',
        'email' => 'a@example.com',
        'level' => 'intermediate',
    ])->assertRedirect();

    $created = Member::query()->where('email', 'a@example.com')->value('id');

    $this->actingAs($admin)->get('/members?search=Nguyen')
        ->assertOk()
        ->assertInertia(fn (Assert $page) => $page
            ->component('Members/Index')
            ->where('members.data.0.id', $created));

    $this->actingAs($admin)->post('/minigames', [
        'name' => 'Doi nam',
        'format' => 'double_male',
    ])->assertRedirect();

    $minigameId = Minigame::query()->where('name', 'Doi nam')->value('id');

    $this->actingAs($admin)->get("/minigames/{$minigameId}")
        ->assertOk()
        ->assertInertia(fn (Assert $page) => $page
            ->component('Minigames/Show')
            ->where('minigame.win_points', 9));

    $club->update(['default_win_points' => 3]);

    $this->actingAs($admin)->get("/minigames/{$minigameId}")
        ->assertOk()
        ->assertInertia(fn (Assert $page) => $page->where('minigame.win_points', 9));

    $this->actingAs($admin)->post('/courts', [
        'name' => 'Court 1',
        'code' => 'C01',
    ])->assertRedirect(route('courts.index'));

    $this->actingAs($admin)->get('/courts')
        ->assertInertia(fn (Assert $page) => $page
            ->component('Courts/Index')
            ->where('courts.data.0.code', 'C01'));

    expect(Court::query()->where('code', 'C01')->exists())->toBeTrue();
});

it('allows the owner to update club settings and promote an admin', function () {
    $owner = clubUser(UserRole::Owner);
    $member = clubUser(UserRole::Member);

    $this->actingAs($member)->put('/settings', [
        'name' => 'New Name',
    ])->assertForbidden();

    $this->actingAs($owner)->put('/settings', [
        'name' => 'PickMate Hanoi',
        'default_score' => 15,
        'default_best_of' => 3,
    ])->assertRedirect(route('settings'));

    $this->actingAs($owner)->get('/settings')
        ->assertInertia(fn (Assert $page) => $page
            ->component('Settings/Club')
            ->where('club.name', 'PickMate Hanoi')
            ->where('club.default_score', 15));

    $this->actingAs($owner)->patch("/settings/users/{$member->id}", [
        'role' => 'admin',
    ])->assertRedirect(route('settings'));

    expect($member->refresh()->role)->toBe(UserRole::Admin);
});

it('forbids a member from creating club data and hides another club', function () {
    $memberUser = clubUser(UserRole::Member);
    $otherClub = Club::factory()->create();
    $foreignMember = Member::factory()->create([
        'club_id' => $otherClub->id,
        'gender' => 'male',
    ]);

    $this->actingAs($memberUser)->post('/members', [
        'name' => 'Blocked',
        'gender' => 'male',
    ])->assertForbidden();

    $this->actingAs($memberUser)->get("/members/{$foreignMember->id}")
        ->assertNotFound();
});
