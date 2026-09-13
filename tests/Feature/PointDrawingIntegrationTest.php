<?php

namespace Tests\Feature;

use App\Models\Customer;
use App\Models\CustomerPointBalance;
use App\Models\Drawing;
use App\Models\Prize;
use App\Models\RafflePeriod;
use App\Models\User;
use App\Models\Winner;
use App\Services\PointDrawingService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class PointDrawingIntegrationTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed();
    }

    public function test_drawing_memilih_customer_dengan_bobot_poin_dan_membuat_winner(): void
    {
        $period = RafflePeriod::factory()->active()->create();
        $prize = Prize::factory()->create([
            'raffle_period_id' => $period->id,
            'quantity' => 2,
        ]);
        $customerA = Customer::factory()->create();
        $customerB = Customer::factory()->create();
        $customerC = Customer::factory()->create();

        CustomerPointBalance::create([
            'customer_id' => $customerA->id,
            'raffle_period_id' => $period->id,
            'prize_id' => $prize->id,
            'total_poin' => 5,
        ]);
        CustomerPointBalance::create([
            'customer_id' => $customerB->id,
            'raffle_period_id' => $period->id,
            'prize_id' => $prize->id,
            'total_poin' => 3,
        ]);
        CustomerPointBalance::create([
            'customer_id' => $customerC->id,
            'raffle_period_id' => $period->id,
            'prize_id' => $prize->id,
            'total_poin' => 2,
        ]);

        $drawing = (new PointDrawingService)->draw($prize, User::where('email', 'admin@example.com')->firstOrFail());

        $this->assertSame('completed', $drawing->status);
        $this->assertSame(10, $drawing->metadata['pool_count']);
        $this->assertSame(3, $drawing->metadata['eligible_customers']);
        $this->assertCount(2, $drawing->winners);
        $this->assertCount(2, $drawing->winners->pluck('customer_id')->unique());
        $this->assertDatabaseHas('audit_logs', [
            'action' => 'drawing.completed',
            'auditable_type' => Drawing::class,
            'auditable_id' => $drawing->id,
        ]);
        foreach ($drawing->winners as $winner) {
            $this->assertContains($winner->customer_id, [$customerA->id, $customerB->id, $customerC->id]);
        }
    }

    public function test_drawing_kedua_untuk_hadiah_yang_sama_ditolak(): void
    {
        $period = RafflePeriod::factory()->active()->create();
        $prize = Prize::factory()->create(['raffle_period_id' => $period->id]);
        $customer = Customer::factory()->create();
        CustomerPointBalance::create([
            'customer_id' => $customer->id,
            'raffle_period_id' => $period->id,
            'prize_id' => $prize->id,
            'total_poin' => 1,
        ]);

        $service = new PointDrawingService;
        $executor = User::where('email', 'admin@example.com')->firstOrFail();
        $service->draw($prize, $executor);

        $this->expectException(\RuntimeException::class);
        $this->expectExceptionMessage('Hadiah ini sudah pernah diundi.');
        $service->draw($prize, $executor);
    }

    public function test_drawing_ditolak_untuk_periode_tidak_aktif(): void
    {
        $period = RafflePeriod::factory()->inactive()->create();
        $prize = Prize::factory()->create(['raffle_period_id' => $period->id]);
        $customer = Customer::factory()->create();
        CustomerPointBalance::create([
            'customer_id' => $customer->id,
            'raffle_period_id' => $period->id,
            'prize_id' => $prize->id,
            'total_poin' => 1,
        ]);

        $this->expectException(\RuntimeException::class);
        $this->expectExceptionMessage('Periode tidak aktif untuk proses pengundian.');
        (new PointDrawingService)->draw($prize, User::where('email', 'admin@example.com')->firstOrFail());
    }

    public function test_preview_pool_mengembalikan_kesiapan_drawing(): void
    {
        $period = RafflePeriod::factory()->active()->create();
        $prize = Prize::factory()->create([
            'raffle_period_id' => $period->id,
            'quantity' => 2,
        ]);
        $customers = Customer::factory()->count(2)->create();

        foreach ($customers as $customer) {
            CustomerPointBalance::create([
                'customer_id' => $customer->id,
                'raffle_period_id' => $period->id,
                'prize_id' => $prize->id,
                'total_poin' => 3,
            ]);
        }

        $preview = (new PointDrawingService)->preview($prize);

        $this->assertTrue($preview['can_draw']);
        $this->assertSame(6, $preview['pool_count']);
        $this->assertSame(2, $preview['eligible_customers']);
        $this->assertNull($preview['reason']);
    }

    public function test_admin_dapat_publish_dan_unpublish_pemenang(): void
    {
        $period = RafflePeriod::factory()->active()->create();
        $prize = Prize::factory()->create(['raffle_period_id' => $period->id]);
        $drawing = Drawing::factory()->create([
            'raffle_period_id' => $period->id,
            'prize_id' => $prize->id,
            'executed_by' => User::where('email', 'admin@example.com')->firstOrFail()->id,
        ]);
        $winner = Winner::create([
            'drawing_id' => $drawing->id,
            'raffle_period_id' => $period->id,
            'prize_id' => $prize->id,
            'customer_id' => Customer::factory()->create()->id,
            'won_at' => now(),
        ]);
        $admin = User::where('email', 'admin@example.com')->firstOrFail();

        $this->actingAs($admin)
            ->postJson(route('admin.winners.publish', $winner))
            ->assertOk()
            ->assertJson(['success' => true]);
        $this->assertTrue($winner->fresh()->is_published);
        $this->assertNotNull($winner->fresh()->published_at);

        $this->actingAs($admin)
            ->postJson(route('admin.winners.unpublish', $winner))
            ->assertOk()
            ->assertJson(['success' => true]);
        $this->assertFalse($winner->fresh()->is_published);
        $this->assertNull($winner->fresh()->published_at);
        $this->assertDatabaseHas('audit_logs', [
            'action' => 'winner.published',
            'auditable_id' => $winner->id,
        ]);
        $this->assertDatabaseHas('audit_logs', [
            'action' => 'winner.unpublished',
            'auditable_id' => $winner->id,
        ]);
    }

    public function test_halaman_publik_hanya_menampilkan_pemenang_yang_dipublikasikan(): void
    {
        $period = RafflePeriod::factory()->active()->create();
        $prize = Prize::factory()->create(['raffle_period_id' => $period->id]);
        $publishedCustomer = Customer::factory()->create(['name' => 'Pemenang Resmi']);
        $draftCustomer = Customer::factory()->create(['name' => 'Pemenang Draft']);
        $drawing = Drawing::factory()->create([
            'raffle_period_id' => $period->id,
            'prize_id' => $prize->id,
            'executed_by' => User::where('email', 'admin@example.com')->firstOrFail()->id,
        ]);

        Winner::create([
            'drawing_id' => $drawing->id,
            'raffle_period_id' => $period->id,
            'prize_id' => $prize->id,
            'customer_id' => $publishedCustomer->id,
            'won_at' => now(),
            'is_published' => true,
            'published_at' => now(),
        ]);
        Winner::create([
            'drawing_id' => $drawing->id,
            'raffle_period_id' => $period->id,
            'prize_id' => $prize->id,
            'customer_id' => $draftCustomer->id,
            'won_at' => now(),
            'is_published' => false,
        ]);

        $this->get(route('winners.index'))
            ->assertOk()
            ->assertSee('Pemenang Resmi')
            ->assertDontSee('Pemenang Draft');
    }
}
