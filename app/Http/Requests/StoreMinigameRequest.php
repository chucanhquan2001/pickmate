<?php

namespace App\Http\Requests;

use App\Enums\MinigameFormat;
use Illuminate\Validation\Rule;

class StoreMinigameRequest extends ClubWriteRequest
{
    /**
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        return [
            'name' => ['required', 'string', 'max:255'],
            'description' => ['nullable', 'string', 'max:2000'],
            'format' => ['required', Rule::enum(MinigameFormat::class)],
            'starts_at' => ['nullable', 'date'],
            'ends_at' => ['nullable', 'date', 'after_or_equal:starts_at'],
            'default_score' => ['sometimes', 'required', 'integer', 'in:11,15,21'],
            'best_of' => ['sometimes', 'required', 'integer', 'in:1,3,5'],
            'participation_points' => ['sometimes', 'required', 'integer', 'min:0', 'max:1000'],
            'win_points' => ['sometimes', 'required', 'integer', 'min:0', 'max:1000'],
            'loss_points' => ['sometimes', 'required', 'integer', 'min:0', 'max:1000'],
            'clean_win_bonus' => ['sometimes', 'required', 'integer', 'min:0', 'max:1000'],
        ];
    }
}
