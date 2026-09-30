<?php

namespace App\Http\Requests;

class UpdateMinigameRulesRequest extends ClubWriteRequest
{
    /**
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        return [
            'default_score' => ['required', 'integer', 'in:11,15,21'],
            'best_of' => ['required', 'integer', 'in:1,3,5'],
            'participation_points' => ['required', 'integer', 'min:0', 'max:1000'],
            'win_points' => ['required', 'integer', 'min:0', 'max:1000'],
            'loss_points' => ['required', 'integer', 'min:0', 'max:1000'],
            'clean_win_bonus' => ['required', 'integer', 'min:0', 'max:1000'],
        ];
    }
}
