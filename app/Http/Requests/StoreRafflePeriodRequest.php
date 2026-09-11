<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreRafflePeriodRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->can('manage-periods') ?? false;
    }

    public function rules(): array
    {
        return [
            'code' => ['required', 'string', 'max:50', 'unique:raffle_periods,code'],
            'name' => ['required', 'string', 'max:255'],
            'description' => ['nullable', 'string'],
            'start_at' => ['required', 'date'],
            'end_at' => ['required', 'date', 'after_or_equal:start_at'],
            'status' => ['required', 'in:draft,active,inactive,closed'],
        ];
    }

    public function messages(): array
    {
        return [
            'code.required' => 'Kode periode wajib diisi.',
            'code.unique' => 'Kode periode sudah digunakan.',
            'name.required' => 'Nama periode wajib diisi.',
            'start_at.required' => 'Tanggal mulai periode wajib diisi.',
            'end_at.required' => 'Tanggal berakhir periode wajib diisi.',
            'end_at.after_or_equal' => 'Tanggal berakhir harus sama atau setelah tanggal mulai.',
        ];
    }
}
