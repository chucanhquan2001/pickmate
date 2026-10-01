<?php

namespace App\Http\Requests;

use App\Http\Requests\Concerns\NormalizesScheduledAt;

class UpdateMatchRequest extends ClubWriteRequest
{
    use NormalizesScheduledAt;

    protected function prepareForValidation(): void
    {
        if ($this->input('court_id') === '') {
            $this->merge(['court_id' => null]);
        }

        $this->normalizeScheduledAt();
    }

    /**
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        return [
            'scheduled_at' => ['sometimes', 'required', 'date'],
            'court_id' => ['nullable', 'integer'],
            'team_1' => ['required_with:team_2', 'array', 'min:1', 'max:2'],
            'team_1.*' => ['integer', 'distinct'],
            'team_2' => ['required_with:team_1', 'array', 'min:1', 'max:2'],
            'team_2.*' => ['integer', 'distinct'],
        ];
    }
}
