<?php

namespace Tests\Feature;

use App\Models\RafflePeriod;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use PhpOffice\PhpSpreadsheet\IOFactory;
use Tests\TestCase;

class ReportExportTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed();
    }

    private function loginAsSuperAdmin(): User
    {
        return User::where('email', 'admin@example.com')->firstOrFail();
    }

    private function assertValidXlsxResponse($response, string $filename): void
    {
        $response->assertHeader('Content-Type', 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
        $response->assertHeader('Content-Disposition', 'attachment; filename="'.$filename.'"');

        $tmpFile = tempnam(sys_get_temp_dir(), 'export_test');
        file_put_contents($tmpFile, $response->getContent());
        $spreadsheet = IOFactory::load($tmpFile);
        $sheet = $spreadsheet->getActiveSheet();
        $this->assertGreaterThanOrEqual(1, $sheet->getHighestDataRow(), 'Sheet should have at least a header row.');
        unlink($tmpFile);
    }

    public function test_point_redemptions_export(): void
    {
        $this->actingAs($this->loginAsSuperAdmin());

        $response = $this->get(route('admin.reports.point-redemptions.export'));
        $response->assertOk();
        $this->assertValidXlsxResponse($response, 'laporan_poin_redemption.xlsx');
    }

    public function test_point_redemptions_export_with_filters(): void
    {
        $this->actingAs($this->loginAsSuperAdmin());

        $period = RafflePeriod::first();
        $response = $this->get(route('admin.reports.point-redemptions.export', [
            'period_id' => $period->id,
            'from' => now()->subDays(30)->format('Y-m-d'),
            'to' => now()->format('Y-m-d'),
        ]));
        $response->assertOk();
        $this->assertValidXlsxResponse($response, 'laporan_poin_redemption.xlsx');
    }

    public function test_customer_point_balances_export(): void
    {
        $this->actingAs($this->loginAsSuperAdmin());

        $response = $this->get(route('admin.reports.customer-point-balances.export'));
        $response->assertOk();
        $this->assertValidXlsxResponse($response, 'laporan_saldo_poin_customer.xlsx');
    }

    public function test_winners_export(): void
    {
        $this->actingAs($this->loginAsSuperAdmin());

        $response = $this->get(route('admin.reports.winners.export'));
        $response->assertOk();
        $this->assertValidXlsxResponse($response, 'laporan_pemenang_undian.xlsx');
    }

    public function test_winners_export_with_published_filter(): void
    {
        $this->actingAs($this->loginAsSuperAdmin());

        $response = $this->get(route('admin.reports.winners.export', ['published' => 'published']));
        $response->assertOk();
        $this->assertValidXlsxResponse($response, 'laporan_pemenang_undian.xlsx');
    }

    public function test_audit_logs_export_by_auditor(): void
    {
        $auditor = User::where('email', 'auditor@example.com')->firstOrFail();
        $this->actingAs($auditor);

        $response = $this->get(route('admin.reports.audit-logs.export'));
        $response->assertOk();
        $this->assertValidXlsxResponse($response, 'laporan_audit_log.xlsx');
    }

    public function test_audit_logs_export_forbidden_for_manager(): void
    {
        $manager = User::where('email', 'manager@example.com')->firstOrFail();
        $this->actingAs($manager);

        $response = $this->get(route('admin.reports.audit-logs.export'));
        $response->assertForbidden();
    }

    public function test_audit_logs_export_with_action_filter(): void
    {
        $auditor = User::where('email', 'auditor@example.com')->firstOrFail();
        $this->actingAs($auditor);

        $response = $this->get(route('admin.reports.audit-logs.export', [
            'action' => 'drawing.completed',
        ]));
        $response->assertOk();
        $this->assertValidXlsxResponse($response, 'laporan_audit_log.xlsx');
    }

    public function test_reports_index_page_loads(): void
    {
        $this->actingAs($this->loginAsSuperAdmin());

        $response = $this->get(route('admin.reports.index'));
        $response->assertOk()
            ->assertViewHas('periods')
            ->assertViewHas('prizes');
    }

    public function test_reports_accessible_by_manager(): void
    {
        $manager = User::where('email', 'manager@example.com')->firstOrFail();
        $this->actingAs($manager);

        $response = $this->get(route('admin.reports.index'));
        $response->assertOk();
    }

    public function test_reports_accessible_by_auditor(): void
    {
        $auditor = User::where('email', 'auditor@example.com')->firstOrFail();
        $this->actingAs($auditor);

        $response = $this->get(route('admin.reports.index'));
        $response->assertOk();
    }

    public function test_reports_accessible_by_customer_service(): void
    {
        $cs = User::where('email', 'customerservice@example.com')->firstOrFail();
        $this->actingAs($cs);

        $response = $this->get(route('admin.reports.index'));
        $response->assertOk();
    }

    public function test_export_forbidden_for_user_without_permission(): void
    {
        $user = User::factory()->create(['password' => bcrypt('password')]);
        $this->actingAs($user);

        $response = $this->get(route('admin.reports.point-redemptions.export'));
        $response->assertForbidden();
    }

    // =====================================================
    // Tests for new report pages (load → datatable → export)
    // =====================================================

    public function test_point_redemptions_report_page_loads(): void
    {
        $this->actingAs($this->loginAsSuperAdmin());

        $response = $this->get(route('admin.reports.point-redemptions.index'));
        $response->assertOk()
            ->assertViewHas('periods')
            ->assertViewHas('prizes');
    }

    public function test_point_redemptions_data_endpoint_returns_datatables_json(): void
    {
        $this->actingAs($this->loginAsSuperAdmin());

        $response = $this->get(route('admin.reports.point-redemptions.data', [
            'draw' => 1,
            'start' => 0,
            'length' => 10,
        ]));
        $response->assertOk();
    }

    public function test_customer_point_balances_report_page_loads(): void
    {
        $this->actingAs($this->loginAsSuperAdmin());

        $response = $this->get(route('admin.reports.customer-point-balances.index'));
        $response->assertOk()
            ->assertViewHas('periods')
            ->assertViewHas('prizes');
    }

    public function test_winners_report_page_loads(): void
    {
        $this->actingAs($this->loginAsSuperAdmin());

        $response = $this->get(route('admin.reports.winners.index'));
        $response->assertOk()
            ->assertViewHas('periods')
            ->assertViewHas('prizes');
    }

    public function test_audit_logs_report_page_loads_for_auditor(): void
    {
        $auditor = User::where('email', 'auditor@example.com')->firstOrFail();
        $this->actingAs($auditor);

        $response = $this->get(route('admin.reports.audit-logs.index'));
        $response->assertOk()
            ->assertViewHas('actions')
            ->assertViewHas('users');
    }

    public function test_audit_logs_report_page_forbidden_for_manager(): void
    {
        $manager = User::where('email', 'manager@example.com')->firstOrFail();
        $this->actingAs($manager);

        $response = $this->get(route('admin.reports.audit-logs.index'));
        $response->assertForbidden();
    }

    public function test_reports_menu_accessible_by_manager(): void
    {
        $manager = User::where('email', 'manager@example.com')->firstOrFail();
        $this->actingAs($manager);

        $response = $this->get(route('admin.reports.index'));
        $response->assertOk();
    }
}
