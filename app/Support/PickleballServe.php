<?php

namespace App\Support;

class PickleballServe
{
    /**
     * Opening state of a game.
     *
     * Side-out doubles starts at server 2 (USA Pickleball 5.B, 6.B.2).
     * Rally scoring and singles start with one server.
     *
     * @return array<string, mixed>
     */
    public static function initial(int $servingTeam, bool $doubles, string $scoringType): array
    {
        return self::present([
            'team_1_score' => 0,
            'team_2_score' => 0,
            'serving_team' => $servingTeam,
            'server_number' => $doubles && $scoringType === 'side_out' ? 2 : 1,
            'server_role' => 'starting',
            'doubles' => $doubles,
            'scoring_type' => $scoringType,
        ], null);
    }

    /**
     * Apply the team that won the rally.
     *
     * @param  array<string, mixed>  $state
     * @return array<string, mixed>
     */
    public static function apply(array $state, int $winnerTeam): array
    {
        $next = [
            'team_1_score' => (int) $state['team_1_score'],
            'team_2_score' => (int) $state['team_2_score'],
            'serving_team' => (int) $state['serving_team'],
            'server_number' => (int) $state['server_number'],
            'server_role' => (string) $state['server_role'],
            'doubles' => (bool) $state['doubles'],
            'scoring_type' => (string) $state['scoring_type'],
        ];

        $serving = $next['serving_team'];
        $event = 'hold';

        if ($next['scoring_type'] === 'side_out') {
            if ($winnerTeam === $serving) {
                self::addPoint($next, $serving);
            } elseif ($next['doubles'] && $next['server_number'] === 1) {
                $next['server_number'] = 2;
                $next['server_role'] = $next['server_role'] === 'starting' ? 'partner' : 'starting';
                $event = 'second_server';
            } else {
                $next['serving_team'] = $serving === 1 ? 2 : 1;
                $next['server_number'] = 1;
                $next['server_role'] = $next['doubles'] ? self::roleOnRight($next) : 'starting';
                $event = 'side_out';
            }
        } else {
            self::addPoint($next, $winnerTeam);

            if ($winnerTeam !== $serving) {
                $next['serving_team'] = $winnerTeam;
                $next['server_number'] = 1;
                $next['server_role'] = $next['doubles'] ? self::roleOnRight($next) : 'starting';
                $event = 'side_out';
            }
        }

        return self::present($next, $event);
    }

    /**
     * @param  array<string, int|string|bool>  $state
     */
    private static function addPoint(array &$state, int $team): void
    {
        if ($team === 1) {
            $state['team_1_score']++;
        } else {
            $state['team_2_score']++;
        }
    }

    /**
     * The player standing in the right court serves first after a side out.
     * Even score: the starting server. Odd score: their partner.
     *
     * @param  array<string, int|string|bool>  $state
     */
    private static function roleOnRight(array $state): string
    {
        $team = (int) $state['serving_team'];
        $score = $team === 1 ? (int) $state['team_1_score'] : (int) $state['team_2_score'];

        return $score % 2 === 0 ? 'starting' : 'partner';
    }

    /**
     * @param  array<string, int|string|bool>  $state
     * @return array<string, mixed>
     */
    private static function present(array $state, ?string $event): array
    {
        $serving = (int) $state['serving_team'];
        $serverScore = $serving === 1 ? (int) $state['team_1_score'] : (int) $state['team_2_score'];
        $receiverScore = $serving === 1 ? (int) $state['team_2_score'] : (int) $state['team_1_score'];
        $serverSide = self::side((string) $state['server_role'], $serverScore);
        $receivingTeam = $serving === 1 ? 2 : 1;
        $receiverScoreForSide = $receivingTeam === 1 ? (int) $state['team_1_score'] : (int) $state['team_2_score'];
        $receiverRole = $state['doubles']
            ? self::roleOnSide($receiverScoreForSide, $serverSide)
            : 'starting';

        $scoreCall = $state['doubles'] && $state['scoring_type'] === 'side_out'
            ? "{$serverScore}-{$receiverScore}-{$state['server_number']}"
            : "{$serverScore}-{$receiverScore}";

        return [
            ...$state,
            'server_side' => $serverSide,
            'receiver_team' => $receivingTeam,
            'receiver_role' => $receiverRole,
            'receiver_side' => $serverSide,
            'score_call' => $scoreCall,
            'event' => $event,
        ];
    }

    private static function side(string $role, int $score): string
    {
        $startingOnRight = $score % 2 === 0;

        if ($role === 'starting') {
            return $startingOnRight ? 'right' : 'left';
        }

        return $startingOnRight ? 'left' : 'right';
    }

    private static function roleOnSide(int $score, string $side): string
    {
        $startingSide = $score % 2 === 0 ? 'right' : 'left';

        return $startingSide === $side ? 'starting' : 'partner';
    }
}
