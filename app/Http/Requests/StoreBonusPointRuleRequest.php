<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreBonusPointRuleRequest extends FormRequest
{
    use ValidatesBonusPointRuleMode;

    public function authorize(): bool
    {
        return $this->user()?->can('manage-prizes') ?? false;
    }

    public function rules(): array
    {
        return array_merge([
            'raffle_period_id' => ['required', 'exists:raffle_periods,id'],
            'payment_type_id' => [
                'required',
                'exists:payment_types,id',
                Rule::unique('bonus_point_rules')->where(fn ($query) => $query
                    ->where('raffle_period_id', $this->input('raffle_period_id'))),
            ],
            'is_active' => ['required', 'boolean'],
        ], $this->bonusModeRules());
    }

    public function messages(): array
    {
        return $this->bonusModeMessages();
    }
}
