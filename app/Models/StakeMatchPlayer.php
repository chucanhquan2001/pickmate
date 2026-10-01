<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class StakeMatchPlayer extends Model
{
    public const UPDATED_AT = null;

    protected $fillable = [
        'stake_match_id',
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

    public function stakeMatch(): BelongsTo
    {
        return $this->belongsTo(StakeMatch::class);
    }

    public function member(): BelongsTo
    {
        return $this->belongsTo(Member::class);
    }
}
