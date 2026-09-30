<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Ranking extends Model
{
    public const CREATED_AT = null;

    protected $fillable = [
        'minigame_id',
        'member_id',
        'matches',
        'wins',
        'losses',
        'points',
        'rating',
        'rank',
    ];

    protected function casts(): array
    {
        return [
            'matches' => 'integer',
            'wins' => 'integer',
            'losses' => 'integer',
            'points' => 'integer',
            'rating' => 'decimal:2',
            'rank' => 'integer',
        ];
    }

    public function minigame(): BelongsTo
    {
        return $this->belongsTo(Minigame::class);
    }

    public function member(): BelongsTo
    {
        return $this->belongsTo(Member::class);
    }

    public function winRate(): float
    {
        if ($this->matches === 0) {
            return 0.0;
        }

        return round($this->wins / $this->matches, 3);
    }
}
