<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Role;
use Spatie\Permission\Models\Permission;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        // Create permissions
        $permissions = [
            'view-dashboard',
            'manage-users',
            'manage-roles',
            'manage-permissions',
            'manage-tenants',
            'manage-customers',
            'manage-periods',
            'manage-prizes',
            'manage-coupons',
            'manage-transactions',
            'manage-draws',
            'view-reports',
            'view-audit-logs',
        ];

        foreach ($permissions as $permission) {
            Permission::firstOrCreate(['name' => $permission, 'guard_name' => 'web']);
        }

        // Create roles
        $superAdminRole = Role::firstOrCreate(['name' => 'Super Admin', 'guard_name' => 'web']);
        $managerRole = Role::firstOrCreate(['name' => 'Manager', 'guard_name' => 'web']);
        $customerServiceRole = Role::firstOrCreate(['name' => 'Customer Service', 'guard_name' => 'web']);
        $auditorRole = Role::firstOrCreate(['name' => 'Auditor', 'guard_name' => 'web']);

        // Assign all permissions to Super Admin
        $superAdminRole->syncPermissions(Permission::all());

        // Assign permissions to Manager
        $managerRole->syncPermissions([
            'view-dashboard',
            'manage-users',
            'manage-tenants',
            'manage-customers',
            'manage-periods',
            'manage-prizes',
            'manage-coupons',
            'manage-transactions',
            'manage-draws',
            'view-reports',
        ]);

        // Assign permissions to Customer Service
        $customerServiceRole->syncPermissions([
            'view-dashboard',
            'manage-customers',
            'manage-transactions',
            'view-reports',
        ]);

        // Assign permissions to Auditor
        $auditorRole->syncPermissions([
            'view-dashboard',
            'view-reports',
            'view-audit-logs',
        ]);

        // Create Super Admin user
        $adminUser = User::firstOrCreate(
            ['email' => 'admin@example.com'],
            [
                'name' => 'Administrator',
                'password' => bcrypt('password'),
                'status' => 'active',
            ]
        );
        $adminUser->assignRole($superAdminRole);

        // Create Manager user
        $managerUser = User::firstOrCreate(
            ['email' => 'manager@example.com'],
            [
                'name' => 'Manager',
                'password' => bcrypt('password'),
                'status' => 'active',
            ]
        );
        $managerUser->assignRole($managerRole);

        // Create Customer Service user
        $csUser = User::firstOrCreate(
            ['email' => 'customerservice@example.com'],
            [
                'name' => 'Customer Service Staff',
                'password' => bcrypt('password'),
                'status' => 'active',
            ]
        );
        $csUser->assignRole($customerServiceRole);

        // Create Auditor user
        $auditorUser = User::firstOrCreate(
            ['email' => 'auditor@example.com'],
            [
                'name' => 'Auditor',
                'password' => bcrypt('password'),
                'status' => 'active',
            ]
        );
        $auditorUser->assignRole($auditorRole);
    }
}
