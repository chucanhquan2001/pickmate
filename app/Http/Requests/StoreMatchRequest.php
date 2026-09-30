<?php

namespace App\Http\Requests;

class StoreMatchRequest extends ClubWriteRequest
{
    protected function prepareForValidation(): void
    {
        if ($this->input('court_id') === '') {
            $this->merge(['court_id' => null]);
        }
    }

    /**
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        return [
            'scheduled_at' => ['required', 'date'],
            'court_id' => ['nullable', 'integer'],
            'team_1' => ['required', 'array', 'min:1', 'max:2'],
            'team_1.*' => ['integer', 'distinct'],
            'team_2' => ['required', 'array', 'min:1', 'max:2'],
            'team_2.*' => ['integer', 'distinct'],
        ];
    }
}
