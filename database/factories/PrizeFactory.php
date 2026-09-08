<?php

namespace Database\Factories;

use App\Models\Prize;
use App\Models\RafflePeriod;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Prize>
 */
class PrizeFactory extends Factory
{
    public function definition(): array
    {
        return [
            'raffle_period_id' => RafflePeriod::factory(),
            'name' => fake()->words(3, true),
            'description' => fake()->sentence(),
            'quantity' => 1,
            'sequence' => fake()->unique()->numberBetween(1, 9999),
            'status' => 'active',
        ];
    }
}
