<?php

namespace App\Models;

use App\Enums\MemberGender;
use App\Enums\MemberLevel;
use App\Enums\MemberStatus;
use Database\Factories\MemberFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Member extends Model
{
    /** @use HasFactory<MemberFactory> */
    use HasFactory;

    protected $fillable = [
        'club_id',
        'user_id',
        'name',
        'nickname',
        'avatar',
        'gender',
        'birthday',
        'phone',
        'email',
        'level',
        'joined_at',
        'status',
    ];

    protected function casts(): array
    {
        return [
            'gender' => MemberGender::class,
            'level' => MemberLevel::class,
            'status' => MemberStatus::class,
            'birthday' => 'date',
            'joined_at' => 'date',
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

    public function minigames(): BelongsToMany
    {
        return $this->belongsToMany(Minigame::class, 'minigame_members');
    }

    public function rankings(): HasMany
    {
        return $this->hasMany(Ranking::class);
    }
}
