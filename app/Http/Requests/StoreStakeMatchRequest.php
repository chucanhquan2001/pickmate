<?php

namespace App\Http\Requests;

use App\Enums\ScoringType;
use App\Enums\StakeFormat;
use App\Enums\UserStatus;
use App\Http\Requests\Concerns\NormalizesScheduledAt;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreStakeMatchRequest extends FormRequest
{
    use NormalizesScheduledAt;

    protected function prepareForValidation(): void
    {
        $this->normalizeScheduledAt();
    }

    public function authorize(): bool
    {
        $user = $this->user();

        return $user !== null
            && $user->status === UserStatus::Active
            && $user->currentMembership() !== null;
    }

    /**
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        return [
            'format' => ['required', Rule::enum(StakeFormat::class)],
            'scoring_type' => ['required', Rule::enum(ScoringType::class)],
            'scheduled_at' => ['required', 'date'],
            'court_id' => ['required', 'integer'],
            'item' => ['required', 'string', 'max:100'],
            'quantity' => ['required', 'integer', 'min:1', 'max:10000'],
            'expected_amount' => ['required', 'integer', 'min:0', 'max:999999999'],
            'team_1' => ['required', 'array', 'min:1', 'max:2'],
            'team_1.*' => ['integer', 'distinct'],
            'team_2' => ['required', 'array', 'min:1', 'max:2'],
            'team_2.*' => ['integer', 'distinct'],
        ];
    }

    /**
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'format.required' => 'Chọn đơn hoặc đôi.',
            'scoring_type.required' => 'Chọn loại tính điểm.',
            'scheduled_at.required' => 'Chọn thời gian.',
            'court_id.required' => 'Chọn sân.',
            'item.required' => 'Điền vật phẩm.',
            'quantity.required' => 'Điền số lượng.',
            'quantity.min' => 'Số lượng phải từ 1.',
            'expected_amount.required' => 'Điền tiền dự kiến.',
            'expected_amount.min' => 'Tiền dự kiến không được âm.',
            'team_1.required' => 'Chọn đội 1.',
            'team_2.required' => 'Chọn đội 2.',
        ];
    }
}
