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

    public function isOwner(): bool
    {
        return $this->role === UserRole::Owner;
    }

    public function canManageClub(): bool
    {
        return $this->role === UserRole::Owner || $this->role === UserRole::Admin;
    }
}
