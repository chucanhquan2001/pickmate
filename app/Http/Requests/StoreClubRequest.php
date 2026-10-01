<?php

namespace App\Http\Requests;

use App\Enums\MemberGender;
use App\Enums\UserStatus;
use App\Rules\SkillRating;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreClubRequest extends FormRequest
{
    public function authorize(): bool
    {
        $user = $this->user();

        return $user !== null && $user->status === UserStatus::Active;
    }

    protected function prepareForValidation(): void
    {
        if ($this->input('nickname') === '') {
            $this->merge(['nickname' => null]);
        }

        $this->merge(SkillRating::trimmed($this));
    }

    /**
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        return [
            'name' => ['required', 'string', 'max:255'],
            'nickname' => ['nullable', 'string', 'max:255'],
            'gender' => ['required', Rule::enum(MemberGender::class)],
            'dupr_rating' => ['required', new SkillRating],
            'spcn_rating' => ['required', new SkillRating],
        ];
    }
}
