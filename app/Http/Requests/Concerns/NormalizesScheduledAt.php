<?php

namespace App\Http\Requests\Concerns;

use App\Support\LocalDateTime;

trait NormalizesScheduledAt
{
    protected function normalizeScheduledAt(): void
    {
        $value = $this->input('scheduled_at');

        if (! is_string($value) || trim($value) === '') {
            return;
        }

        $timezone = $this->user()?->club?->timezone ?: config('pickmate.club.timezone');

        $this->merge([
            'scheduled_at' => LocalDateTime::toUtc($value, $timezone),
        ]);
    }
}
