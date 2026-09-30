<?php

use App\Enums\MemberGender;
use App\Enums\MinigameStatus;
use App\Enums\UserRole;
use App\Models\Court;
use App\Models\MatchGame;
use App\Models\Member;
use App\Models\Minigame;
use App\Models\Ranking;
use App\Models\RankingLog;
use Inertia\Testing\AssertableInertia as Assert;

it('runs the minigame loop and keeps rankings inside that minigame', function () {
    $admin = clubUser();
    $players = Member::factory()->count(4)->create([
        'club_id' => 1,
        'gender' => MemberGender::Male,
    ]);
    $court = Court::factory()->create([
        'club_id' => 1,
        'name' => 'Court 1',
        'code' => 'C01',
    ]);

    $this->actingAs($admin);

    $this->post('/minigames', [
        'name' => 'Doi nam thang 9',
        'format' => 'double_male',
    ])->assertRedirect();

    $minigameId = Minigame::query()->where('name', 'Doi nam thang 9')->value('id');

    $this->put("/minigames/{$minigameId}/roster", [
        'member_ids' => $players->pluck('id')->all(),
    ])->assertRedirect();

    $this->post("/minigames/{$minigameId}/matches", [
        'scheduled_at' => now()->addHour()->toIso8601String(),
        'court_id' => $court->id,
        'team_1' => [$players[0]->id, $players[1]->id],
        'team_2' => [$players[2]->id, $players[3]->id],
    ])->assertSessionHasErrors('minigame');

    $this->post("/minigames/{$minigameId}/activate")->assertRedirect();

    $this->get("/minigames/{$minigameId}")
        ->assertInertia(fn (Assert $page) => $page
            ->component('Minigames/Show')
            ->where('minigame.status', 'active'));

    $this->post("/minigames/{$minigameId}/matches", [
        'scheduled_at' => now()->addHour()->toIso8601String(),
        'court_id' => $court->id,
        'team_1' => [$players[0]->id, $players[1]->id],
        'team_2' => [$players[2]->id, $players[3]->id],
    ])->assertRedirect();

    $matchId = MatchGame::query()->where('minigame_id', $minigameId)->value('id');

    $this->post("/minigames/{$minigameId}/matches/{$matchId}/result", [
        'sets' => [
            ['team_1_score' => 11, 'team_2_score' => 7],
        ],
    ])->assertRedirect(route('matches.show', [$minigameId, $matchId]));

    $this->get("/minigames/{$minigameId}/matches/{$matchId}")
        ->assertInertia(fn (Assert $page) => $page
            ->component('Matches/Show')
            ->where('match.winner_team', 1)
            ->where('match.status', 'completed'));

    $rankings = rankingsByMember($minigameId);

    expect($rankings[$players[0]->id]['points'])->toBe(5)
        ->and($rankings[$players[0]->id]['wins'])->toBe(1)
        ->and($rankings[$players[2]->id]['points'])->toBe(1)
        ->and($rankings[$players[2]->id]['losses'])->toBe(1);

    expect(RankingLog::query()->where('minigame_id', $minigameId)->where('match_id', $matchId)->count())->toBeGreaterThan(0);

    $this->post('/minigames', [
        'name' => 'Doi nu',
        'format' => 'double_female',
    ])->assertRedirect();

    $otherId = Minigame::query()->where('name', 'Doi nu')->value('id');

    expect(Ranking::query()->where('minigame_id', $otherId)->count())->toBe(0);
    expect(Ranking::query()->where('minigame_id', $minigameId)->where('points', 5)->count())->toBe(2);

    $this->delete('/current-minigame')->assertRedirect(route('dashboard'));

    $this->get('/dashboard')
        ->assertOk()
        ->assertInertia(fn (Assert $page) => $page
            ->component('Dashboard')
            ->where('selected', null)
            ->where('minigames.0.name', 'Doi nam thang 9'));

    $this->post("/minigames/{$minigameId}/select")->assertRedirect(route('dashboard'));

    $this->get('/dashboard')
        ->assertOk()
        ->assertInertia(fn (Assert $page) => $page
            ->where('selected.roster_count', 4)
            ->where('selected.matches_played', 1)
            ->where('selected.top_rankings.0.points', 5));

    $this->get("/members/{$players[0]->id}")
        ->assertOk()
        ->assertInertia(fn (Assert $page) => $page
            ->component('Members/Show')
            ->where('member.minigames.0.points', 5)
            ->where('member.matches.0.minigame.name', 'Doi nam thang 9'));

    $this->post("/minigames/{$minigameId}/close")->assertRedirect();
    expect(Minigame::query()->find($minigameId)->status)->toBe(MinigameStatus::Closed);

    $this->post("/minigames/{$minigameId}/matches", [
        'scheduled_at' => now()->addHours(2)->toIso8601String(),
        'team_1' => [$players[0]->id, $players[1]->id],
        'team_2' => [$players[2]->id, $players[3]->id],
    ])->assertSessionHasErrors('minigame');

    $this->post("/minigames/{$minigameId}/matches/{$matchId}/result", [
        'sets' => [
            ['team_1_score' => 11, 'team_2_score' => 0],
        ],
    ])->assertSessionHasErrors('minigame');
});

