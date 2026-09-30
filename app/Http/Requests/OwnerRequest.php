<?php

namespace App\Http\Requests;

use App\Enums\UserStatus;
use Illuminate\Foundation\Http\FormRequest;

abstract class OwnerRequest extends FormRequest
{
    public function authorize(): bool
    {
        $user = $this->user();

        return $user !== null
            && $user->status === UserStatus::Active
            && $user->isOwner();
    }
}
