<?php

use App\Support\MatchScore;
use Illuminate\Validation\ValidationException;

it('requires enough sets to finish the match', function () {
    expect(fn () => MatchScore::winnerTeam([
        ['team_1_score' => 11, 'team_2_score' => 7],
    ], 11, 3))->toThrow(ValidationException::class);
});

it('rejects a set that never reaches the target score', function () {
    expect(fn () => MatchScore::winnerTeam([
        ['team_1_score' => 10, 'team_2_score' => 8],
    ], 11, 1))->toThrow(ValidationException::class);
});

it('detects a clean win', function () {
    $sets = [
        ['team_1_score' => 11, 'team_2_score' => 7],
        ['team_1_score' => 11, 'team_2_score' => 9],
    ];

    expect(MatchScore::winnerTeam($sets, 11, 3))->toBe(1)
        ->and(MatchScore::isCleanWin($sets, 1))->toBeTrue();
});
