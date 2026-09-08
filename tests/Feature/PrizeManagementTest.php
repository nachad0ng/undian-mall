<?php

namespace Tests\Feature;

use App\Models\Coupon;
use App\Models\Customer;
use App\Models\Drawing;
use App\Models\Prize;
use App\Models\Purchase;
use App\Models\RafflePeriod;
use App\Models\Tenant;
use App\Models\User;
use App\Models\Winner;
use Tests\TestCase;

class PrizeManagementTest extends TestCase
{
    protected User $adminUser;
    protected User $csUser;

    protected function setUp(): void
    {
        parent::setUp();

        $this->adminUser = User::where('email', 'admin@example.com')->first();
        $this->csUser = User::where('email', 'customerservice@example.com')->first();
    }

    public function test_user_without_permission_cannot_manage_prizes(): void
    {
        $prize = Prize::factory()->create(['sequence' => 1]);

        $this->actingAs($this->csUser)->get(route('admin.prizes.index'))->assertForbidden();
        $this->actingAs($this->csUser)->post(route('admin.prizes.store'), [])->assertForbidden();
        $this->actingAs($this->csUser)->post(route('admin.prizes.move', $prize), ['direction' => 'up'])->assertForbidden();

        $prize->rafflePeriod->forceDelete();
    }

    public function test_store_prize_requires_period_quantity_and_unique_sequence(): void
    {
        $period = RafflePeriod::factory()->create();
        Prize::factory()->create([
            'raffle_period_id' => $period->id,
            'sequence' => 1,
        ]);

        $this->actingAs($this->adminUser)
            ->from(route('admin.prizes.create'))
            ->post(route('admin.prizes.store'), [
                'raffle_period_id' => '',
                'name' => '',
                'quantity' => 0,
                'sequence' => 1,
                'status' => 'active',
            ])
            ->assertRedirect(route('admin.prizes.create'))
            ->assertSessionHasErrors(['raffle_period_id', 'name', 'quantity']);

        $this->actingAs($this->adminUser)
            ->from(route('admin.prizes.create'))
            ->post(route('admin.prizes.store'), [
                'raffle_period_id' => $period->id,
                'name' => 'Hadiah Duplikat Urutan',
                'quantity' => 1,
                'sequence' => 1,
                'status' => 'active',
            ])
            ->assertRedirect(route('admin.prizes.create'))
            ->assertSessionHasErrors(['sequence']);

        $period->prizes()->delete();
        $period->forceDelete();
    }

    public function test_super_admin_can_create_update_and_reorder_prizes(): void
    {
        $period = RafflePeriod::factory()->active()->create();

        $this->actingAs($this->adminUser)
            ->post(route('admin.prizes.store'), [
                'raffle_period_id' => $period->id,
                'name' => 'Hadiah Utama',
                'description' => 'Mobil',
                'quantity' => 1,
                'sequence' => 1,
                'status' => 'active',
            ])
            ->assertRedirect(route('admin.prizes.index'));

        $this->actingAs($this->adminUser)
            ->post(route('admin.prizes.store'), [
                'raffle_period_id' => $period->id,
                'name' => 'Hadiah Kedua',
                'quantity' => 2,
                'sequence' => 2,
                'status' => 'active',
            ])
            ->assertRedirect(route('admin.prizes.index'));

        $first = Prize::where('name', 'Hadiah Utama')->where('raffle_period_id', $period->id)->first();
        $second = Prize::where('name', 'Hadiah Kedua')->where('raffle_period_id', $period->id)->first();

        $this->actingAs($this->adminUser)
            ->get(route('admin.prizes.show', $first))
            ->assertOk()
            ->assertSee('Hadiah Utama');

        $this->actingAs($this->adminUser)
            ->post(route('admin.prizes.move', $second), ['direction' => 'up'])
            ->assertRedirect();

        $this->assertSame(1, $second->fresh()->sequence);
        $this->assertSame(2, $first->fresh()->sequence);

        $this->actingAs($this->adminUser)
            ->put(route('admin.prizes.update', $first), [
                'raffle_period_id' => $period->id,
                'name' => 'Hadiah Utama Updated',
                'quantity' => 1,
                'sequence' => 2,
                'status' => 'active',
            ])
            ->assertRedirect(route('admin.prizes.index'));

        $this->actingAs($this->adminUser)
            ->delete(route('admin.prizes.destroy', $second))
            ->assertRedirect(route('admin.prizes.index'));

        $this->assertNull(Prize::find($second->id));
        $first->delete();
        $period->forceDelete();
    }

    public function test_prize_used_in_drawing_cannot_be_deleted_or_recklessly_updated(): void
    {
        $period = RafflePeriod::factory()->active()->create();
        $otherPeriod = RafflePeriod::factory()->active()->create();
        $prize = Prize::factory()->create([
            'raffle_period_id' => $period->id,
            'name' => 'Hadiah Terkunci',
            'quantity' => 2,
            'sequence' => 1,
        ]);
        $neighbor = Prize::factory()->create([
            'raffle_period_id' => $period->id,
            'name' => 'Hadiah Tetangga',
            'quantity' => 1,
            'sequence' => 2,
        ]);

        $this->markPrizeAsDrawn($prize);

        $this->actingAs($this->adminUser)
            ->delete(route('admin.prizes.destroy', $prize))
            ->assertRedirect()
            ->assertSessionHas('error');

        $this->actingAs($this->adminUser)
            ->from(route('admin.prizes.edit', $prize))
            ->put(route('admin.prizes.update', $prize), [
                'raffle_period_id' => $otherPeriod->id,
                'name' => 'Hadiah Terkunci',
                'quantity' => 1,
                'sequence' => 3,
                'status' => 'active',
            ])
            ->assertRedirect(route('admin.prizes.edit', $prize))
            ->assertSessionHasErrors(['raffle_period_id', 'quantity', 'sequence']);

        $this->actingAs($this->adminUser)
            ->post(route('admin.prizes.move', $prize), ['direction' => 'down'])
            ->assertRedirect()
            ->assertSessionHas('error');

        $this->assertSame(1, $prize->fresh()->sequence);
        $this->assertSame($period->id, $prize->fresh()->raffle_period_id);

        $prize->winners()->delete();
        $prize->drawings()->delete();
        Coupon::where('raffle_period_id', $period->id)->delete();
        Purchase::where('raffle_period_id', $period->id)->forceDelete();
        $neighbor->delete();
        $prize->delete();
        $period->forceDelete();
        $otherPeriod->forceDelete();
    }

    private function markPrizeAsDrawn(Prize $prize): void
    {
        $customer = Customer::factory()->create();
        $tenant = Tenant::factory()->create();
        $purchase = Purchase::factory()->create([
            'raffle_period_id' => $prize->raffle_period_id,
            'customer_id' => $customer->id,
            'tenant_id' => $tenant->id,
            'entered_by' => $this->adminUser->id,
        ]);
        $coupon = Coupon::factory()->create([
            'raffle_period_id' => $prize->raffle_period_id,
            'purchase_id' => $purchase->id,
            'customer_id' => $customer->id,
        ]);
        $drawing = Drawing::create([
            'raffle_period_id' => $prize->raffle_period_id,
            'prize_id' => $prize->id,
            'executed_by' => $this->adminUser->id,
            'executed_at' => now(),
            'status' => 'completed',
        ]);
        Winner::create([
            'drawing_id' => $drawing->id,
            'raffle_period_id' => $prize->raffle_period_id,
            'prize_id' => $prize->id,
            'coupon_id' => $coupon->id,
            'customer_id' => $customer->id,
            'won_at' => now(),
            'is_published' => false,
        ]);
    }
}
