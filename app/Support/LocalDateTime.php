<?php

namespace App\Support;

use Carbon\Carbon;

class LocalDateTime
{
    public static function toUtc(string $value, string $timezone): string
    {
        $value = trim($value);

        if (preg_match('/[Zz]|[+-]\d{2}:\d{2}$/', $value) === 1) {
            return Carbon::parse($value)->utc()->format('Y-m-d H:i:s');
        }

        $normalized = str_replace('T', ' ', $value);

        return Carbon::parse($normalized, $timezone)->utc()->format('Y-m-d H:i:s');
    }
}
