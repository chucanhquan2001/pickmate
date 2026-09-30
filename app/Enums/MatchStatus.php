<?php

namespace App\Enums;

enum MatchStatus: string
{
    case Scheduled = 'scheduled';
    case Playing = 'playing';
    case Completed = 'completed';
    case Cancelled = 'cancelled';
}
