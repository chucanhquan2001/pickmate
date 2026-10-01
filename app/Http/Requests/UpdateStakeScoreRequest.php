<?php

namespace App\Http\Requests;

use App\Enums\UserStatus;
use Illuminate\Foundation\Http\FormRequest;

class UpdateStakeScoreRequest extends FormRequest
{
    public function authorize(): bool
    {
        $user = $this->user();

        return $user !== null
            && $user->status === UserStatus::Active
            && $user->currentMembership() !== null;
    }

    /**
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        return [
            'team_1_score' => ['required', 'integer', 'min:0', 'max:99'],
            'team_2_score' => ['required', 'integer', 'min:0', 'max:99'],
        ];
    }
}
