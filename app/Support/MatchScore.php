<?php

namespace App\Support;

use Illuminate\Validation\ValidationException;

class MatchScore
{
    /**
     * @param  list<array{team_1_score: int, team_2_score: int}>  $sets
     */
    public static function winnerTeam(array $sets, int $targetScore, int $bestOf): int
    {
        if ($sets === []) {
            throw ValidationException::withMessages([
                'sets' => 'Enter at least one set.',
            ]);
        }

        $required = intdiv($bestOf, 2) + 1;
        $wins = [1 => 0, 2 => 0];

        foreach ($sets as $index => $set) {
            $teamOne = (int) $set['team_1_score'];
            $teamTwo = (int) $set['team_2_score'];

            if ($teamOne === $teamTwo) {
                throw ValidationException::withMessages([
                    'sets' => 'A set cannot end in a tie.',
                ]);
            }

            if (max($teamOne, $teamTwo) < $targetScore) {
                throw ValidationException::withMessages([
                    'sets' => "The winning score must reach {$targetScore}.",
                ]);
            }

            $wins[$teamOne > $teamTwo ? 1 : 2]++;

            $decided = max($wins[1], $wins[2]) >= $required;
            $isLastSet = $index === array_key_last($sets);

            if ($decided && ! $isLastSet) {
                throw ValidationException::withMessages([
                    'sets' => 'The match already had a winner before the final set.',
                ]);
            }
        }

        if ($wins[1] === $wins[2] || max($wins[1], $wins[2]) < $required) {
            throw ValidationException::withMessages([
                'sets' => 'The set scores do not decide a winner for this best-of format.',
            ]);
        }

        return $wins[1] > $wins[2] ? 1 : 2;
    }

    /**
     * @param  list<array{team_1_score: int, team_2_score: int}>  $sets
     */
    public static function isCleanWin(array $sets, int $winnerTeam): bool
    {
        if ($sets === []) {
            return false;
        }

        foreach ($sets as $set) {
            $setWinner = $set['team_1_score'] > $set['team_2_score'] ? 1 : 2;

            if ($setWinner !== $winnerTeam) {
                return false;
            }
        }

        return true;
    }
}
