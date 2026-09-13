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
            'nominal_per_poin' => 1000000,
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
        ]);
        $this->assertEquals($this->period->id, $purchase->rafflePeriod->id);
        $this->assertEquals($this->customer->id, $purchase->customer->id);
        $this->assertEquals($this->tenant->id, $purchase->tenant->id);
        $this->assertEquals($this->user->id, $purchase->enteredBy->id);
        $this->assertTrue($this->customer->purchases->contains($purchase));
        $this->assertTrue($this->tenant->purchases->contains($purchase));
        $this->assertTrue($this->user->purchasesEntered->contains($purchase));

        // 3. Drawing relationship
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

        // 4. Winner relationship
        $winner = Winner::create([
            'drawing_id' => $drawing->id,
            'raffle_period_id' => $this->period->id,
            'prize_id' => $prize->id,
            'customer_id' => $this->customer->id,
            'won_at' => now(),
            'is_published' => true,
            'published_at' => now(),
        ]);
        $this->assertEquals($drawing->id, $winner->drawing->id);
        $this->assertEquals($prize->id, $winner->prize->id);
        $this->assertEquals($this->customer->id, $winner->customer->id);

        // Cleanup test data
        $winner->delete();
        $drawing->delete();
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
        ]);

        $p2 = Purchase::create([
            'raffle_period_id' => $this->period->id,
            'customer_id' => $this->customer->id,
            'tenant_id' => $tenant2->id,
            'entered_by' => $this->user->id,
            'receipt_number' => $receiptNo,
            'purchased_at' => now(),
            'amount' => 300000,
        ]);

        $this->assertNotNull($p1->id);
        $this->assertNotNull($p2->id);

        $p1->forceDelete();
        $p2->forceDelete();
        $tenant2->forceDelete();
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
        ]);
    }

    public function test_soft_deletes_working_on_supported_models(): void
    {
        $customer = Customer::create([
            'name' => 'Temporary Customer',
            'phone' => '081288887777',
            'identity_number' => '3171000000000099',
        ]);

        $customerId = $customer->id;
        $customer->delete();

        $this->assertSoftDeleted('customers', ['id' => $customerId]);
        $this->assertNull(Customer::find($customerId));
        $this->assertNotNull(Customer::withTrashed()->find($customerId));

        $customer->forceDelete();
    }
}
