<?php

namespace Tests\Unit;

use App\Models\RafflePeriod;
use Tests\TestCase;

class RafflePeriodRuleTest extends TestCase
{
    public function test_cannot_accept_transactions_when_period_is_not_active(): void
    {
        $period = RafflePeriod::factory()->create([
            'status' => 'draft',
            'start_at' => now()->subDay(),
            'end_at' => now()->addDay(),
        ]);

        $this->assertFalse($period->canAcceptTransactions());
        $period->forceDelete();
    }

    public function test_cannot_accept_transactions_before_start_or_after_end(): void
    {
        $period = RafflePeriod::factory()->active()->create([
            'start_at' => now()->addDay(),
            'end_at' => now()->addDays(5),
        ]);

        $this->assertFalse($period->canAcceptTransactions(now()));

        $period->update([
            'start_at' => now()->subDays(5),
            'end_at' => now()->subDay(),
        ]);

        $this->assertFalse($period->fresh()->canAcceptTransactions(now()));
        $period->forceDelete();
    }

    public function test_can_accept_transactions_when_active_and_within_dates(): void
    {
        $period = RafflePeriod::factory()->active()->create([
            'start_at' => now()->subDay(),
            'end_at' => now()->addDay(),
        ]);

        $this->assertTrue($period->canAcceptTransactions());
        $period->forceDelete();
    }

    public function test_coupon_calculation_uses_period_rules(): void
    {
        $period = RafflePeriod::factory()->create([
            'purchase_threshold' => 100000,
            'coupon_unit' => 100000,
            'max_coupon_per_transaction' => 3,
        ]);

        $this->assertSame(0, $period->calculateCoupons(50000));
        $this->assertSame(2, $period->calculateCoupons(250000));
        $this->assertSame(3, $period->calculateCoupons(900000));
        $period->forceDelete();
    }
}
