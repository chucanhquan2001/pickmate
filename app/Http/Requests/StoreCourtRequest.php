<?php

namespace App\Http\Requests;

use App\Enums\CourtStatus;
use Illuminate\Validation\Rule;

class StoreCourtRequest extends ClubWriteRequest
{
    /**
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        return [
            'name' => ['required', 'string', 'max:255'],
            'code' => [
                'required',
                'string',
                'max:50',
                Rule::unique('courts', 'code')->where(fn ($query) => $query->where('club_id', $this->user()->club_id)),
            ],
            'status' => ['nullable', Rule::enum(CourtStatus::class)],
            'note' => ['nullable', 'string', 'max:1000'],
        ];
    }
}
