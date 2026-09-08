<?php

namespace Tests\Feature;

use App\Models\Purchase;
use App\Models\Tenant;
use App\Models\User;
use Tests\TestCase;

class TenantManagementTest extends TestCase
{
    protected User $adminUser;
    protected User $csUser;

    protected function setUp(): void
    {
        parent::setUp();

        $this->adminUser = User::where('email', 'admin@example.com')->first();
        $this->csUser = User::where('email', 'customerservice@example.com')->first();
    }

    public function test_user_without_permission_cannot_manage_tenants(): void
    {
        $tenant = Tenant::factory()->create();

        $this->actingAs($this->csUser)->get(route('admin.tenants.index'))->assertForbidden();
        $this->actingAs($this->csUser)->post(route('admin.tenants.store'), [])->assertForbidden();
        $this->actingAs($this->csUser)->post(route('admin.tenants.toggle-status', $tenant))->assertForbidden();

        $tenant->forceDelete();
    }

    public function test_store_tenant_requires_code_name_and_status(): void
    {
        $this->actingAs($this->adminUser)
            ->from(route('admin.tenants.create'))
            ->post(route('admin.tenants.store'), [
                'code' => '',
                'name' => '',
                'status' => 'unknown',
            ])
            ->assertRedirect(route('admin.tenants.create'))
            ->assertSessionHasErrors(['code', 'name', 'status']);
    }

    public function test_super_admin_can_crud_and_toggle_tenant(): void
    {
        $code = 'TNT-'.uniqid();

        $this->actingAs($this->adminUser)
            ->post(route('admin.tenants.store'), [
                'code' => $code,
                'name' => 'Tenant Checkpoint',
                'unit_number' => 'A-01',
                'phone' => '08123456789',
                'status' => 'active',
            ])
            ->assertRedirect(route('admin.tenants.index'));

        $tenant = Tenant::where('code', $code)->first();
        $this->assertTrue($tenant->isActive());

        $this->actingAs($this->adminUser)
            ->get(route('admin.tenants.show', $tenant))
            ->assertOk()
            ->assertSee('Tenant Checkpoint');

        $this->actingAs($this->adminUser)
            ->put(route('admin.tenants.update', $tenant), [
                'code' => $code,
                'name' => 'Tenant Checkpoint Updated',
                'unit_number' => 'A-02',
                'phone' => '08123456789',
                'status' => 'active',
            ])
            ->assertRedirect(route('admin.tenants.index'));

        $this->actingAs($this->adminUser)
            ->post(route('admin.tenants.toggle-status', $tenant))
            ->assertRedirect();

        $this->assertSame('inactive', $tenant->fresh()->status);
        $this->assertFalse($tenant->fresh()->isActive());

        $this->actingAs($this->adminUser)
            ->delete(route('admin.tenants.destroy', $tenant))
            ->assertRedirect(route('admin.tenants.index'));

        $this->assertSoftDeleted('tenants', ['id' => $tenant->id]);
        $tenant->forceDelete();
    }

    public function test_tenant_with_purchases_cannot_be_deleted(): void
    {
        $tenant = Tenant::factory()->create();
        Purchase::factory()->create([
            'tenant_id' => $tenant->id,
            'entered_by' => $this->adminUser->id,
        ]);

        $this->actingAs($this->adminUser)
            ->delete(route('admin.tenants.destroy', $tenant))
            ->assertRedirect()
            ->assertSessionHas('error');

        $this->assertNotNull($tenant->fresh());
        $tenant->purchases()->forceDelete();
        $tenant->forceDelete();
    }
}
