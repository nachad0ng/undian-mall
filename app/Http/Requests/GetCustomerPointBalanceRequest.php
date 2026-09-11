<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class GetCustomerPointBalanceRequest extends FormRequest
{
    public function authorize(): bool
    {
        $user = $this->user();

        return $user && ($user->can('manage-prizes') || $user->can('manage-users'));
    }

    public function rules(): array
    {
        return [
            'customer_id' => ['required', 'integer', 'exists:customers,id'],
        ];
    }

    public function messages(): array
    {
        return [
            'customer_id.exists' => 'Customer tidak ditemukan.',
        ];
    }
}
