<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateBonusPointRuleRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->can('manage-prizes') ?? false;
    }

    public function rules(): array
    {
        $bonusPointRule = $this->route('bonus_point_rule');

        return [
            'raffle_period_id' => ['required', 'exists:raffle_periods,id'],
            'payment_type_id' => [
                'required',
                'exists:payment_types,id',
                Rule::unique('bonus_point_rules')->where(fn ($query) => $query
                    ->where('raffle_period_id', $this->input('raffle_period_id')))
                    ->ignore($bonusPointRule),
            ],
            'bonus_poin' => ['required', 'integer', 'min:0'],
            'is_active' => ['required', 'boolean'],
        ];
    }
}
