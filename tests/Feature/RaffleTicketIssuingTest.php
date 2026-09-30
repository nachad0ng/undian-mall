<?php

namespace Tests\Feature;

use App\Models\Customer;
use App\Models\Prize;
use App\Models\Purchase;
use App\Models\RafflePeriod;
use App\Models\RaffleTicket;
use App\Models\User;
use App\Services\ManualDrawingService;
use App\Services\PointCalculationService;
use App\Services\PointRedemptionService;
use App\Services\RaffleTicketService;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class RaffleTicketIssuingTest extends TestCase
{
    use RefreshDatabase;

    private PointRedemptionService $redemptionService;

    protected function setUp(): void
    {
        parent::setUp();
        $this->redemptionService = new PointRedemptionService(
            new PointCalculationService,
            new RaffleTicketService
        );
    }

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

    public function test_satu_poin_satu_nomor_dengan_sequence_per_hadiah(): void
    {
        $period = $this->makePeriod();
        $mobil = Prize::factory()->create([
            'raffle_period_id' => $period->id,
            'name' => 'Mobil',
            'nominal_per_poin' => 1_000_000,
            'ticket_digits' => 3,
            'status' => 'active',
            'active_for_exchange' => true,
        ]);
        $motor = Prize::factory()->create([
            'raffle_period_id' => $period->id,
            'name' => 'Motor',
            'nominal_per_poin' => 1_000_000,
            'ticket_digits' => 4,
            'status' => 'active',
            'active_for_exchange' => true,
        ]);
        $customer = Customer::factory()->create();
        $cs = User::factory()->create();

        $purchase1 = Purchase::factory()->create([
            'raffle_period_id' => $period->id,
            'customer_id' => $customer->id,
            'amount' => 2_500_000,
            'purchased_at' => Carbon::now()->subHours(2),
            'exchange_status' => 'belum',
        ]);
        $redemption1 = $this->redemptionService->redeem($customer, $purchase1, $mobil, $cs->id);

        $this->assertSame(2, $redemption1->raffleTickets->count());
        $this->assertSame(['001', '002'], $redemption1->raffleTickets->pluck('ticket_number')->all());

        $purchase2 = Purchase::factory()->create([
            'raffle_period_id' => $period->id,
            'customer_id' => $customer->id,
            'amount' => 1_000_000,
            'purchased_at' => Carbon::now()->subHour(),
            'exchange_status' => 'belum',
        ]);
        $redemption2 = $this->redemptionService->redeem($customer, $purchase2, $motor, $cs->id);

        // Sequence motor terpisah dari mobil, dengan 4 digit
        $this->assertSame(['0001'], $redemption2->raffleTickets->pluck('ticket_number')->all());
        $this->assertSame(2, $mobil->fresh()->ticket_counter);
        $this->assertSame(1, $motor->fresh()->ticket_counter);
    }

    public function test_nominal_kurang_ditolak_tanpa_tiket(): void
    {
        $period = $this->makePeriod();
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
            'amount' => 500_000,
            'purchased_at' => Carbon::now()->subHours(2),
            'exchange_status' => 'belum',
        ]);

        $this->expectException(\RuntimeException::class);
        $this->redemptionService->redeem($customer, $purchase, $prize, $cs->id);
        $this->assertSame(0, RaffleTicket::query()->count());
    }

    public function test_manual_draw_cocok_nomor_jadi_pemenang(): void
    {
        $period = $this->makePeriod();
        $prize = Prize::factory()->create([
            'raffle_period_id' => $period->id,
            'name' => 'Sepeda',
            'nominal_per_poin' => 1_000_000,
            'ticket_digits' => 3,
            'quantity' => 2,
            'status' => 'active',
            'active_for_exchange' => true,
        ]);
        $customer = Customer::factory()->create();
        $cs = User::factory()->create();
        $purchase = Purchase::factory()->create([
            'raffle_period_id' => $period->id,
            'customer_id' => $customer->id,
            'amount' => 2_000_000,
            'purchased_at' => Carbon::now()->subHours(2),
            'exchange_status' => 'belum',
        ]);
        $this->redemptionService->redeem($customer, $purchase, $prize, $cs->id);

        $service = new ManualDrawingService;
        $preview = $service->preview($prize);
        $this->assertTrue($preview['can_draw']);
        $this->assertSame(2, $preview['ticket_count']);

        $drawing = $service->draw($prize, '001', $cs);
        $winner = $drawing->winners->first();
        $this->assertSame('001', $winner->winning_number);
        $this->assertSame($customer->id, $winner->customer_id);

        // Nomor tidak terdaftar ditolak
        try {
            $service->draw($prize, '999', $cs);
            $this->fail('Seharusnya nomor tak terdaftar ditolak.');
        } catch (\RuntimeException $e) {
            $this->assertStringContainsString('tidak terdaftar', $e->getMessage());
        }

        // Nomor yang sama tidak bisa menang dua kali
        try {
            $service->draw($prize, '001', $cs);
            $this->fail('Seharusnya nomor yang sudah menang ditolak.');
        } catch (\RuntimeException $e) {
            $this->assertStringContainsString('sudah menang', $e->getMessage());
        }
    }
}
