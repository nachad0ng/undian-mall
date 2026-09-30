<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdatePrizeRequest extends FormRequest
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
                Rule::unique('prizes', 'sequence')
                    ->where(fn ($query) => $query->where('raffle_period_id', $this->input('raffle_period_id')))
                    ->ignore($this->route('prize')),
            ],
            'status' => ['required', 'in:active,inactive'],
        ];
    }

    public function withValidator($validator)
    {
        $validator->after(function ($validator) {
            $prize = $this->route('prize');

            if ($prize && $prize->isUsedInDrawing()) {
                if ((int) $this->input('raffle_period_id') !== (int) $prize->raffle_period_id) {
                    $validator->errors()->add('raffle_period_id', 'Periode undian tidak dapat diubah karena hadiah sudah digunakan dalam pengundian.');
                }

                // Jumlah hadiah tidak boleh lebih kecil dari jumlah pemenang yang sudah ada
                $winnerCount = $prize->winners()->count();
                if ((int) $this->input('quantity') < $winnerCount) {
                    $validator->errors()->add('quantity', "Jumlah hadiah minimal {$winnerCount} sesuai jumlah pemenang yang telah diundi.");
                }

                if ((int) $this->input('sequence') !== (int) $prize->sequence) {
                    $validator->errors()->add('sequence', 'Urutan hadiah tidak dapat diubah karena sudah digunakan dalam pengundian.');
                }
            }

            if ($prize && $prize->raffleTickets()->exists()
                && (int) $this->input('ticket_digits') !== (int) $prize->ticket_digits) {
                $validator->errors()->add('ticket_digits', 'Jumlah digit tidak dapat diubah karena nomor undian sudah diterbitkan.');
            }
        });
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
