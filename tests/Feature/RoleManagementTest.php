<?php

namespace Tests\Feature;

use App\Models\User;
use Spatie\Permission\Models\Role;
use Spatie\Permission\Models\Permission;
use Tests\TestCase;

class RoleManagementTest extends TestCase
{
    protected $adminUser;
    protected $managerUser;

    protected function setUp(): void
    {
        parent::setUp();

        $this->adminUser = User::where('email', 'admin@example.com')->first();
        $this->managerUser = User::where('email', 'manager@example.com')->first();
    }

    public function test_unauthenticated_user_cannot_access_roles_index(): void
    {
        $response = $this->get(route('admin.roles.index'));
        $response->assertRedirect(route('login'));
    }

    public function test_user_without_permission_cannot_access_roles_index(): void
    {
        $response = $this->actingAs($this->managerUser)->get(route('admin.roles.index'));
        $response->assertStatus(403);
    }

    public function test_user_without_permission_cannot_access_roles_create(): void
    {
        $response = $this->actingAs($this->managerUser)->get(route('admin.roles.create'));
        $response->assertStatus(403);
    }

    public function test_user_without_permission_cannot_store_role(): void
    {
        $response = $this->actingAs($this->managerUser)->post(route('admin.roles.store'), [
            'name' => 'Unauthorized Role',
        ]);
        $response->assertStatus(403);
    }

    public function test_user_without_permission_cannot_access_roles_edit(): void
    {
        $role = Role::where('name', 'Super Admin')->first();
        $response = $this->actingAs($this->managerUser)->get(route('admin.roles.edit', $role));
        $response->assertStatus(403);
    }

    public function test_user_without_permission_cannot_update_role(): void
    {
        $role = Role::where('name', 'Super Admin')->first();
        $response = $this->actingAs($this->managerUser)->put(route('admin.roles.update', $role), [
            'name' => 'Hacked Role',
        ]);
        $response->assertStatus(403);
    }

    public function test_user_without_permission_cannot_delete_role(): void
    {
        $role = Role::create(['name' => 'Temp Role To Delete', 'guard_name' => 'web']);
        $response = $this->actingAs($this->managerUser)->delete(route('admin.roles.destroy', $role));
        
        $this->assertDatabaseHas('roles', ['name' => 'Temp Role To Delete']);
        $role->delete();

        $response->assertStatus(403);
    }

    public function test_super_admin_can_view_roles_index(): void
    {
        $response = $this->actingAs($this->adminUser)->get(route('admin.roles.index'));
        $response->assertStatus(200);
        $response->assertViewIs('admin.roles.index');
    }

    public function test_super_admin_can_fetch_roles_datatables_json(): void
    {
        $response = $this->actingAs($this->adminUser)->getJson(route('admin.roles.index'));
        $response->assertStatus(200);
        $response->assertJsonStructure([
            'draw',
            'recordsTotal',
            'recordsFiltered',
            'data' => [
                '*' => [
                    'id',
                    'name',
                    'permissions_count',
                    'created_at',
                    'actions' => [
                        'edit_url',
                        'delete_url',
                    ],
                ]
            ]
        ]);
    }

    public function test_super_admin_can_view_create_role_page(): void
    {
        $response = $this->actingAs($this->adminUser)->get(route('admin.roles.create'));
        $response->assertStatus(200);
        $response->assertViewIs('admin.roles.create');
        $response->assertViewHas('permissions');
    }

    public function test_super_admin_can_store_new_role_with_permissions(): void
    {
        $permissions = Permission::take(2)->pluck('id')->toArray();
        $roleName = 'Test Marketing Staff';

        $response = $this->actingAs($this->adminUser)->post(route('admin.roles.store'), [
            'name' => $roleName,
            'permissions' => $permissions,
        ]);

        $response->assertRedirect(route('admin.roles.index'));
        $response->assertSessionHas('success');

        $role = Role::where('name', $roleName)->first();
        $this->assertNotNull($role);
        $this->assertCount(2, $role->permissions);

        // Cleanup
        $role->delete();
    }

