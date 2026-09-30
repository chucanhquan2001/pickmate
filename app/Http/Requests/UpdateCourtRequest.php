<?php

namespace App\Http\Requests;

use App\Enums\CourtStatus;
use Illuminate\Validation\Rule;

class UpdateCourtRequest extends ClubWriteRequest
{
    /**
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        return [
            'name' => ['sometimes', 'required', 'string', 'max:255'],
            'code' => [
                'sometimes',
                'required',
                'string',
                'max:50',
                Rule::unique('courts', 'code')
                    ->where(fn ($query) => $query->where('club_id', $this->user()->club_id))
                    ->ignore($this->route('court')),
            ],
            'status' => ['nullable', Rule::enum(CourtStatus::class)],
            'note' => ['nullable', 'string', 'max:1000'],
        ];
    }
}
