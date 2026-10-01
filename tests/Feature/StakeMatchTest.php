<?php

use App\Enums\CourtStatus;
use App\Enums\UserRole;
use App\Models\Club;
use App\Models\Court;
use App\Models\Member;
use App\Models\StakeMatch;
use App\Models\User;
use Inertia\Testing\AssertableInertia as Assert;

function stakePayload(Court $court, array $team1, array $team2, string $format = 'single'): array
{
    return [
        'format' => $format,
        'scoring_type' => 'side_out',
        'scheduled_at' => '2026-10-02 18:00:00',
        'court_id' => $court->id,
        'item' => 'trứng',
        'quantity' => 10,
        'expected_amount' => 50000,
        'team_1' => $team1,
        'team_2' => $team2,
    ];
}

it('creates a singles or doubles stake and lists only matches the user played', function () {
    $player = clubUser(UserRole::Member);
    $club = Club::query()->firstOrFail();
    $me = Member::factory()->create([
        'club_id' => $club->id,
        'user_id' => $player->id,
        'name' => 'Toi',
    ]);
    $opponent = Member::factory()->create([
        'club_id' => $club->id,
        'name' => 'Doi thu',
    ]);
    $partner = Member::factory()->create([
        'club_id' => $club->id,
        'name' => 'Dong doi',
    ]);
    $other = Member::factory()->create([
        'club_id' => $club->id,
        'name' => 'Doi thu 2',
    ]);
    $court = Court::factory()->create(['club_id' => $club->id]);
    $bystander = clubUser(UserRole::Member);
    Member::factory()->create([
        'club_id' => $club->id,
        'user_id' => $bystander->id,
        'name' => 'Nguoi ngoai',
    ]);

    $this->actingAs($player)->post('/keo', stakePayload($court, [$me->id], [$opponent->id]))
        ->assertRedirect();

    $single = StakeMatch::query()->firstOrFail();

    $this->actingAs($player)->get('/keo')
        ->assertOk()
        ->assertInertia(fn (Assert $page) => $page
            ->component('Keo/Index')
            ->where('matches.data.0.id', $single->id)
            ->where('matches.data.0.item', 'trứng')
            ->where('matches.data.0.quantity', 10)
            ->where('matches.data.0.expected_amount', 50000));

    $this->actingAs($player)->get("/keo/{$single->id}")
        ->assertOk()
        ->assertInertia(fn (Assert $page) => $page
            ->component('Keo/Show')
            ->where('match.format', 'single')
            ->where('match.scoring_type', 'side_out'));

    $this->actingAs($player)->post("/keo/{$single->id}/score", [
        'team_1_score' => 11,
        'team_2_score' => 7,
    ])->assertRedirect(route('keo.show', $single));

    $this->actingAs($player)->get("/keo/{$single->id}")
        ->assertOk()
        ->assertInertia(fn (Assert $page) => $page
            ->where('match.team_1_score', 11)
            ->where('match.team_2_score', 7));

    $this->actingAs($bystander)->get("/keo/{$single->id}")->assertNotFound();
    $this->actingAs($bystander)->get('/keo')
        ->assertOk()
        ->assertInertia(fn (Assert $page) => $page->where('matches.data', []));

    $this->actingAs($player)->post('/keo', stakePayload(
        $court,
        [$me->id, $partner->id],
        [$opponent->id, $other->id],
        'double',
    ))->assertRedirect();

    expect(StakeMatch::query()->where('format', 'double')->exists())->toBeTrue();

    $this->actingAs($player)->post('/keo', stakePayload($court, [$me->id], [$opponent->id], 'double'))
        ->assertSessionHasErrors('team_1');
});

it('hides a stake from someone in another club', function () {
    $player = clubUser(UserRole::Member);
    $club = Club::query()->firstOrFail();
    $me = Member::factory()->create([
        'club_id' => $club->id,
        'user_id' => $player->id,
    ]);
    $opponent = Member::factory()->create(['club_id' => $club->id]);
    $court = Court::factory()->create(['club_id' => $club->id]);

    $this->actingAs($player)->post('/keo', stakePayload($court, [$me->id], [$opponent->id]))
        ->assertRedirect();

    $stake = StakeMatch::query()->firstOrFail();
    $outsider = User::factory()->create();

    $this->actingAs($outsider)->get("/keo/{$stake->id}")->assertNotFound();
    $this->actingAs($outsider)->get('/keo')
        ->assertOk()
        ->assertInertia(fn (Assert $page) => $page->where('matches.data', []));
});

it('rejects a court or member from outside the club', function () {
    $player = clubUser(UserRole::Member);
    $club = Club::query()->firstOrFail();
    $me = Member::factory()->create([
        'club_id' => $club->id,
        'user_id' => $player->id,
    ]);
    $opponent = Member::factory()->create(['club_id' => $club->id]);
    $foreignCourt = Court::factory()->create();
    $closedCourt = Court::factory()->create([
        'club_id' => $club->id,
        'status' => CourtStatus::Inactive,
    ]);

    $this->actingAs($player)->post('/keo', stakePayload($foreignCourt, [$me->id], [$opponent->id]))
        ->assertSessionHasErrors('court_id');

    $this->actingAs($player)->post('/keo', stakePayload($closedCourt, [$me->id], [$opponent->id]))
        ->assertSessionHasErrors('court_id');

    $foreignMember = Member::factory()->create();

    $this->actingAs($player)->post('/keo', stakePayload(
        Court::factory()->create(['club_id' => $club->id]),
        [$me->id],
        [$foreignMember->id],
    ))->assertSessionHasErrors('team_1');

    expect(StakeMatch::query()->count())->toBe(0);
});
