<?php

namespace App\Http\Requests;

use App\Enums\MinigameFormat;
use Illuminate\Validation\Rule;

class UpdateMinigameRequest extends ClubWriteRequest
{
    /**
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        return [
            'name' => ['sometimes', 'required', 'string', 'max:255'],
            'description' => ['nullable', 'string', 'max:2000'],
            'format' => ['sometimes', 'required', Rule::enum(MinigameFormat::class)],
            'starts_at' => ['nullable', 'date'],
            'ends_at' => ['nullable', 'date', 'after_or_equal:starts_at'],
        ];
    }
}
