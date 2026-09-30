<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class MatchSet extends Model
{
    protected $fillable = [
        'match_id',
        'set_number',
        'team_1_score',
        'team_2_score',
    ];

    protected function casts(): array
    {
        return [
            'set_number' => 'integer',
            'team_1_score' => 'integer',
            'team_2_score' => 'integer',
        ];
    }

    public function match(): BelongsTo
    {
        return $this->belongsTo(MatchGame::class, 'match_id');
    }
}
