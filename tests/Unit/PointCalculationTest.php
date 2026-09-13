<?php

namespace Tests\Unit;

use App\Models\BonusPointRule;
use App\Models\Customer;
use App\Models\PaymentType;
use App\Models\Prize;
use App\Models\Purchase;
use App\Models\RafflePeriod;
use App\Services\PointCalculationService;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class PointCalculationTest extends TestCase
{
    use RefreshDatabase;

    protected $seed = false;

    private PointCalculationService $service;

    protected function setUp(): void
    {
        parent::setUp();
        $this->service = new PointCalculationService;
    }

    public function test_hitung_poin_dari_nominal_belanja_menggunakan_pembagian_bulat_kebawah(): void
    {
        // Hadiah dengan rule: Rp1.000.000 = 1 poin
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
            'name' => 'Mobil Toyota',
            'nominal_per_poin' => 1_000_000,
            'active_for_exchange' => true,
            'status' => 'active',
        ]);

        $purchase = Purchase::factory()->create([
            'raffle_period_id' => $period->id,
            'customer_id' => Customer::factory()->create()->id,
            'exchange_status' => 'belum',
            'purchased_at' => Carbon::now()->subHours(2),
            'amount' => 5_500_000, // 5 poin + sisa 500.000 (hangus)
        ]);

        $result = $this->service->calculatePointsForPurchase($purchase, $prize, $period);

        $this->assertSame(1_000_000, $result['nominal_per_poin']);
        $this->assertSame(5, $result['points_from_amount']); // FLOOR(5.500.000 / 1.000.000)
        $this->assertSame(0, $result['bonus_points']);
        $this->assertSame(5, $result['total_points']);
        $this->assertSame(500_000, $result['unused_remainder']); // sisa hangus
    }

    public function test_sisa_nominal_tidak_genap_kelipatan_hangus_tidak_dicarry_over(): void
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
            'name' => 'Motor Honda',
            'nominal_per_poin' => 250_000,
            'active_for_exchange' => true,
            'status' => 'active',
        ]);

        // Belanja Rp1.000.000 → 4 poin. Sisa 0.
        $purchase = Purchase::factory()->create([
            'raffle_period_id' => $period->id,
            'customer_id' => Customer::factory()->create()->id,
            'exchange_status' => 'belum',
            'purchased_at' => Carbon::now()->subHours(2),
            'amount' => 1_000_000,
        ]);

        $result = $this->service->calculatePointsForPurchase($purchase, $prize, $period);

        $this->assertSame(4, $result['points_from_amount']);
        $this->assertSame(0, $result['unused_remainder']);
        $this->assertSame(4, $result['total_points']);

        // Belanja Rp1.100.000 → 4 poin. Sisa 100.000 (hangus).
        $purchase2 = Purchase::factory()->create([
            'raffle_period_id' => $period->id,
            'customer_id' => Customer::factory()->create()->id,
            'exchange_status' => 'belum',
            'purchased_at' => Carbon::now()->subHours(1),
            'amount' => 1_100_000,
        ]);

        $result2 = $this->service->calculatePointsForPurchase($purchase2, $prize, $period);

        $this->assertSame(4, $result2['points_from_amount']);
        $this->assertSame(100_000, $result2['unused_remainder']);
        $this->assertSame(4, $result2['total_points']);
    }

    public function test_poin_dari_nominal_saja_tanpa_bonus_pembayaran(): void
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
            'name' => 'Kulkas',
            'nominal_per_poin' => 500_000,
            'active_for_exchange' => true,
            'status' => 'active',
        ]);

        $customer = Customer::factory()->create();
        $purchase = Purchase::factory()->create([
            'raffle_period_id' => $period->id,
            'customer_id' => $customer->id,
            'payment_type_id' => null, // tanpa tipe pembayaran
            'exchange_status' => 'belum',
            'purchased_at' => Carbon::now()->subHours(2),
            'amount' => 2_750_000,
        ]);

        $result = $this->service->calculatePointsForPurchase($purchase, $prize, $period);

        $this->assertSame(5, $result['points_from_amount']); // 2.750.000 / 500.000 = 5
        $this->assertSame(0, $result['bonus_points']);
        $this->assertSame(250_000, $result['unused_remainder']); // 2.750.000 % 500.000 = 250.000
        $this->assertSame(5, $result['total_points']);
    }

    public function test_kombinasi_poin_nominal_dan_bonus_pembayaran(): void
    {
        $period = RafflePeriod::factory()->create([
            'start_at' => Carbon::now()->subDay(),
            'end_at' => Carbon::now()->addDays(30),
            'exchange_start_at' => Carbon::now()->subDay(),
            'exchange_end_at' => Carbon::now()->addDays(30),
            'status' => 'active',
            'drawing_status' => 'pending',
        ]);

        // Tipe pembayaran
        $paymentType = PaymentType::factory()->kartuMega()->create();

        // Bonus: tambah 2 poin kalau bayar pakai Kartu Mega
        BonusPointRule::factory()->create([
            'raffle_period_id' => $period->id,
            'payment_type_id' => $paymentType->id,
            'bonus_poin' => 2,
            'is_active' => true,
        ]);

        $prize = Prize::factory()->create([
            'raffle_period_id' => $period->id,
            'name' => 'Sepeda',
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

        $result = $this->service->calculatePointsForPurchase($purchase, $prize, $period);

        $this->assertSame(3, $result['points_from_amount']); // 3.500.000 / 1.000.000 = 3 (sisa 500.000 hangus)
        $this->assertSame(2, $result['bonus_points']); // bonus dari Kartu Mega
        $this->assertSame(3_500_000 % 1_000_000, $result['unused_remainder']);
        $this->assertSame(5, $result['total_points']); // 3 + 2 = 5
    }

    public function test_bonus_poin_tidak_berlaku_kalau_tidak_ada_match_rule(): void
    {
        $period = RafflePeriod::factory()->create([
            'start_at' => Carbon::now()->subDay(),
            'end_at' => Carbon::now()->addDays(30),
            'exchange_start_at' => Carbon::now()->subDay(),
            'exchange_end_at' => Carbon::now()->addDays(30),
            'status' => 'active',
            'drawing_status' => 'pending',
        ]);

        $paymentType = PaymentType::factory()->cash()->create();

        // Bonus hanya berlaku untuk KARTU_MEGA, bukan TUNAI
        BonusPointRule::factory()->create([
            'raffle_period_id' => $period->id,
            'payment_type_id' => PaymentType::factory()->kartuMega()->create()->id,
            'bonus_poin' => 5,
            'is_active' => true,
        ]);

        $prize = Prize::factory()->create([
            'raffle_period_id' => $period->id,
            'name' => 'TV',
            'nominal_per_poin' => 1_000_000,
            'active_for_exchange' => true,
            'status' => 'active',
        ]);

        $customer = Customer::factory()->create();
        $purchase = Purchase::factory()->create([
            'raffle_period_id' => $period->id,
            'customer_id' => $customer->id,
            'payment_type_id' => $paymentType->id, // TUNAI, tidak ada bonus
            'exchange_status' => 'belum',
            'purchased_at' => Carbon::now()->subHours(2),
            'amount' => 1_000_000,
        ]);

        $result = $this->service->calculatePointsForPurchase($purchase, $prize, $period);

        $this->assertSame(1, $result['points_from_amount']);
        $this->assertSame(0, $result['bonus_points']);
        $this->assertSame(1, $result['total_points']);
    }

    public function test_pembayaran_dengan_bonus_0_masih_ada_tetapi_0(): void
    {
        $period = RafflePeriod::factory()->create([
            'start_at' => Carbon::now()->subDay(),
            'end_at' => Carbon::now()->addDays(30),
            'exchange_start_at' => Carbon::now()->subDay(),
            'exchange_end_at' => Carbon::now()->addDays(30),
            'status' => 'active',
            'drawing_status' => 'pending',
        ]);

        $paymentType = PaymentType::factory()->debitLain()->create();

        BonusPointRule::factory()->create([
            'raffle_period_id' => $period->id,
            'payment_type_id' => $paymentType->id,
            'bonus_poin' => 0,
            'is_active' => true,
        ]);

        $prize = Prize::factory()->create([
            'raffle_period_id' => $period->id,
            'name' => 'Blender',
            'nominal_per_poin' => 500_000,
            'active_for_exchange' => true,
            'status' => 'active',
        ]);

        $customer = Customer::factory()->create();
        $purchase = Purchase::factory()->create([
            'raffle_period_id' => $period->id,
            'customer_id' => $customer->id,
            'payment_type_id' => $paymentType->id,
            'exchange_status' => 'belum',
            'purchased_at' => Carbon::now()->subHours(1),
            'amount' => 1_200_000,
        ]);

        $result = $this->service->calculatePointsForPurchase($purchase, $prize, $period);

        $this->assertSame(2, $result['points_from_amount']);
        $this->assertSame(0, $result['bonus_points']);
        $this->assertSame(2, $result['total_points']);
        $this->assertSame(200_000, $result['unused_remainder']);
    }

    public function test_poin_nol_kalau_nominal_sama_dengan_nominal_per_poin_tetapi_tidak_genap(): void
    {
        // Ini uji edge case: nominal 1 rupiah dengan rule 1.000.000 per poin
        // FLOOR(1 / 1.000.000) = 0
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
            'name' => 'Uang Tunai',
            'nominal_per_poin' => 1_000_000,
            'active_for_exchange' => true,
            'status' => 'active',
        ]);

        $customer = Customer::factory()->create();
        $purchase = Purchase::factory()->create([
            'raffle_period_id' => $period->id,
            'customer_id' => $customer->id,
            'exchange_status' => 'belum',
            'purchased_at' => Carbon::now()->subHours(1),
            'amount' => 1,
        ]);

        $result = $this->service->calculatePointsForPurchase($purchase, $prize, $period);

        $this->assertSame(0, $result['points_from_amount']);
        $this->assertSame(1, $result['unused_remainder']);
        $this->assertSame(0, $result['total_points']);
    }

    public function test_poin_nol_kalau_nominal_kurang_dari_threshold(): void
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
            'name' => ' 메인賞 ',
            'nominal_per_poin' => 1_000_000,
            'active_for_exchange' => true,
            'status' => 'active',
        ]);

        $customer = Customer::factory()->create();
        $purchase = Purchase::factory()->create([
            'raffle_period_id' => $period->id,
            'customer_id' => $customer->id,
            'exchange_status' => 'belum',
            'purchased_at' => Carbon::now()->subHours(1),
            'amount' => 999_999,
        ]);

        $result = $this->service->calculatePointsForPurchase($purchase, $prize, $period);

        $this->assertSame(0, $result['points_from_amount']);
        $this->assertSame(999_999, $result['unused_remainder']);
        $this->assertSame(0, $result['total_points']);
    }

    public function test_validasi_struk_terpakai_ditolak(): void
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
            'name' => ' Tas ',
            'nominal_per_poin' => 500_000,
            'active_for_exchange' => true,
            'status' => 'active',
        ]);

        $customer = Customer::factory()->create();
        $purchase = Purchase::factory()->create([
            'raffle_period_id' => $period->id,
            'customer_id' => $customer->id,
            'exchange_status' => 'sudah', // sudah ditukar
            'purchased_at' => Carbon::now()->subHours(2),
            'amount' => 1_000_000,
        ]);

        $validation = $this->service->validatePurchaseForRedemption($purchase, $period);

        $this->assertFalse($validation['valid']);
        $this->assertStringContainsString('sudah pernah ditukar', $validation['errors'][0]);
    }
}
