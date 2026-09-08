<?php

namespace Tests\Feature;

use App\Models\Customer;
use App\Models\Drawing;
use App\Models\Prize;
use App\Models\Purchase;
use App\Models\RafflePeriod;
use App\Models\Tenant;
use App\Models\User;
use App\Models\Winner;
use App\Models\Coupon;
use Illuminate\Database\QueryException;
use Illuminate\Support\Facades\Schema;
use Tests\TestCase;

class DatabaseFoundationTest extends TestCase
{
    protected User $user;
    protected RafflePeriod $period;
    protected Customer $customer;
    protected Tenant $tenant;

    protected function setUp(): void
    {
        parent::setUp();

        $this->user = User::firstOrCreate(
            ['email' => 'admin@example.com'],
            ['name' => 'Admin', 'password' => bcrypt('password'), 'status' => 'active']
        );

        $this->period = RafflePeriod::firstOrCreate(
            ['code' => 'TEST-2026'],
            [
                'name' => 'Test Period 2026',
                'start_at' => now()->startOfMonth(),
                'end_at' => now()->endOfMonth(),
                'purchase_threshold' => 100000,
                'coupon_unit' => 100000,
                'status' => 'active',
                'drawing_status' => 'pending',
            ]
        );

        $this->customer = Customer::firstOrCreate(
            ['phone' => '081299990001'],
            [
                'name' => 'Budi Santoso',
                'identity_number' => '3171000000000001',
                'address' => 'Jl. Sudirman No. 1, Jakarta',
            ]
        );

        $this->tenant = Tenant::firstOrCreate(
            ['code' => 'TNT-001'],
            [
                'name' => 'Zara Mall',
                'unit_number' => 'G-10',
                'status' => 'active',
            ]
        );
    }

    public function test_all_expected_database_tables_exist(): void
    {
        $tables = [
            'raffle_periods',
            'customers',
            'tenants',
            'prizes',
            'purchases',
            'coupons',
            'drawings',
            'winners',
        ];

        foreach ($tables as $table) {
            $this->assertTrue(Schema::hasTable($table), "Table {$table} does not exist.");
        }
    }

    public function test_model_relationships_and_data_flow(): void
    {
        // 1. Prize relationship
        $prize = Prize::create([
            'raffle_period_id' => $this->period->id,
            'name' => 'Grand Prize Mobil Listrik',
            'quantity' => 1,
            'sequence' => 1,
            'status' => 'active',
        ]);
        $this->assertEquals($this->period->id, $prize->rafflePeriod->id);
        $this->assertTrue($this->period->prizes->contains($prize));

        // 2. Purchase relationship
        $purchase = Purchase::create([
            'raffle_period_id' => $this->period->id,
            'customer_id' => $this->customer->id,
            'tenant_id' => $this->tenant->id,
            'entered_by' => $this->user->id,
            'receipt_number' => 'INV-TEST-REL-001',
            'purchased_at' => now(),
            'amount' => 300000,
            'total_coupons' => 3,
        ]);
        $this->assertEquals($this->period->id, $purchase->rafflePeriod->id);
        $this->assertEquals($this->customer->id, $purchase->customer->id);
        $this->assertEquals($this->tenant->id, $purchase->tenant->id);
        $this->assertEquals($this->user->id, $purchase->enteredBy->id);
        $this->assertTrue($this->customer->purchases->contains($purchase));
        $this->assertTrue($this->tenant->purchases->contains($purchase));
        $this->assertTrue($this->user->purchasesEntered->contains($purchase));

        // 3. Coupon relationship
        $coupon = Coupon::create([
            'raffle_period_id' => $this->period->id,
            'purchase_id' => $purchase->id,
            'customer_id' => $this->customer->id,
            'coupon_number' => 'CPN-TEST-REL-001',
            'status' => 'active',
        ]);
        $this->assertEquals($purchase->id, $coupon->purchase->id);
        $this->assertEquals($this->customer->id, $coupon->customer->id);
        $this->assertEquals($this->period->id, $coupon->rafflePeriod->id);
        $this->assertTrue($purchase->coupons->contains($coupon));
        $this->assertTrue($this->customer->coupons->contains($coupon));

        // 4. Drawing relationship
        $drawing = Drawing::create([
            'raffle_period_id' => $this->period->id,
            'prize_id' => $prize->id,
            'executed_by' => $this->user->id,
            'executed_at' => now(),
            'status' => 'completed',
            'metadata' => ['seed' => 12345, 'pool_count' => 100],
        ]);
        $this->assertEquals($this->period->id, $drawing->rafflePeriod->id);
        $this->assertEquals($prize->id, $drawing->prize->id);
        $this->assertEquals($this->user->id, $drawing->executedBy->id);
        $this->assertTrue($this->user->drawingsExecuted->contains($drawing));

        // 5. Winner relationship
        $winner = Winner::create([
            'drawing_id' => $drawing->id,
            'raffle_period_id' => $this->period->id,
            'prize_id' => $prize->id,
            'coupon_id' => $coupon->id,
            'customer_id' => $this->customer->id,
            'won_at' => now(),
            'is_published' => true,
            'published_at' => now(),
        ]);
        $this->assertEquals($drawing->id, $winner->drawing->id);
        $this->assertEquals($coupon->id, $winner->coupon->id);
        $this->assertEquals($prize->id, $winner->prize->id);
        $this->assertEquals($this->customer->id, $winner->customer->id);
        $this->assertEquals($winner->id, $coupon->winner->id);

        // Cleanup test data
        $winner->delete();
        $drawing->delete();
        $coupon->delete();
        $purchase->forceDelete();
        $prize->delete();
    }

