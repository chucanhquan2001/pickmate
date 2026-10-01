<?php

namespace App\Models;

use App\Enums\JoinRequestStatus;
use App\Enums\MemberGender;
use App\Enums\MemberLevel;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ClubJoinRequest extends Model
{
    protected $fillable = [
        'club_id',
        'user_id',
        'gender',
        'nickname',
        'level',
        'status',
        'reviewed_by',
        'reviewed_at',
    ];

    protected function casts(): array
    {
        return [
            'gender' => MemberGender::class,
            'level' => MemberLevel::class,
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
