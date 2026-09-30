<?php

namespace App\Http\Requests;

trait ValidatesBonusPointRuleMode
{
    protected function prepareForValidation(): void
    {
        if ($this->input('mode') === 'add') {
            $this->merge(['multiplier' => null]);
        } elseif ($this->input('mode') === 'multiply') {
            $this->merge(['bonus_poin' => 0]);
        }
    }

    /**
     * @return array<string, mixed>
     */
    protected function bonusModeRules(): array
    {
        return [
            'mode' => ['required', 'in:add,multiply'],
            'bonus_poin' => ['required_if:mode,add', 'nullable', 'integer', 'min:0'],
            'multiplier' => ['required_if:mode,multiply', 'nullable', 'numeric', 'min:1', 'max:100'],
        ];
    }

    /**
     * @return array<string, string>
     */
    protected function bonusModeMessages(): array
    {
        return [
            'mode.required' => 'Mode bonus wajib dipilih.',
            'mode.in' => 'Mode bonus tidak valid.',
            'bonus_poin.required_if' => 'Bonus poin wajib diisi untuk mode tambah.',
            'multiplier.required_if' => 'Pengali wajib diisi untuk mode kali lipat.',
            'multiplier.min' => 'Pengali minimal 1.',
        ];
    }
}