    public function test_store_role_validation_fails_on_duplicate_and_empty(): void
    {
        // Empty name
        $response = $this->actingAs($this->adminUser)->post(route('admin.roles.store'), [
            'name' => '',
        ]);
        $response->assertSessionHasErrors(['name']);

        // Duplicate name
        $response = $this->actingAs($this->adminUser)->post(route('admin.roles.store'), [
            'name' => 'Super Admin',
        ]);
        $response->assertSessionHasErrors(['name']);
    }

    public function test_super_admin_can_view_edit_role_page(): void
    {
        $role = Role::where('name', 'Super Admin')->first();
        $response = $this->actingAs($this->adminUser)->get(route('admin.roles.edit', $role));
        $response->assertStatus(200);
        $response->assertViewIs('admin.roles.edit');
        $response->assertViewHas('role');
        $response->assertViewHas('permissions');
        $response->assertViewHas('rolePermissions');
    }

    public function test_super_admin_can_update_role(): void
    {
        $role = Role::create(['name' => 'Role For Update Test', 'guard_name' => 'web']);
        $permission = Permission::first();

        // Test with string ID as sent by HTML form inputs
        $response = $this->actingAs($this->adminUser)->put(route('admin.roles.update', $role), [
            'name' => 'Role Updated Name',
            'permissions' => [(string) $permission->id],
        ]);

        $response->assertRedirect(route('admin.roles.index'));
        $response->assertSessionHas('success');

        $role->refresh();
        $this->assertEquals('Role Updated Name', $role->name);
        $this->assertTrue($role->hasPermissionTo($permission->name));

        // Cleanup
        $role->delete();
    }

    public function test_super_admin_can_remove_permission_from_role(): void
    {
        $permissions = Permission::take(2)->get();
        $role = Role::create(['name' => 'Role For Removing Permission', 'guard_name' => 'web']);
        $role->syncPermissions($permissions);
        $this->assertCount(2, $role->permissions);

        // Remove 1 permission, keeping only the first one (sent as string ID)
        $response = $this->actingAs($this->adminUser)->put(route('admin.roles.update', $role), [
            'name' => 'Role For Removing Permission',
            'permissions' => [(string) $permissions[0]->id],
        ]);

        $response->assertRedirect(route('admin.roles.index'));
        $response->assertSessionHas('success');

        $role->refresh();
        $this->assertCount(1, $role->permissions);
        $this->assertTrue($role->hasPermissionTo($permissions[0]->name));
        $this->assertFalse($role->hasPermissionTo($permissions[1]->name));

        // Cleanup
        $role->delete();
    }

    public function test_super_admin_can_remove_all_permissions_from_role(): void
    {
        $permissions = Permission::take(2)->get();
        $role = Role::create(['name' => 'Role For Removing All Permissions', 'guard_name' => 'web']);
        $role->syncPermissions($permissions);
        $this->assertCount(2, $role->permissions);

        // Submit form with no permissions checked (permissions key omitted or empty)
        $response = $this->actingAs($this->adminUser)->put(route('admin.roles.update', $role), [
            'name' => 'Role For Removing All Permissions',
        ]);

        $response->assertRedirect(route('admin.roles.index'));
        $response->assertSessionHas('success');

        $role->refresh();
        $this->assertCount(0, $role->permissions);

        // Cleanup
        $role->delete();
    }

    public function test_cannot_delete_system_roles(): void
    {
        $systemRoles = ['Super Admin', 'Manager', 'Customer Service', 'Auditor'];

        foreach ($systemRoles as $name) {
            $role = Role::where('name', $name)->first();
            $response = $this->actingAs($this->adminUser)->deleteJson(route('admin.roles.destroy', $role));
            $response->assertStatus(422);
            $response->assertJson([
                'success' => false,
            ]);
            $this->assertDatabaseHas('roles', ['name' => $name]);
        }
    }

    public function test_can_delete_custom_role(): void
    {
        $role = Role::create(['name' => 'Custom Role Deletable', 'guard_name' => 'web']);

        $response = $this->actingAs($this->adminUser)->deleteJson(route('admin.roles.destroy', $role));
        $response->assertStatus(200);
        $response->assertJson([
            'success' => true,
        ]);

        $this->assertDatabaseMissing('roles', ['name' => 'Custom Role Deletable']);
    }
}
