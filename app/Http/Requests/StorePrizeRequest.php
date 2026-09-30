<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StorePrizeRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->can('manage-prizes') ?? false;
    }

    public function rules(): array
    {
        return [
            'raffle_period_id' => ['required', 'integer', 'exists:raffle_periods,id'],
            'name' => ['required', 'string', 'max:255'],
            'description' => ['nullable', 'string'],
            'quantity' => ['required', 'integer', 'min:1'],
            'nominal_per_poin' => ['required', 'integer', 'min:1'],
            'ticket_digits' => ['required', 'integer', 'min:1', 'max:6'],
            'sequence' => [
                'required',
                'integer',
                'min:1',
                Rule::unique('prizes', 'sequence')->where(
                    fn ($query) => $query->where('raffle_period_id', $this->input('raffle_period_id'))
                ),
            ],
            'status' => ['required', 'in:active,inactive'],
        ];
    }

    public function messages(): array
    {
        return [
            'raffle_period_id.required' => 'Periode undian wajib dipilih.',
            'raffle_period_id.exists' => 'Periode undian tidak valid.',
            'name.required' => 'Nama hadiah wajib diisi.',
            'quantity.required' => 'Jumlah hadiah wajib diisi.',
            'nominal_per_poin.required' => 'Nominal per poin wajib diisi.',
            'sequence.required' => 'Urutan hadiah wajib diisi.',
            'sequence.unique' => 'Urutan hadiah pada periode ini sudah digunakan.',
        ];
    }
}
