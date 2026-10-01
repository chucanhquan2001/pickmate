<?php

namespace App\Http\Requests;

use App\Enums\MemberGender;
use App\Enums\MemberStatus;
use App\Rules\SkillRating;
use Illuminate\Validation\Rule;

class UpdateMemberRequest extends ClubWriteRequest
{
    protected function prepareForValidation(): void
    {
        if ($this->exists('email') && $this->input('email') === '') {
            $this->merge(['email' => null]);
        }

        $this->merge(SkillRating::trimmed($this));
    }

    /**
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        return [
            'name' => ['sometimes', 'required', 'string', 'max:255'],
            'nickname' => ['nullable', 'string', 'max:255'],
            'avatar' => ['nullable', 'string', 'max:2048'],
            'gender' => ['sometimes', 'required', Rule::enum(MemberGender::class)],
            'birthday' => ['nullable', 'date'],
            'phone' => ['nullable', 'string', 'max:30'],
            'email' => [
                'nullable',
                'email',
                'max:255',
                Rule::unique('members', 'email')
                    ->where(fn ($query) => $query->where('club_id', $this->user()->club_id))
                    ->ignore($this->route('member')),
            ],
            'dupr_rating' => ['required', new SkillRating],
            'spcn_rating' => ['required', new SkillRating],
            'joined_at' => ['nullable', 'date'],
            'status' => ['nullable', Rule::enum(MemberStatus::class)],
        ];
    }
}
