<?php

namespace Database\Factories;

use App\Models\PaymentType;
use Illuminate\Database\Eloquent\Factories\Factory;

class PaymentTypeFactory extends Factory
{
    protected $model = PaymentType::class;

    public function definition(): array
    {
        return [
            'code' => $this->faker->unique()->bothify('????_####'),
            'name' => $this->faker->words(3, true),
            'description' => null,
            'is_active' => true,
        ];
    }

    public function cash(): static
    {
        return $this->state(fn () => [
            'code' => 'TUNAI',
            'name' => 'Tunai',
        ]);
    }

    public function kartuMega(): static
    {
        return $this->state(fn () => [
            'code' => 'KARTU_MEGA',
            'name' => 'Kartu Kredit Bank Mega',
        ]);
    }

    public function kartuMall(): static
    {
        return $this->state(fn () => [
            'code' => 'KARTU_MALL',
            'name' => 'Kartu Mall',
        ]);
    }

    public function debitLain(): static
    {
        return $this->state(fn () => [
            'code' => 'DEBIT_LAIN',
            'name' => 'Debit Bank Lain',
        ]);
    }
}
