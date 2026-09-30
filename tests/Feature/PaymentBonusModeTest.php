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
use App\Services\RaffleTicketService;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class PaymentBonusModeTest extends TestCase
{
    use RefreshDatabase;

    protected $seed = false;

    private function makePeriod(): RafflePeriod
    {
        return RafflePeriod::factory()->create([
            'start_at' => Carbon::now()->subDay(),
            'end_at' => Carbon::now()->addDays(30),
            'exchange_start_at' => Carbon::now()->subDay(),
            'exchange_end_at' => Carbon::now()->addDays(30),
            'status' => 'active',
            'drawing_status' => 'pending',
        ]);
    }

    public function test_mode_kali_menggandakan_poin_nominal(): void
    {
        $period = $this->makePeriod();
        $paymentType = PaymentType::factory()->kartuMega()->create();
        BonusPointRule::factory()->multiply(2.0)->create([
            'raffle_period_id' => $period->id,
            'payment_type_id' => $paymentType->id,
        ]);
        $prize = Prize::factory()->create([
            'raffle_period_id' => $period->id,
            'nominal_per_poin' => 1_000_000,
            'status' => 'active',
            'active_for_exchange' => true,
        ]);
        $purchase = Purchase::factory()->create([
            'raffle_period_id' => $period->id,
            'customer_id' => Customer::factory()->create()->id,
            'payment_type_id' => $paymentType->id,
            'exchange_status' => 'belum',
            'purchased_at' => Carbon::now()->subHours(2),
            'amount' => 3_500_000,
        ]);

        $result = (new PointCalculationService)->calculatePointsForPurchase($purchase, $prize, $period);

        $this->assertSame(3, $result['points_from_amount']);
        $this->assertSame('multiply', $result['bonus_mode']);
        $this->assertSame(3, $result['bonus_points']);
        $this->assertSame(6, $result['total_points']);
    }

    public function test_mode_tambah_tetap_seperti_semula(): void
    {
        $period = $this->makePeriod();
        $paymentType = PaymentType::factory()->kartuMega()->create();
        BonusPointRule::factory()->create([
            'raffle_period_id' => $period->id,
            'payment_type_id' => $paymentType->id,
            'mode' => 'add',
            'bonus_poin' => 2,
            'multiplier' => null,
            'is_active' => true,
        ]);
        $prize = Prize::factory()->create([
            'raffle_period_id' => $period->id,
            'nominal_per_poin' => 1_000_000,
            'status' => 'active',
            'active_for_exchange' => true,
        ]);
        $purchase = Purchase::factory()->create([
            'raffle_period_id' => $period->id,
            'customer_id' => Customer::factory()->create()->id,
            'payment_type_id' => $paymentType->id,
            'exchange_status' => 'belum',
            'purchased_at' => Carbon::now()->subHours(2),
            'amount' => 3_500_000,
        ]);

        $result = (new PointCalculationService)->calculatePointsForPurchase($purchase, $prize, $period);

        $this->assertSame('add', $result['bonus_mode']);
        $this->assertSame(2, $result['bonus_points']);
        $this->assertSame(5, $result['total_points']);
    }

    public function test_penukaran_dengan_mode_kali_menerbitkan_tiket_sesuai_total(): void
    {
        $period = $this->makePeriod();
        $paymentType = PaymentType::factory()->kartuMega()->create();
        BonusPointRule::factory()->multiply(2.0)->create([
            'raffle_period_id' => $period->id,
            'payment_type_id' => $paymentType->id,
        ]);
        $prize = Prize::factory()->create([
            'raffle_period_id' => $period->id,
            'nominal_per_poin' => 1_000_000,
            'ticket_digits' => 3,
            'status' => 'active',
            'active_for_exchange' => true,
        ]);
        $customer = Customer::factory()->create();
        $cs = User::factory()->create();
        $purchase = Purchase::factory()->create([
            'raffle_period_id' => $period->id,
            'customer_id' => $customer->id,
            'payment_type_id' => $paymentType->id,
            'exchange_status' => 'belum',
            'purchased_at' => Carbon::now()->subHours(2),
            'amount' => 2_000_000,
        ]);

        $service = new PointRedemptionService(new PointCalculationService, new RaffleTicketService);
        $redemption = $service->redeem($customer, $purchase, $prize, $cs->id);

        // 2 poin nominal x2 = 4 poin = 4 nomor undian
        $this->assertSame(4, $redemption->total_poin_didapat);
        $this->assertSame(4, $redemption->raffleTickets->count());
        $this->assertSame('multiply', $redemption->bonus_mode_snapshot);
        $this->assertSame(2, $redemption->poin_dari_nominal);
        $this->assertSame(2, $redemption->poin_bonus_pembayaran);
    }

    public function test_preview_menampilkan_bonus_sebelum_proses(): void
    {
        $this->seed();
        $admin = User::where('email', 'admin@example.com')->firstOrFail();
        $period = RafflePeriod::where('code', 'MALL-2026')->firstOrFail();
        $prize = Prize::where('raffle_period_id', $period->id)->orderBy('sequence')->firstOrFail();
        $paymentType = PaymentType::where('code', 'KARTU_MEGA')->firstOrFail();

        BonusPointRule::updateOrCreate(
            ['raffle_period_id' => $period->id, 'payment_type_id' => $paymentType->id],
            ['mode' => 'multiply', 'bonus_poin' => 0, 'multiplier' => 2, 'is_active' => true]
        );

        $amount = $prize->nominal_per_poin * 3;
        $response = $this->actingAs($admin)->postJson(route('admin.point-exchange.preview'), [
            'period_id' => $period->id,
            'prize_id' => $prize->id,
            'amount' => $amount,
            'payment_type_id' => $paymentType->id,
        ]);

        $response->assertOk()
            ->assertJsonPath('success', true)
            ->assertJsonPath('calculation.points_from_amount', 3)
            ->assertJsonPath('calculation.bonus_mode', 'multiply')
            ->assertJsonPath('calculation.total_points', 6)
            ->assertJsonPath('ticket_count', 6)
            ->assertJsonPath('bonus.mode', 'multiply');
    }
}