    public function test_duplicate_receipt_prevention_within_same_period_and_tenant(): void
    {
        $receiptNo = 'INV-DUP-TEST-001';

        // Struk pertama sukses
        $p1 = Purchase::create([
            'raffle_period_id' => $this->period->id,
            'customer_id' => $this->customer->id,
            'tenant_id' => $this->tenant->id,
            'entered_by' => $this->user->id,
            'receipt_number' => $receiptNo,
            'purchased_at' => now(),
            'amount' => 500000,
            'total_coupons' => 5,
        ]);

        $this->assertNotNull($p1->id);

        // Struk kedua dengan nomor struk, tenant, dan periode yang sama HARUS gagal
        $this->expectException(QueryException::class);

        try {
            Purchase::create([
                'raffle_period_id' => $this->period->id,
                'customer_id' => $this->customer->id,
                'tenant_id' => $this->tenant->id,
                'entered_by' => $this->user->id,
                'receipt_number' => $receiptNo,
                'purchased_at' => now(),
                'amount' => 500000,
                'total_coupons' => 5,
            ]);
        } finally {
            $p1->forceDelete();
        }
    }

    public function test_same_receipt_allowed_for_different_tenant(): void
    {
        $tenant2 = Tenant::firstOrCreate(
            ['code' => 'TNT-002'],
            ['name' => 'H&M Mall', 'status' => 'active']
        );

        $receiptNo = 'INV-SHARED-001';

        $p1 = Purchase::create([
            'raffle_period_id' => $this->period->id,
            'customer_id' => $this->customer->id,
            'tenant_id' => $this->tenant->id,
            'entered_by' => $this->user->id,
            'receipt_number' => $receiptNo,
            'purchased_at' => now(),
            'amount' => 200000,
            'total_coupons' => 2,
        ]);

        $p2 = Purchase::create([
            'raffle_period_id' => $this->period->id,
            'customer_id' => $this->customer->id,
            'tenant_id' => $tenant2->id,
            'entered_by' => $this->user->id,
            'receipt_number' => $receiptNo,
            'purchased_at' => now(),
            'amount' => 300000,
            'total_coupons' => 3,
        ]);

        $this->assertNotNull($p1->id);
        $this->assertNotNull($p2->id);

        $p1->forceDelete();
        $p2->forceDelete();
        $tenant2->forceDelete();
    }

    public function test_unique_coupon_number_constraint(): void
    {
        $purchase = Purchase::create([
            'raffle_period_id' => $this->period->id,
            'customer_id' => $this->customer->id,
            'tenant_id' => $this->tenant->id,
            'entered_by' => $this->user->id,
            'receipt_number' => 'INV-CPN-TEST-001',
            'purchased_at' => now(),
            'amount' => 100000,
            'total_coupons' => 1,
        ]);

        $c1 = Coupon::create([
            'raffle_period_id' => $this->period->id,
            'purchase_id' => $purchase->id,
            'customer_id' => $this->customer->id,
            'coupon_number' => 'UNIQUE-CPN-001',
            'status' => 'active',
        ]);

        $this->assertNotNull($c1->id);

        $this->expectException(QueryException::class);

        try {
            Coupon::create([
                'raffle_period_id' => $this->period->id,
                'purchase_id' => $purchase->id,
                'customer_id' => $this->customer->id,
                'coupon_number' => 'UNIQUE-CPN-001',
                'status' => 'active',
            ]);
        } finally {
            $c1->delete();
            $purchase->forceDelete();
        }
    }

    public function test_foreign_key_integrity_on_invalid_relation(): void
    {
        $this->expectException(QueryException::class);

        // Memasukkan purchase dengan customer_id tidak valid (9999999)
        Purchase::create([
            'raffle_period_id' => $this->period->id,
            'customer_id' => 9999999,
            'tenant_id' => $this->tenant->id,
            'entered_by' => $this->user->id,
            'receipt_number' => 'INV-FK-INVALID',
            'purchased_at' => now(),
            'amount' => 100000,
            'total_coupons' => 1,
        ]);
    }

    public function test_soft_deletes_working_on_supported_models(): void
    {
        $customer = Customer::create([
            'name' => 'Temporary Customer',
            'phone' => '081288887777',
        ]);

        $customerId = $customer->id;
        $customer->delete();

        $this->assertSoftDeleted('customers', ['id' => $customerId]);
        $this->assertNull(Customer::find($customerId));
        $this->assertNotNull(Customer::withTrashed()->find($customerId));

        $customer->forceDelete();
    }
}
