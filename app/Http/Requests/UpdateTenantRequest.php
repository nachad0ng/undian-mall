<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateTenantRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->can('manage-tenants') ?? false;
    }

    public function rules(): array
    {
        return [
            'code' => [
                'required',
                'string',
                'max:50',
                Rule::unique('tenants', 'code')->ignore($this->route('tenant')),
            ],
            'name' => ['required', 'string', 'max:255'],
            'unit_number' => ['nullable', 'string', 'max:50'],
            'phone' => ['nullable', 'string', 'max:50'],
            'status' => ['required', 'in:active,inactive'],
        ];
    }

    public function messages(): array
    {
        return [
            'code.required' => 'Kode tenant wajib diisi.',
            'code.unique' => 'Kode tenant sudah terdaftar.',
            'name.required' => 'Nama tenant wajib diisi.',
            'status.required' => 'Status tenant wajib dipilih.',
        ];
    }
}
