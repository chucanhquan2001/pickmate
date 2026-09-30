<?php

namespace App\Http\Requests;

class UpdateClubRequest extends OwnerRequest
{
    /**
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        return [
            'name' => ['sometimes', 'required', 'string', 'max:255'],
            'logo' => ['nullable', 'string', 'max:2048'],
            'timezone' => ['sometimes', 'required', 'timezone'],
            'language' => ['sometimes', 'required', 'string', 'max:10'],
            'default_score' => ['sometimes', 'required', 'integer', 'in:11,15,21'],
            'default_best_of' => ['sometimes', 'required', 'integer', 'in:1,3,5'],
            'default_participation_points' => ['sometimes', 'required', 'integer', 'min:0', 'max:1000'],
            'default_win_points' => ['sometimes', 'required', 'integer', 'min:0', 'max:1000'],
            'default_loss_points' => ['sometimes', 'required', 'integer', 'min:0', 'max:1000'],
            'default_clean_win_bonus' => ['sometimes', 'required', 'integer', 'min:0', 'max:1000'],
        ];
    }
}
