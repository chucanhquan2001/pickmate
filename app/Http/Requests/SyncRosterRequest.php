<?php

namespace App\Http\Requests;

class SyncRosterRequest extends ClubWriteRequest
{
    /**
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        return [
            'member_ids' => ['present', 'array'],
            'member_ids.*' => ['integer', 'distinct'],
        ];
    }
}
