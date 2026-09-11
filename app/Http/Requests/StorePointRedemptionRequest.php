<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StorePointRedemptionRequest extends FormRequest
{
    public function authorize(): bool
    {
        $user = $this->user();

        // Hanya user yang punya permission manage-prizes atau manage-users yang bisa proses
        return $user
            && ($user->can('manage-prizes') || $user->can('manage-users'));
    }

    public function rules(): array
    {
        return [
            'customer_id' => ['required', 'integer', 'exists:customers,id'],
            'period_id' => ['required', 'integer', 'exists:raffle_periods,id'],
            'purchase_id' => ['nullable', 'integer', 'exists:purchases,id'],
            'receipt_number' => [
                'required_without:purchase_id',
                'nullable',
                'string',
                'max:100',
                Rule::unique('purchases')->where(fn ($query) => $query
                    ->where('raffle_period_id', $this->input('period_id'))
                    ->where('tenant_id', $this->input('tenant_id'))),
            ],
            'tenant_id' => ['required_without:purchase_id', 'nullable', 'integer', Rule::exists('tenants', 'id')->where('status', 'active')],
            'purchased_at' => ['required_without:purchase_id', 'nullable', 'date'],
            'amount' => ['required_without:purchase_id', 'nullable', 'integer', 'min:1'],
            'payment_type_id' => ['nullable', 'integer', Rule::exists('payment_types', 'id')->where('is_active', true)],
            'prize_id' => ['required', 'integer', 'exists:prizes,id'],
            'cs_id' => ['nullable', 'integer', 'exists:users,id'],
            'notes' => ['nullable', 'string', 'max:1000'],
        ];
    }

    public function messages(): array
    {
        return [
            'customer_id.exists' => 'Customer tidak ditemukan.',
            'purchase_id.exists' => 'Struk/transaksi tidak ditemukan.',
            'period_id.exists' => 'Periode tidak ditemukan.',
            'receipt_number.unique' => 'Nomor struk sudah terdaftar pada periode dan tenant tersebut.',
            'tenant_id.exists' => 'Tenant tidak aktif atau tidak ditemukan.',
            'payment_type_id.exists' => 'Tipe pembayaran tidak aktif atau tidak ditemukan.',
            'prize_id.exists' => 'Hadiah tidak ditemukan.',
            'cs_id.exists' => 'Petugas CS tidak ditemukan.',
        ];
    }
}