it('scores a split best-of-three match without a clean-win bonus', function () {
    $admin = clubUser();
    $players = Member::factory()->count(4)->create([
        'club_id' => 1,
        'gender' => MemberGender::Male,
    ]);

    $this->actingAs($admin);

    $this->post('/minigames', [
        'name' => 'Best of 3',
        'format' => 'double_male',
    ])->assertRedirect();

    $minigameId = Minigame::query()->where('name', 'Best of 3')->value('id');

    $this->patch("/minigames/{$minigameId}/rules", [
        'default_score' => 11,
        'best_of' => 3,
        'participation_points' => 1,
        'win_points' => 3,
        'loss_points' => 0,
        'clean_win_bonus' => 1,
    ])->assertRedirect();

    $this->put("/minigames/{$minigameId}/roster", [
        'member_ids' => $players->pluck('id')->all(),
    ])->assertRedirect();
    $this->post("/minigames/{$minigameId}/activate")->assertRedirect();

    $this->post("/minigames/{$minigameId}/matches", [
        'scheduled_at' => now()->addHour()->toIso8601String(),
        'team_1' => [$players[0]->id, $players[1]->id],
        'team_2' => [$players[2]->id, $players[3]->id],
    ])->assertRedirect();

    $matchId = MatchGame::query()->where('minigame_id', $minigameId)->value('id');

    $this->post("/minigames/{$minigameId}/matches/{$matchId}/result", [
        'sets' => [
            ['team_1_score' => 11, 'team_2_score' => 5],
            ['team_1_score' => 5, 'team_2_score' => 11],
            ['team_1_score' => 11, 'team_2_score' => 9],
        ],
    ])->assertRedirect();

    $this->get("/minigames/{$minigameId}/matches/{$matchId}")
        ->assertInertia(fn (Assert $page) => $page->where('match.winner_team', 1));

    $points = rankingsByMember($minigameId);

    expect($points[$players[0]->id]['points'])->toBe(4)
        ->and($points[$players[2]->id]['points'])->toBe(1);
});

it('rejects players who are not on the roster or do not match the format', function () {
    $admin = clubUser();
    $males = Member::factory()->count(2)->create(['club_id' => 1, 'gender' => MemberGender::Male]);
    $outsider = Member::factory()->create(['club_id' => 1, 'gender' => MemberGender::Male]);
    $female = Member::factory()->female()->create(['club_id' => 1]);

    $this->actingAs($admin);

    $this->post('/minigames', [
        'name' => 'Don nam',
        'format' => 'single_male',
    ])->assertRedirect();

    $minigameId = Minigame::query()->where('name', 'Don nam')->value('id');

    $this->put("/minigames/{$minigameId}/roster", [
        'member_ids' => [$female->id],
    ])->assertSessionHasErrors('member_ids');

    $this->put("/minigames/{$minigameId}/roster", [
        'member_ids' => $males->pluck('id')->all(),
    ])->assertRedirect();

    $this->post("/minigames/{$minigameId}/activate")->assertRedirect();

    $this->post("/minigames/{$minigameId}/matches", [
        'scheduled_at' => now()->addHour()->toIso8601String(),
        'team_1' => [$males[0]->id, $males[1]->id],
        'team_2' => [$outsider->id],
    ])->assertSessionHasErrors('team_1');

    $this->post("/minigames/{$minigameId}/matches", [
        'scheduled_at' => now()->addHour()->toIso8601String(),
        'team_1' => [$males[0]->id],
        'team_2' => [$outsider->id],
    ])->assertSessionHasErrors('team_1');
});

it('lets a member read the ranking but not enter a result', function () {
    $admin = clubUser();
    $member = clubUser(UserRole::Member);
    $players = Member::factory()->count(2)->create(['club_id' => 1, 'gender' => MemberGender::Male]);

    $this->actingAs($admin)->post('/minigames', [
        'name' => 'Don nam',
        'format' => 'single_male',
    ])->assertRedirect();

    $minigameId = Minigame::query()->where('name', 'Don nam')->value('id');

    $this->actingAs($admin)->put("/minigames/{$minigameId}/roster", [
        'member_ids' => $players->pluck('id')->all(),
    ])->assertRedirect();
    $this->actingAs($admin)->post("/minigames/{$minigameId}/activate")->assertRedirect();

    $this->actingAs($admin)->post("/minigames/{$minigameId}/matches", [
        'scheduled_at' => now()->addHour()->toIso8601String(),
        'team_1' => [$players[0]->id],
        'team_2' => [$players[1]->id],
    ])->assertRedirect();

    $matchId = MatchGame::query()->where('minigame_id', $minigameId)->value('id');

    $this->actingAs($member)->get("/minigames/{$minigameId}/rankings")
        ->assertOk()
        ->assertInertia(fn (Assert $page) => $page->component('Rankings/Index'));

    $this->actingAs($member)->post("/minigames/{$minigameId}/matches/{$matchId}/result", [
        'sets' => [
            ['team_1_score' => 11, 'team_2_score' => 3],
        ],
    ])->assertForbidden();
});

function rankingsByMember(int $minigameId)
{
    $page = test()->get("/minigames/{$minigameId}/rankings")->assertOk()->inertiaPage();

    return collect($page['props']['rankings'])->keyBy('member_id');
}
