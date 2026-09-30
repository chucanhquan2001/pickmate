<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class RankingLog extends Model
{
    public const UPDATED_AT = null;

    protected $fillable = [
        'minigame_id',
        'member_id',
        'match_id',
        'old_point',
        'change_point',
        'new_point',
        'old_rank',
        'new_rank',
    ];

    protected function casts(): array
    {
        return [
            'old_point' => 'integer',
            'change_point' => 'integer',
            'new_point' => 'integer',
            'old_rank' => 'integer',
            'new_rank' => 'integer',
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

    public function match(): BelongsTo
    {
        return $this->belongsTo(MatchGame::class, 'match_id');
    }
}
