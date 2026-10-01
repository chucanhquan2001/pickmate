<?php

namespace App\Enums;

enum StakeFormat: string
{
    case Single = 'single';
    case Double = 'double';

    public function playersPerTeam(): int
    {
        return $this === self::Single ? 1 : 2;
    }
}
