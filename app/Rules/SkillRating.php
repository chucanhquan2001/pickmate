<?php

namespace App\Rules;

use Closure;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;

class SkillRating implements ValidationRule
{
    /**
     * @return array{dupr_rating?: string, spcn_rating?: string}
     */
    public static function trimmed(FormRequest $request): array
    {
        $trimmed = [];

        foreach (['dupr_rating', 'spcn_rating'] as $field) {
            $value = $request->input($field);

            if (is_string($value)) {
                $trimmed[$field] = trim($value);
            }
        }

        return $trimmed;
    }

    public function validate(string $attribute, mixed $value, Closure $fail): void
    {
        if (! is_string($value) || preg_match('/^(?:[1-7]\.[0-9]|8\.0)$/', $value) !== 1) {
            $fail('Điểm phải có đúng một chữ số thập phân, từ 1.0 đến 8.0.');
        }
    }
}
