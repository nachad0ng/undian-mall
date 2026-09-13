<?php

namespace Tests\Feature;

use App\Models\BonusPointRule;
use App\Models\Customer;
use App\Models\PaymentType;
use App\Models\Prize;
use App\Models\Purchase;
use App\Models\RafflePeriod;
use App\Models\User;
use App\Services\PointCalculationService;
use App\Services\PointRedemptionService;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class PointRedemptionIntegrationTest extends TestCase
{
    use RefreshDatabase;

    private PointRedemptionService $service;

    protected function setUp(): void
    {
        parent::setUp();
        $this->service = new PointRedemptionService(
            new PointCalculationService
        );
    }

    public function test_penukaran_poin_sukses_dan_struk_terkunci(): void
    {
        $period = RafflePeriod::factory()->create([
            'name' => 'Promo September',
            'start_at' => Carbon::now()->subDay(),
            'end_at' => Carbon::now()->addDays(30),
            'exchange_start_at' => Carbon::now()->subDay(),
            'exchange_end_at' => Carbon::now()->addDays(30),
            'status' => 'active',
            'drawing_status' => 'pending',
        ]);

        $prize = Prize::factory()->create([
            'raffle_period_id' => $period->id,
            'name' => 'Sepeda',
            'nominal_per_poin' => 1_000_000,
            'active_for_exchange' => true,
            'status' => 'active',
        ]);

        $customer = Customer::factory()->create(['name' => 'Budi']);
        $purchase = Purchase::factory()->create([
            'raffle_period_id' => $period->id,
            'customer_id' => $customer->id,
            'exchange_status' => 'belum',
            'purchased_at' => Carbon::now()->subHours(2),
            'amount' => 3_500_000,
            'receipt_number' => 'STRUK-001',
        ]);

        $cs = User::factory()->create(['name' => 'CS Agus']);

        $redemption = $this->service->redeem(
            $customer,
            $purchase,
            $prize,
            $cs->id,
            'Test redemption'
        );

        // Cek status struk berubah jadi 'sudah'
        $purchase->refresh();
        $this->assertSame('sudah', $purchase->exchange_status);

        // Cek redemption tercatat
        $this->assertSame($customer->id, $redemption->customer_id);
        $this->assertSame($prize->id, $redemption->prize_id);
        $this->assertSame($purchase->id, $redemption->purchase_id);
        $this->assertSame($cs->id, $redemption->cs_id);
        $this->assertSame('success', $redemption->status);

        // Cek total poin: 3 poin dari nominal + sisa 500.000 hangus
        $this->assertSame(3, $redemption->total_poin_didapat);
    }

    public function test_kalkulasi_redemption_disimpan_sebagai_snapshot_historis(): void
    {
        $period = RafflePeriod::factory()->create([
            'start_at' => Carbon::now()->subDay(),
            'end_at' => Carbon::now()->addDays(30),
            'exchange_start_at' => Carbon::now()->subDay(),
            'exchange_end_at' => Carbon::now()->addDays(30),
            'status' => 'active',
            'drawing_status' => 'pending',
        ]);
        $paymentType = PaymentType::firstOrCreate(
            ['code' => 'KARTU_MEGA'],
            ['name' => 'Kartu Kredit Bank Mega', 'is_active' => true],
        );
        $bonusRule = BonusPointRule::factory()->create([
            'raffle_period_id' => $period->id,
            'payment_type_id' => $paymentType->id,
            'bonus_poin' => 2,
            'is_active' => true,
        ]);
        $prize = Prize::factory()->create([
            'raffle_period_id' => $period->id,
            'nominal_per_poin' => 1_000_000,
            'active_for_exchange' => true,
            'status' => 'active',
        ]);
        $customer = Customer::factory()->create();
        $purchase = Purchase::factory()->create([
            'raffle_period_id' => $period->id,
            'customer_id' => $customer->id,
            'payment_type_id' => $paymentType->id,
            'exchange_status' => 'belum',
            'purchased_at' => Carbon::now()->subHours(2),
            'amount' => 3_500_000,
        ]);

        $redemption = $this->service->redeem($customer, $purchase, $prize);

        $this->assertSame(1_000_000, $redemption->nominal_per_poin_snapshot);
        $this->assertSame(3, $redemption->poin_dari_nominal);
        $this->assertSame(2, $redemption->poin_bonus_pembayaran);
        $this->assertSame($bonusRule->id, $redemption->bonus_rule_id_snapshot);
        $this->assertSame('KARTU_MEGA', $redemption->payment_type_code_snapshot);
        $this->assertSame('Kartu Kredit Bank Mega', $redemption->payment_type_name_snapshot);
        $this->assertDatabaseHas('audit_logs', [
            'action' => 'point_redemption.created',
            'auditable_type' => $redemption::class,
            'auditable_id' => $redemption->id,
        ]);

        $prize->update(['nominal_per_poin' => 500_000]);
        $bonusRule->update(['bonus_poin' => 9]);

        $this->assertSame(1_000_000, $redemption->fresh()->nominal_per_poin_snapshot);
        $this->assertSame(3, $redemption->fresh()->poin_dari_nominal);
        $this->assertSame(2, $redemption->fresh()->poin_bonus_pembayaran);
        $this->assertSame(5, $redemption->fresh()->total_poin_didapat);
    }

    public function test_struk_yang_sudah_ditukar_tidak_bisa_dipakai_kembali(): void
    {
        $period = RafflePeriod::factory()->create([
            'start_at' => Carbon::now()->subDay(),
            'end_at' => Carbon::now()->addDays(30),
            'exchange_start_at' => Carbon::now()->subDay(),
            'exchange_end_at' => Carbon::now()->addDays(30),
            'status' => 'active',
            'drawing_status' => 'pending',
        ]);

        $prize = Prize::factory()->create([
            'raffle_period_id' => $period->id,
            'name' => 'Motor',
            'nominal_per_poin' => 250_000,
            'active_for_exchange' => true,
            'status' => 'active',
        ]);

        $customer = Customer::factory()->create(['name' => 'Siti']);
        $purchase = Purchase::factory()->create([
            'raffle_period_id' => $period->id,
            'customer_id' => $customer->id,
            'exchange_status' => 'belum',
            'purchased_at' => Carbon::now()->subHours(2),
            'amount' => 1_000_000,
        ]);

        // Redemption pertama: sukses
        $this->service->redeem($customer, $purchase, $prize);

        // Coba redemption kedua dengan struk yang sama: harus gagal
        $this->expectException(\RuntimeException::class);
        $this->expectExceptionMessage('Struk sudah pernah ditukar poinnya.');
        $this->service->redeem($customer, $purchase, $prize);
    }

    public function test_struk_diluar_rentang_periode_ditolak(): void
    {
        $period = RafflePeriod::factory()->create([
            'start_at' => Carbon::now(),
            'end_at' => Carbon::now()->addDays(30),
            'exchange_start_at' => Carbon::now(),
            'exchange_end_at' => Carbon::now()->addDays(30),
            'status' => 'active',
            'drawing_status' => 'pending',
        ]);

        $prize = Prize::factory()->create([
            'raffle_period_id' => $period->id,
            'name' => 'Kamera',
            'nominal_per_poin' => 500_000,
            'active_for_exchange' => true,
            'status' => 'active',
        ]);

        $customer = Customer::factory()->create(['name' => 'Rini']);
        $purchase = Purchase::factory()->create([
            'raffle_period_id' => $period->id,
            'customer_id' => $customer->id,
            'exchange_status' => 'belum',
            'purchased_at' => Carbon::now()->subDays(5), // diluar periode
            'amount' => 1_000_000,
        ]);

        $this->expectException(\RuntimeException::class);
        $this->expectExceptionMessage('Tanggal belanja tidak masuk rentang periode event.');
        $this->service->redeem($customer, $purchase, $prize);
    }

    public function test_hadiah_dan_struk_harus_satu_periode(): void
    {
        $period1 = RafflePeriod::factory()->create([
            'name' => 'Event A',
            'start_at' => Carbon::now()->subDay(),
            'end_at' => Carbon::now()->addDays(10),
            'exchange_start_at' => Carbon::now()->subDay(),
            'exchange_end_at' => Carbon::now()->addDays(10),
            'status' => 'active',
            'drawing_status' => 'pending',
        ]);

        $period2 = RafflePeriod::factory()->create([
            'name' => 'Event B',
            'start_at' => Carbon::now()->addDays(40),
            'end_at' => Carbon::now()->addDays(60),
            'status' => 'active',
            'drawing_status' => 'pending',
        ]);

        $prize = Prize::factory()->create([
            'raffle_period_id' => $period2->id, // hadiah dari periode 2
            'name' => 'Laptop',
            'nominal_per_poin' => 1_000_000,
            'active_for_exchange' => true,
            'status' => 'active',
        ]);

        $customer = Customer::factory()->create(['name' => 'Wati']);
        $purchase = Purchase::factory()->create([
            'raffle_period_id' => $period1->id, // struk dari periode 1
            'customer_id' => $customer->id,
            'exchange_status' => 'belum',
            'purchased_at' => Carbon::now()->subHours(2),
            'amount' => 2_000_000,
        ]);

        $this->expectException(\RuntimeException::class);
        $this->expectExceptionMessage('Hadiah dan struk harus dari periode yang sama.');
        $this->service->redeem($customer, $purchase, $prize);
    }

    public function test_poin_tersimpan_terpisah_per_hadiah(): void
    {
        $period = RafflePeriod::factory()->create([
            'start_at' => Carbon::now()->subDay(),
            'end_at' => Carbon::now()->addDays(30),
            'exchange_start_at' => Carbon::now()->subDay(),
            'exchange_end_at' => Carbon::now()->addDays(30),
            'status' => 'active',
            'drawing_status' => 'pending',
        ]);

        $prizeMobil = Prize::factory()->create([
            'raffle_period_id' => $period->id,
            'name' => 'Mobil',
            'nominal_per_poin' => 1_000_000,
            'active_for_exchange' => true,
            'status' => 'active',
        ]);

        $prizeMotor = Prize::factory()->create([
            'raffle_period_id' => $period->id,
            'name' => 'Motor',
            'nominal_per_poin' => 250_000,
            'active_for_exchange' => true,
            'status' => 'active',
        ]);

        $customer = Customer::factory()->create(['name' => 'Andi']);

        // 2 struk berbeda untuk hadiah berbeda
        $purchase1 = Purchase::factory()->create([
            'raffle_period_id' => $period->id,
            'customer_id' => $customer->id,
            'exchange_status' => 'belum',
            'purchased_at' => Carbon::now()->subHours(3),
            'amount' => 2_000_000,
            'receipt_number' => 'STRUK-A1',
        ]);

        $purchase2 = Purchase::factory()->create([
            'raffle_period_id' => $period->id,
            'customer_id' => $customer->id,
            'exchange_status' => 'belum',
            'purchased_at' => Carbon::now()->subHours(2),
            'amount' => 1_000_000,
            'receipt_number' => 'STRUK-A2',
        ]);

        // Tukar poin untuk Mobil (struk 1): 2 poin
        $this->service->redeem($customer, $purchase1, $prizeMobil);

        // Tukar poin untuk Motor (struk 2): 4 poin (1.000.000 / 250.000)
        $this->service->redeem($customer, $purchase2, $prizeMotor);

        // Cek saldo per hadiah
        $balanceMobil = $customer->getPointBalanceForPeriodAndPrize($period->id, $prizeMobil->id);
        $balanceMotor = $customer->getPointBalanceForPeriodAndPrize($period->id, $prizeMotor->id);

        $this->assertSame(2, $balanceMobil->total_poin);
        $this->assertSame(4, $balanceMotor->total_poin);
    }

    public function test_saldo_poin_customer_dapat_dilihat(): void
    {
        $period = RafflePeriod::factory()->create([
            'start_at' => Carbon::now()->subDay(),
            'end_at' => Carbon::now()->addDays(30),
            'exchange_start_at' => Carbon::now()->subDay(),
            'exchange_end_at' => Carbon::now()->addDays(30),
            'status' => 'active',
            'drawing_status' => 'pending',
        ]);

        $prize = Prize::factory()->create([
            'raffle_period_id' => $period->id,
            'name' => 'Tulip',
            'nominal_per_poin' => 500_000,
            'active_for_exchange' => true,
            'status' => 'active',
        ]);

        $customer = Customer::factory()->create(['name' => 'Kiki']);

        $purchase = Purchase::factory()->create([
            'raffle_period_id' => $period->id,
            'customer_id' => $customer->id,
            'exchange_status' => 'belum',
            'purchased_at' => Carbon::now()->subHours(2),
            'amount' => 2_250_000, // 4 poin, sisa 250.000 hangus
        ]);

        $this->service->redeem($customer, $purchase, $prize);

        $balances = $this->service->getCustomerPointBalances($customer, $period);

        $this->assertCount(1, $balances);
        $this->assertSame($prize->id, $balances[0]['prize_id']);
        $this->assertSame('Tulip', $balances[0]['prize_name']);
        $this->assertSame(4, $balances[0]['total_poin']);
    }

    public function test_customer_dapat_ikut_banyak_hadiah_dengan_struk_bedanya(): void
    {
        $period = RafflePeriod::factory()->create([
            'start_at' => Carbon::now()->subDay(),
            'end_at' => Carbon::now()->addDays(30),
            'exchange_start_at' => Carbon::now()->subDay(),
            'exchange_end_at' => Carbon::now()->addDays(30),
            'status' => 'active',
            'drawing_status' => 'pending',
        ]);

        $prizeA = Prize::factory()->create([
            'raffle_period_id' => $period->id,
            'name' => 'Hadiah A',
            'nominal_per_poin' => 1_000_000,
            'active_for_exchange' => true,
            'status' => 'active',
        ]);

        $prizeB = Prize::factory()->create([
            'raffle_period_id' => $period->id,
            'name' => 'Hadiah B',
            'nominal_per_poin' => 500_000,
            'active_for_exchange' => true,
            'status' => 'active',
        ]);

        $customer = Customer::factory()->create(['name' => 'Irfan']);

        $strukA = Purchase::factory()->create([
            'raffle_period_id' => $period->id,
            'customer_id' => $customer->id,
            'exchange_status' => 'belum',
            'purchased_at' => Carbon::now()->subHours(3),
            'amount' => 3_000_000,
            'receipt_number' => 'SA-001',
        ]);

        $strukB = Purchase::factory()->create([
            'raffle_period_id' => $period->id,
            'customer_id' => $customer->id,
            'exchange_status' => 'belum',
            'purchased_at' => Carbon::now()->subHours(2),
            'amount' => 2_000_000,
            'receipt_number' => 'SB-001',
        ]);

        // Tukar hadiah A: 3 poin
        $this->service->redeem($customer, $strukA, $prizeA);
        // Tukar hadiah B: 4 poin
        $this->service->redeem($customer, $strukB, $prizeB);

        $balanceA = $customer->getPointBalanceForPeriodAndPrize($period->id, $prizeA->id);
        $balanceB = $customer->getPointBalanceForPeriodAndPrize($period->id, $prizeB->id);

        $this->assertSame(3, $balanceA->total_poin);
        $this->assertSame(4, $balanceB->total_poin);
    }
}
