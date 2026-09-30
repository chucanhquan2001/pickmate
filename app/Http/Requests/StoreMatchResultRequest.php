<?php

namespace App\Http\Requests;

class StoreMatchResultRequest extends ClubWriteRequest
{
    /**
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        return [
            'sets' => ['required', 'array', 'min:1', 'max:5'],
            'sets.*.team_1_score' => ['required', 'integer', 'min:0', 'max:99'],
            'sets.*.team_2_score' => ['required', 'integer', 'min:0', 'max:99'],
        ];
    }
}
