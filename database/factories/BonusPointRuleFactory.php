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
            'bonus_poin' => $this->faker->numberBetween(0, 10),
            'is_active' => true,
        ];
    }

    public function activeBonus(int $bonusPoints = 5): static
    {
        return $this->state([
            'bonus_poin' => $bonusPoints,
            'is_active' => true,
        ]);
    }

    public function zeroBonus(): static
    {
        return $this->state([
            'bonus_poin' => 0,
            'is_active' => true,
        ]);
    }
}
