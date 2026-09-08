<?php

namespace Database\Factories;

use App\Models\Customer;
use App\Models\Purchase;
use App\Models\RafflePeriod;
use App\Models\Tenant;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Purchase>
 */
class PurchaseFactory extends Factory
{
    public function definition(): array
    {
        return [
            'raffle_period_id' => RafflePeriod::factory(),
            'customer_id' => Customer::factory(),
            'tenant_id' => Tenant::factory(),
            'entered_by' => User::factory(),
            'receipt_number' => 'INV-'.fake()->unique()->numerify('########'),
            'purchased_at' => now(),
            'amount' => 200000,
            'total_coupons' => 2,
        ];
    }
}
