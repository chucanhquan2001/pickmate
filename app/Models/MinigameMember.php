<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class MinigameMember extends Model
{
    public const UPDATED_AT = null;

    protected $fillable = [
        'minigame_id',
        'member_id',
    ];

    public function minigame(): BelongsTo
    {
        return $this->belongsTo(Minigame::class);
    }

    public function member(): BelongsTo
    {
        return $this->belongsTo(Member::class);
    }
}
