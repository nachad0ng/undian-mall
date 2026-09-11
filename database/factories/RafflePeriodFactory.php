<?php

namespace Database\Factories;

use App\Models\RafflePeriod;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<RafflePeriod>
 */
class RafflePeriodFactory extends Factory
{
    public function definition(): array
    {
        $start = now()->subDays(2)->startOfDay();

        return [
            'code' => 'PRD-'.fake()->unique()->numerify('######'),
            'name' => fake()->sentence(3),
            'description' => fake()->sentence(),
            'start_at' => $start,
            'end_at' => now()->addDays(10)->endOfDay(),
            'status' => 'draft',
            'drawing_status' => 'pending',
        ];
    }

    public function active(): static
    {
        return $this->state(fn () => ['status' => 'active']);
    }

    public function inactive(): static
    {
        return $this->state(fn () => ['status' => 'inactive']);
    }

    public function closed(): static
    {
        return $this->state(fn () => ['status' => 'closed']);
    }
}
