<?php

namespace Database\Seeders;

use App\Models\Tenant;
use Illuminate\Database\Seeder;

class TenantSeeder extends Seeder
{
    /**
     * Seed tenant data.
     */
    public function run(): void
    {
        Tenant::factory()->count(100)->create();
    }
}
