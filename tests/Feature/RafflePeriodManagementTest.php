<?php

namespace Tests\Feature;

use App\Models\Purchase;
use App\Models\RafflePeriod;
use App\Models\User;
use Tests\TestCase;

class RafflePeriodManagementTest extends TestCase
{
    protected User $adminUser;
    protected User $csUser;

    protected function setUp(): void
    {
        parent::setUp();

        $this->adminUser = User::where('email', 'admin@example.com')->first();
        $this->csUser = User::where('email', 'customerservice@example.com')->first();
    }

    public function test_user_without_permission_cannot_access_period_pages(): void
    {
        $period = RafflePeriod::factory()->create();

        $this->actingAs($this->csUser)->get(route('admin.raffle-periods.index'))->assertForbidden();
        $this->actingAs($this->csUser)->get(route('admin.raffle-periods.create'))->assertForbidden();
        $this->actingAs($this->csUser)->get(route('admin.raffle-periods.show', $period))->assertForbidden();
        $this->actingAs($this->csUser)->post(route('admin.raffle-periods.store'), [])->assertForbidden();
        $this->actingAs($this->csUser)->post(route('admin.raffle-periods.toggle-status', $period))->assertForbidden();

        $period->forceDelete();
    }

    public function test_super_admin_can_view_period_index_and_create_form(): void
    {
        $this->actingAs($this->adminUser)->get(route('admin.raffle-periods.index'))
            ->assertOk()
            ->assertViewIs('admin.raffle-periods.index')
            ->assertSee('Master Data');

        $this->actingAs($this->adminUser)->get(route('admin.raffle-periods.create'))
            ->assertOk()
            ->assertViewIs('admin.raffle-periods.create');
    }

    public function test_store_period_requires_validation_and_coupon_rule(): void
    {
        $this->actingAs($this->adminUser)
            ->from(route('admin.raffle-periods.create'))
            ->post(route('admin.raffle-periods.store'), [
                'code' => '',
                'name' => '',
                'start_at' => now()->addDay()->toDateTimeString(),
                'end_at' => now()->toDateTimeString(),
                'purchase_threshold' => 0,
                'coupon_unit' => 0,
                'status' => 'active',
            ])
            ->assertRedirect(route('admin.raffle-periods.create'))
            ->assertSessionHasErrors(['code', 'name', 'end_at', 'purchase_threshold', 'coupon_unit']);
    }

    public function test_super_admin_can_create_show_and_update_period(): void
    {
        $payload = [
            'code' => 'CP03-'.uniqid(),
            'name' => 'Periode Checkpoint 03',
            'description' => 'Tes CRUD periode',
            'start_at' => now()->subDay()->format('Y-m-d H:i:s'),
            'end_at' => now()->addDays(7)->format('Y-m-d H:i:s'),
            'purchase_threshold' => 150000,
            'coupon_unit' => 100000,
            'max_coupon_per_transaction' => 5,
            'status' => 'draft',
        ];

        $this->actingAs($this->adminUser)
            ->post(route('admin.raffle-periods.store'), $payload)
            ->assertRedirect(route('admin.raffle-periods.index'));

        $period = RafflePeriod::where('code', $payload['code'])->first();
        $this->assertNotNull($period);
        $this->assertSame('pending', $period->drawing_status);

        $this->actingAs($this->adminUser)
            ->get(route('admin.raffle-periods.show', $period))
            ->assertOk()
            ->assertSee('Periode Checkpoint 03');

        $this->actingAs($this->adminUser)
            ->put(route('admin.raffle-periods.update', $period), [
                ...$payload,
                'name' => 'Periode Checkpoint 03 Updated',
                'status' => 'inactive',
            ])
            ->assertRedirect(route('admin.raffle-periods.index'));

        $this->assertSame('Periode Checkpoint 03 Updated', $period->fresh()->name);
        $period->forceDelete();
    }

    public function test_toggle_status_follows_active_inactive_and_closed_rules(): void
    {
        $draft = RafflePeriod::factory()->withoutCouponRule()->create();
        $this->actingAs($this->adminUser)
            ->post(route('admin.raffle-periods.toggle-status', $draft))
            ->assertRedirect()
            ->assertSessionHas('error');
        $this->assertSame('draft', $draft->fresh()->status);

        $ready = RafflePeriod::factory()->create(['status' => 'draft']);
        $this->actingAs($this->adminUser)
            ->post(route('admin.raffle-periods.toggle-status', $ready))
            ->assertRedirect()
            ->assertSessionHas('success');
        $this->assertSame('active', $ready->fresh()->status);

        $this->actingAs($this->adminUser)
            ->post(route('admin.raffle-periods.toggle-status', $ready))
            ->assertRedirect();
        $this->assertSame('inactive', $ready->fresh()->status);

        $closed = RafflePeriod::factory()->closed()->create();
        $this->actingAs($this->adminUser)
            ->post(route('admin.raffle-periods.toggle-status', $closed))
            ->assertRedirect()
            ->assertSessionHas('error');
        $this->assertSame('closed', $closed->fresh()->status);

        $draft->forceDelete();
        $ready->forceDelete();
        $closed->forceDelete();
    }

    public function test_period_with_purchases_cannot_be_deleted(): void
    {
        $period = RafflePeriod::factory()->active()->create();
        Purchase::factory()->create([
            'raffle_period_id' => $period->id,
            'entered_by' => $this->adminUser->id,
        ]);

        $this->actingAs($this->adminUser)
            ->from(route('admin.raffle-periods.index'))
            ->delete(route('admin.raffle-periods.destroy', $period))
            ->assertRedirect()
            ->assertSessionHas('error');

        $this->assertNotNull($period->fresh());

        $period->purchases()->forceDelete();
        $period->forceDelete();
    }
}
