<?php

namespace App\Models;

use App\Enums\ClubStatus;
use Database\Factories\ClubFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Str;

class Club extends Model
{
    /** @use HasFactory<ClubFactory> */
    use HasFactory;

    protected $fillable = [
        'name',
        'slug',
        'invite_token',
        'logo',
        'timezone',
        'language',
        'status',
        'default_score',
        'default_best_of',
        'default_participation_points',
        'default_win_points',
        'default_loss_points',
        'default_clean_win_bonus',
    ];

    protected function casts(): array
    {
        return [
            'status' => ClubStatus::class,
            'default_score' => 'integer',
            'default_best_of' => 'integer',
            'default_participation_points' => 'integer',
            'default_win_points' => 'integer',
            'default_loss_points' => 'integer',
            'default_clean_win_bonus' => 'integer',
        ];
    }

    protected static function booted(): void
    {
        static::creating(function (Club $club): void {
            if (! is_string($club->invite_token) || $club->invite_token === '') {
                $club->invite_token = Str::random(48);
            }
        });
    }

    public function users(): HasMany
    {
        return $this->hasMany(User::class);
    }

    public function members(): HasMany
    {
        return $this->hasMany(Member::class);
    }

    public function courts(): HasMany
    {
        return $this->hasMany(Court::class);
    }

    public function minigames(): HasMany
    {
        return $this->hasMany(Minigame::class);
    }

    public function stakeMatches(): HasMany
    {
        return $this->hasMany(StakeMatch::class);
    }

    public function memberships(): HasMany
    {
        return $this->hasMany(ClubMembership::class);
    }

    public function joinRequests(): HasMany
    {
        return $this->hasMany(ClubJoinRequest::class);
    }
}
