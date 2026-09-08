<?php

namespace Database\Factories;

use App\Models\Coupon;
use App\Models\Customer;
use App\Models\Drawing;
use App\Models\Prize;
use App\Models\RafflePeriod;
use App\Models\Winner;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Winner>
 */
class WinnerFactory extends Factory
{
    public function definition(): array
    {
        return [
            'drawing_id' => Drawing::factory(),
            'raffle_period_id' => RafflePeriod::factory(),
            'prize_id' => Prize::factory(),
            'coupon_id' => Coupon::factory(),
            'customer_id' => Customer::factory(),
            'won_at' => now(),
            'is_published' => false,
        ];
    }
}
