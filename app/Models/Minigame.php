<?php

namespace App\Models;

use App\Enums\MinigameFormat;
use App\Enums\MinigameStatus;
use Database\Factories\MinigameFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Minigame extends Model
{
    /** @use HasFactory<MinigameFactory> */
    use HasFactory;

    protected $fillable = [
        'club_id',
        'name',
        'description',
        'format',
        'status',
        'starts_at',
        'ends_at',
        'default_score',
        'best_of',
        'participation_points',
        'win_points',
        'loss_points',
        'clean_win_bonus',
        'created_by',
    ];

    protected function casts(): array
    {
        return [
            'format' => MinigameFormat::class,
            'status' => MinigameStatus::class,
            'starts_at' => 'datetime',
            'ends_at' => 'datetime',
            'default_score' => 'integer',
            'best_of' => 'integer',
            'participation_points' => 'integer',
            'win_points' => 'integer',
            'loss_points' => 'integer',
            'clean_win_bonus' => 'integer',
        ];
    }

    public function club(): BelongsTo
    {
        return $this->belongsTo(Club::class);
    }

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function members(): BelongsToMany
    {
        return $this->belongsToMany(Member::class, 'minigame_members');
    }

    public function matches(): HasMany
    {
        return $this->hasMany(MatchGame::class);
    }

    public function rankings(): HasMany
    {
        return $this->hasMany(Ranking::class);
    }

    public function isActive(): bool
    {
        return $this->status === MinigameStatus::Active;
    }

    public function isClosed(): bool
    {
        return $this->status === MinigameStatus::Closed;
    }
}
