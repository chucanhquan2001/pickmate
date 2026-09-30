<?php

namespace App\Enums;

enum MinigameStatus: string
{
    case Draft = 'draft';
    case Active = 'active';
    case Closed = 'closed';
}
