<?php

namespace Database\Factories;

use App\Models\Tenant;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Tenant>
 */
class TenantFactory extends Factory
{
    public function definition(): array
    {
        return [
            'code' => 'TNT-'.fake()->unique()->numerify('######'),
            'name' => fake()->company(),
            'unit_number' => fake()->bothify('?-##'),
            'phone' => fake()->numerify('08##########'),
            'status' => 'active',
        ];
    }

    public function inactive(): static
    {
        return $this->state(fn () => ['status' => 'inactive']);
    }
}
