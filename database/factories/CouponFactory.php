<?php

namespace Database\Factories;

use App\Models\Coupon;
use App\Models\Customer;
use App\Models\Purchase;
use App\Models\RafflePeriod;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Coupon>
 */
class CouponFactory extends Factory
{
    public function definition(): array
    {
        return [
            'raffle_period_id' => RafflePeriod::factory(),
            'purchase_id' => Purchase::factory(),
            'customer_id' => Customer::factory(),
            'coupon_number' => 'CPN-'.fake()->unique()->numerify('########'),
            'status' => 'active',
        ];
    }
}
