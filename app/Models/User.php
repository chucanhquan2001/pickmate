<?php

namespace App\Models;

use App\Enums\UserRole;
use App\Enums\UserStatus;
use Database\Factories\UserFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

class User extends Authenticatable
{
    /** @use HasFactory<UserFactory> */
    use HasFactory, Notifiable;

    protected $fillable = [
        'club_id',
        'name',
        'email',
        'avatar',
        'role',
        'status',
    ];

    protected function casts(): array
    {
        return [
            'role' => UserRole::class,
            'status' => UserStatus::class,
        ];
    }

    public function club(): BelongsTo
    {
        return $this->belongsTo(Club::class);
    }

    public function socialAccounts(): HasMany
    {
        return $this->hasMany(SocialAccount::class);
    }

    public function memberships(): HasMany
    {
        return $this->hasMany(ClubMembership::class);
    }

    public function currentMembership(): ?ClubMembership
    {
        if ($this->membershipLoaded && $this->membershipForClubId === $this->club_id) {
            return $this->resolvedMembership;
        }

        $this->membershipLoaded = true;
        $this->membershipForClubId = $this->club_id;
        $this->resolvedMembership = $this->club_id === null
            ? null
            : $this->memberships()->where('club_id', $this->club_id)->first();

        return $this->resolvedMembership;
    }

    public function currentRole(): ?UserRole
    {
        return $this->currentMembership()?->role;
    }

    public function isOwner(): bool
    {
        return $this->currentRole() === UserRole::Owner;
    }

    public function canManageClub(): bool
    {
        $role = $this->currentRole();

        return $role === UserRole::Owner || $role === UserRole::Admin;
    }

    private ?int $membershipForClubId = null;

    private bool $membershipLoaded = false;

    private ?ClubMembership $resolvedMembership = null;
}
