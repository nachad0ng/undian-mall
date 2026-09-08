<?php

namespace Database\Factories;

use App\Models\Drawing;
use App\Models\Prize;
use App\Models\RafflePeriod;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Drawing>
 */
class DrawingFactory extends Factory
{
    public function definition(): array
    {
        return [
            'raffle_period_id' => RafflePeriod::factory(),
            'prize_id' => Prize::factory(),
            'executed_by' => User::factory(),
            'executed_at' => now(),
            'status' => 'completed',
            'metadata' => ['seed' => 1],
        ];
    }
}
