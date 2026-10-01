<?php

namespace App\Models;

use App\Enums\ScoringType;
use App\Enums\StakeFormat;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class StakeMatch extends Model
{
    protected $fillable = [
        'club_id',
        'court_id',
        'scheduled_at',
        'format',
        'scoring_type',
        'item',
        'quantity',
        'expected_amount',
        'team_1_score',
        'team_2_score',
        'created_by',
    ];

    protected function casts(): array
    {
        return [
            'scheduled_at' => 'datetime',
            'format' => StakeFormat::class,
            'scoring_type' => ScoringType::class,
            'quantity' => 'integer',
            'expected_amount' => 'integer',
            'team_1_score' => 'integer',
            'team_2_score' => 'integer',
        ];
    }

    public function club(): BelongsTo
    {
        return $this->belongsTo(Club::class);
    }

    public function court(): BelongsTo
    {
        return $this->belongsTo(Court::class);
    }

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function players(): HasMany
    {
        return $this->hasMany(StakeMatchPlayer::class);
    }
}
