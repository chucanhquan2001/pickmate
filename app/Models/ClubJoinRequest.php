<?php

namespace App\Models;

use App\Enums\JoinRequestStatus;
use App\Enums\MemberGender;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ClubJoinRequest extends Model
{
    protected $fillable = [
        'club_id',
        'user_id',
        'gender',
        'nickname',
        'dupr_rating',
        'spcn_rating',
        'status',
        'reviewed_by',
        'reviewed_at',
    ];

    protected function casts(): array
    {
        return [
            'gender' => MemberGender::class,
            'dupr_rating' => 'decimal:1',
            'spcn_rating' => 'decimal:1',
            'status' => JoinRequestStatus::class,
            'reviewed_at' => 'datetime',
        ];
    }

    public function club(): BelongsTo
    {
        return $this->belongsTo(Club::class);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function reviewer(): BelongsTo
    {
        return $this->belongsTo(User::class, 'reviewed_by');
    }
}
