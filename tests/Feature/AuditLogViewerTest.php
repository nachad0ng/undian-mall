<?php

namespace Tests\Feature;

use App\Models\AuditLog;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AuditLogViewerTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed();
    }

    public function test_auditor_can_view_and_filter_audit_logs(): void
    {
        $auditor = User::where('email', 'auditor@example.com')->firstOrFail();
        AuditLog::create([
            'user_id' => $auditor->id,
            'action' => 'drawing.completed',
            'auditable_type' => 'App\\Models\\Drawing',
            'auditable_id' => 99,
            'metadata' => ['pool_count' => 10],
        ]);
        AuditLog::create([
            'user_id' => $auditor->id,
            'action' => 'point_redemption.created',
            'auditable_type' => 'App\\Models\\PointRedemption',
            'auditable_id' => 100,
        ]);

        $this->actingAs($auditor)
            ->get(route('admin.audit-logs.index', ['action' => 'drawing.completed']))
            ->assertOk()
            ->assertSee('drawing.completed')
            ->assertSee('pool_count')
            ->assertSee('Drawing #99')
            ->assertDontSee('PointRedemption #100');
    }

    public function test_user_without_audit_permission_cannot_view_audit_logs(): void
    {
        $manager = User::where('email', 'manager@example.com')->firstOrFail();

        $this->actingAs($manager)
            ->get(route('admin.audit-logs.index'))
            ->assertForbidden();
    }
}
