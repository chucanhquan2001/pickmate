<?php

use App\Enums\UserRole;
use App\Models\Club;
use App\Models\ClubMembership;
use App\Models\Court;
use App\Models\Member;
use App\Models\Minigame;
use Inertia\Testing\AssertableInertia as Assert;

it('lets an admin manage members and keeps club defaults from changing an existing minigame', function () {
    $admin = clubUser();
    $club = Club::query()->firstOrFail();
    $club->update(['default_win_points' => 9]);

    $createdMember = Member::factory()->create([
        'club_id' => $club->id,
        'name' => 'Nguyen Van A',
        'email' => 'a@example.com',
    ]);
    $created = $createdMember->id;

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
    ])->assertRedirect(route('members.index'));

    expect(ClubMembership::query()->where('user_id', $member->id)->where('club_id', $owner->club_id)->first()->role)
        ->toBe(UserRole::Admin);
});

it('forbids a member from creating club data and hides another club', function () {
    $memberUser = clubUser(UserRole::Member);
    $otherClub = Club::factory()->create();
    $foreignMember = Member::factory()->create([
        'club_id' => $otherClub->id,
        'gender' => 'male',
    ]);

    $this->actingAs($memberUser)->post('/minigames', [
        'name' => 'Blocked',
        'format' => 'double_male',
    ])->assertForbidden();

    $this->actingAs($memberUser)->get("/members/{$foreignMember->id}")
        ->assertNotFound();
});

it('lets managers open the club overview and limits who can change roles', function () {
    $owner = clubUser(UserRole::Owner);
    $admin = clubUser(UserRole::Admin);
    $member = clubUser(UserRole::Member);
    $otherAdmin = clubUser(UserRole::Admin);
    $club = Club::query()->firstOrFail();

    $this->actingAs($admin)->get('/manage')
        ->assertOk()
        ->assertInertia(fn (Assert $page) => $page
            ->component('Manage/Index')
            ->where('clubName', $club->name)
            ->where('counts.admins', 2));

    foreach ([$owner, $admin, $member, $otherAdmin] as $user) {
        Member::factory()->create([
            'club_id' => $club->id,
            'user_id' => $user->id,
            'name' => $user->name,
            'email' => $user->email,
        ]);
    }

    $this->actingAs($admin)->get('/members')
        ->assertOk()
        ->assertInertia(fn (Assert $page) => $page
            ->component('Members/Index')
            ->where('members.data', fn ($rows) => collect($rows)->firstWhere('user_id', $member->id)['can_change_role'] === true
                && collect($rows)->firstWhere('user_id', $otherAdmin->id)['can_change_role'] === false
                && collect($rows)->firstWhere('user_id', $owner->id)['can_change_role'] === false));

    $this->actingAs($member)->get('/manage')->assertForbidden();
    $this->actingAs($member)->get('/members')
        ->assertOk()
        ->assertInertia(fn (Assert $page) => $page
            ->where('members.data', fn ($rows) => collect($rows)->every(fn ($row) => $row['can_change_role'] === false)));

    $this->actingAs($admin)->patch("/settings/users/{$member->id}", [
        'role' => 'admin',
    ])->assertRedirect(route('members.index'));

    expect(ClubMembership::query()->where('user_id', $member->id)->where('club_id', $club->id)->first()->role)
        ->toBe(UserRole::Admin);

    $this->actingAs($admin)->patch("/settings/users/{$otherAdmin->id}", [
        'role' => 'member',
    ])->assertSessionHasErrors('role');

    $this->actingAs($admin)->patch("/settings/users/{$owner->id}", [
        'role' => 'member',
    ])->assertSessionHasErrors('role');

    $this->actingAs($admin)->patch("/settings/users/{$admin->id}", [
        'role' => 'member',
    ])->assertSessionHasErrors('role');

    expect(ClubMembership::query()->where('user_id', $otherAdmin->id)->where('club_id', $club->id)->first()->role)
        ->toBe(UserRole::Admin)
        ->and(ClubMembership::query()->where('user_id', $owner->id)->where('club_id', $club->id)->first()->role)
        ->toBe(UserRole::Owner);

    $this->actingAs($owner)->patch("/settings/users/{$otherAdmin->id}", [
        'role' => 'member',
    ])->assertRedirect(route('members.index'));

    expect(ClubMembership::query()->where('user_id', $otherAdmin->id)->where('club_id', $club->id)->first()->role)
        ->toBe(UserRole::Member);
});
