<?php

namespace App\Models;

use App\Enums\MatchStatus;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class MatchGame extends Model
{
    protected $table = 'matches';

    protected $fillable = [
        'minigame_id',
        'session_id',
        'court_id',
        'status',
        'scheduled_at',
        'started_at',
        'completed_at',
        'created_by',
    ];

    protected function casts(): array
    {
        return [
            'status' => MatchStatus::class,
            'scheduled_at' => 'datetime',
            'started_at' => 'datetime',
            'completed_at' => 'datetime',
        ];
    }

    public function minigame(): BelongsTo
    {
        return $this->belongsTo(Minigame::class);
    }

    public function court(): BelongsTo
    {
        return $this->belongsTo(Court::class);
    }

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function players(): HasMany
    {
        return $this->hasMany(MatchPlayer::class, 'match_id');
    }

    public function sets(): HasMany
    {
        return $this->hasMany(MatchSet::class, 'match_id')->orderBy('set_number');
    }

    public function winnerTeam(): ?int
    {
        if ($this->status !== MatchStatus::Completed) {
            return null;
        }

        $teamOne = 0;
        $teamTwo = 0;

        foreach ($this->sets as $set) {
            if ($set->team_1_score === $set->team_2_score) {
                continue;
            }

            if ($set->team_1_score > $set->team_2_score) {
                $teamOne++;
            } else {
                $teamTwo++;
            }
        }

        if ($teamOne === $teamTwo) {
            return null;
        }

        return $teamOne > $teamTwo ? 1 : 2;
    }
}
