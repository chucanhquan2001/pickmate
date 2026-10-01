<?php

namespace App\Http\Requests;

class UpdateUserRoleRequest extends ClubWriteRequest
{
    /**
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        return [
            'role' => ['required', 'in:admin,member'],
        ];
    }
}
