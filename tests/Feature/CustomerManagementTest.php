<?php

namespace Tests\Feature;

use App\Models\Customer;
use App\Models\Purchase;
use App\Models\User;
use Tests\TestCase;

class CustomerManagementTest extends TestCase
{
    protected User $adminUser;

    protected User $auditorUser;

    protected function setUp(): void
    {
        parent::setUp();

        $this->adminUser = User::where('email', 'admin@example.com')->first();
        $this->auditorUser = User::where('email', 'auditor@example.com')->first();
    }

    public function test_user_without_permission_cannot_manage_customers(): void
    {
        $customer = Customer::factory()->create();

        $this->actingAs($this->auditorUser)->get(route('admin.customers.index'))->assertForbidden();
        $this->actingAs($this->auditorUser)->post(route('admin.customers.store'), [])->assertForbidden();
        $this->actingAs($this->auditorUser)->delete(route('admin.customers.destroy', $customer))->assertForbidden();
    }

    public function test_store_customer_validates_required_fields(): void
    {
        $this->actingAs($this->adminUser)
            ->from(route('admin.customers.create'))
            ->post(route('admin.customers.store'), ['name' => '', 'phone' => '', 'email' => 'not-an-email'])
            ->assertRedirect(route('admin.customers.create'))
            ->assertSessionHasErrors(['name', 'phone', 'email']);
    }

    public function test_admin_can_crud_customer(): void
    {
        $this->actingAs($this->adminUser)
            ->post(route('admin.customers.store'), [
                'name' => 'Customer Checkpoint',
                'phone' => '08123456789',
                'identity_number' => '3170000000000001',
                'email' => 'checkpoint@example.com',
                'address' => 'Alamat Checkpoint',
            ])
            ->assertRedirect(route('admin.customers.index'));

        $customer = Customer::where('phone', '08123456789')->firstOrFail();

        $this->actingAs($this->adminUser)
            ->get(route('admin.customers.show', $customer))
            ->assertOk()
            ->assertSee('Customer Checkpoint');

        $this->actingAs($this->adminUser)
            ->put(route('admin.customers.update', $customer), [
                'name' => 'Customer Updated',
                'phone' => '08123456789',
                'email' => 'updated@example.com',
            ])
            ->assertRedirect(route('admin.customers.index'));

        $this->assertSame('Customer Updated', $customer->fresh()->name);

        $this->actingAs($this->adminUser)
            ->delete(route('admin.customers.destroy', $customer))
            ->assertRedirect(route('admin.customers.index'));

        $this->assertSoftDeleted('customers', ['id' => $customer->id]);
    }

    public function test_customer_with_purchases_cannot_be_deleted(): void
    {
        $customer = Customer::factory()->create();
        Purchase::factory()->create(['customer_id' => $customer->id, 'entered_by' => $this->adminUser->id]);

        $this->actingAs($this->adminUser)
            ->delete(route('admin.customers.destroy', $customer))
            ->assertRedirect()
            ->assertSessionHas('error');

        $this->assertNotNull($customer->fresh());
    }
}
