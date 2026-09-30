<?php

namespace Database\Factories;

use App\Models\BonusPointRule;
use App\Models\PaymentType;
use App\Models\RafflePeriod;
use Illuminate\Database\Eloquent\Factories\Factory;

class BonusPointRuleFactory extends Factory
{
    protected $model = BonusPointRule::class;

    public function definition(): array
    {
        return [
            'raffle_period_id' => RafflePeriod::factory(),
            'payment_type_id' => PaymentType::factory(),
            'mode' => 'add',
            'bonus_poin' => $this->faker->numberBetween(0, 10),
            'multiplier' => null,
            'is_active' => true,
        ];
    }

    public function activeBonus(int $bonusPoints = 5): static
    {
        return $this->state([
            'mode' => 'add',
            'bonus_poin' => $bonusPoints,
            'multiplier' => null,
            'is_active' => true,
        ]);
    }

    public function zeroBonus(): static
    {
        return $this->state([
            'mode' => 'add',
            'bonus_poin' => 0,
            'multiplier' => null,
            'is_active' => true,
        ]);
    }

    public function multiply(float $multiplier = 2.0): static
    {
        return $this->state([
            'mode' => 'multiply',
            'bonus_poin' => 0,
            'multiplier' => $multiplier,
            'is_active' => true,
        ]);
    }
}
