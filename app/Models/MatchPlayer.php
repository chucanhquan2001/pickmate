<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class MatchPlayer extends Model
{
    public const UPDATED_AT = null;

    protected $fillable = [
        'match_id',
        'member_id',
        'team',
        'position',
    ];

    protected function casts(): array
    {
        return [
            'team' => 'integer',
            'position' => 'integer',
        ];
    }

    public function match(): BelongsTo
    {
        return $this->belongsTo(MatchGame::class, 'match_id');
    }

    public function member(): BelongsTo
    {
        return $this->belongsTo(Member::class);
    }
}
