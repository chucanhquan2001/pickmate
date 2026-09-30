<?php

namespace App\Models;

use App\Enums\CourtStatus;
use Database\Factories\CourtFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Court extends Model
{
    /** @use HasFactory<CourtFactory> */
    use HasFactory;

    protected $fillable = [
        'club_id',
        'name',
        'code',
        'status',
        'note',
    ];

    protected function casts(): array
    {
        return [
            'status' => CourtStatus::class,
        ];
    }

    public function club(): BelongsTo
    {
        return $this->belongsTo(Club::class);
    }
}
