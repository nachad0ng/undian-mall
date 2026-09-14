<?php

namespace App\Http\Controllers\Admin;

use App\Exports\AuditLogsExport;
use App\Exports\CustomerPointBalancesExport;
use App\Exports\PointRedemptionsExport;
use App\Exports\WinnersExport;
use App\Http\Controllers\Controller;
use App\Models\AuditLog;
use App\Models\CustomerPointBalance;
use App\Models\PointRedemption;
use App\Models\Prize;
use App\Models\RafflePeriod;
use App\Models\User;
use App\Models\Winner;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use PhpOffice\PhpSpreadsheet\IOFactory;
use Yajra\DataTables\Facades\DataTables;

class ReportController extends Controller
{
    private const ALLOWED_ACTIONS = [
        'point_redemption.created',
        'drawing.completed',
        'winner.published',
        'winner.unpublished',
    ];

    private function getCommonFilterData(): array
    {
        return [
            'periods' => RafflePeriod::query()->orderByDesc('start_at')->get(['id', 'name', 'code']),
            'prizes' => Prize::query()->with('rafflePeriod')->orderBy('name')->get(['id', 'raffle_period_id', 'name']),
        ];
    }

    private function authorizeExport(): void
    {
        $user = request()->user();
        if (! $user->can('view-reports') && ! $user->can('manage-prizes') && ! $user->can('manage-users')) {
            abort(403, 'You do not have permission to export reports.');
        }
    }

    private function authorizeExportAuditLogs(): void
    {
        abort_unless(
            request()->user()->can('view-audit-logs'),
            403,
            'Only auditors can export audit logs.'
        );
    }

    private function downloadExcel($spreadsheet, string $filename): Response
    {
        $writer = IOFactory::createWriter($spreadsheet, 'Xlsx');

        $tmpFile = tempnam(sys_get_temp_dir(), 'export_');

        try {
            $writer->save($tmpFile);

            $response = new Response;
            $response->header('Content-Type', 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
            $response->header('Content-Disposition', 'attachment; filename="'.$filename.'"');
            $response->header('Cache-Control', 'max-age=0');
            $response->setContent(file_get_contents($tmpFile));

            return $response;
        } finally {
            if (file_exists($tmpFile)) {
                unlink($tmpFile);
            }
        }
    }

    // =====================================================
    // Dashboard: List of all reports
    // =====================================================

    public function reportsIndex(Request $request)
    {
        abort_unless(
            $request->user()->can('view-reports') ||
            $request->user()->can('manage-prizes') ||
            $request->user()->can('manage-users') ||
            $request->user()->can('view-audit-logs'),
            403,
            'You do not have permission to view reports.'
        );

        return view('admin.reports.index', $this->getCommonFilterData());
    }

    // =====================================================
    // 1. Point Redemptions Report
    // =====================================================

    public function pointRedemptionsIndex(Request $request)
    {
        abort_unless(
            $request->user()->can('view-reports') ||
            $request->user()->can('manage-prizes') ||
            $request->user()->can('manage-users'),
            403,
            'You do not have permission to view reports.'
        );

        $filterData = $this->getCommonFilterData();

        return view('admin.reports.point_redemptions', $filterData);
    }

    public function pointRedemptionsData(Request $request)
    {
        abort_unless(
            $request->user()->can('view-reports') ||
            $request->user()->can('manage-prizes') ||
            $request->user()->can('manage-users'),
            403,
            'You do not have permission to view reports.'
        );

        $validated = $request->validate([
            'period_id' => ['nullable', 'integer', 'exists:raffle_periods,id'],
            'prize_id' => ['nullable', 'integer', 'exists:prizes,id'],
            'from' => ['nullable', 'date'],
            'to' => ['nullable', 'date', 'after_or_equal:from'],
        ]);

        $query = PointRedemption::query()
            ->select('point_redemptions.*')
            ->with(['customer', 'prize', 'purchase.tenant', 'cs', 'rafflePeriod']);

        if ($validated['period_id'] ?? null) {
            $query->where('raffle_period_id', $validated['period_id']);
        }
        if ($validated['prize_id'] ?? null) {
            $query->where('prize_id', $validated['prize_id']);
        }
        if ($validated['from'] ?? null) {
            $query->whereDate('redeemed_at', '>=', $validated['from']);
        }
        if ($validated['to'] ?? null) {
            $query->whereDate('redeemed_at', '<=', $validated['to']);
        }

        return DataTables::eloquent($query)
            ->addColumn('customer_name', fn (PointRedemption $r) => $r->customer->name)
            ->addColumn('prize_name', fn (PointRedemption $r) => $r->prize->name)
            ->addColumn('receipt_number', fn (PointRedemption $r) => $r->purchase->receipt_number ?? '-')
            ->addColumn('tenant_name', fn (PointRedemption $r) => $r->purchase && $r->purchase->tenant ? $r->purchase->tenant->name : '-')
            ->addColumn('cs_name', fn (PointRedemption $r) => $r->cs ? $r->cs->name : '-')
            ->addColumn('period_name', fn (PointRedemption $r) => $r->rafflePeriod->name ?? '-')
            ->addColumn('payment_type_name', fn (PointRedemption $r) => $r->payment_type_name_snapshot ?? '-')
            ->addColumn('redeemed_at_formatted', fn (PointRedemption $r) => $r->redeemed_at->format('d M Y H:i'))
            ->addColumn('nominal_struk_formatted', fn (PointRedemption $r) => 'Rp '.number_format($r->nominal_struk, 0, ',', '.'))
            ->rawColumns([])
            ->make(true);
    }

    public function exportPointRedemptions(Request $request)
    {
        $this->authorizeExport();

        $validated = $request->validate([
            'period_id' => ['nullable', 'integer', 'exists:raffle_periods,id'],
            'prize_id' => ['nullable', 'integer', 'exists:prizes,id'],
            'from' => ['nullable', 'date'],
            'to' => ['nullable', 'date', 'after_or_equal:from'],
        ]);

        $export = new PointRedemptionsExport(
            $validated['period_id'] ?? null,
            $validated['prize_id'] ?? null,
            $validated['from'] ?? null,
            $validated['to'] ?? null,
        );

        return $this->downloadExcel($export->export(), 'laporan_poin_redemption.xlsx');
    }

    // =====================================================
    // 2. Customer Point Balances Report
    // =====================================================

    public function customerPointBalancesIndex(Request $request)
    {
        abort_unless(
            $request->user()->can('view-reports') ||
            $request->user()->can('manage-prizes') ||
            $request->user()->can('manage-users'),
            403,
            'You do not have permission to view reports.'
        );

        $filterData = $this->getCommonFilterData();

        return view('admin.reports.customer_point_balances', $filterData);
    }

    public function customerPointBalancesData(Request $request)
    {
        abort_unless(
            $request->user()->can('view-reports') ||
            $request->user()->can('manage-prizes') ||
            $request->user()->can('manage-users'),
            403,
            'You do not have permission to view reports.'
        );

        $validated = $request->validate([
            'period_id' => ['nullable', 'integer', 'exists:raffle_periods,id'],
            'prize_id' => ['nullable', 'integer', 'exists:prizes,id'],
        ]);

        $query = CustomerPointBalance::query()
            ->with(['customer', 'prize', 'rafflePeriod']);

        if ($validated['period_id'] ?? null) {
            $query->where('raffle_period_id', $validated['period_id']);
        }
        if ($validated['prize_id'] ?? null) {
            $query->where('prize_id', $validated['prize_id']);
        }

        return DataTables::eloquent($query)
            ->addColumn('customer_name', fn ($r) => $r->customer->name)
            ->addColumn('phone', fn ($r) => $r->customer->phone)
            ->addColumn('identity_number', fn ($r) => $r->customer->identity_number)
            ->addColumn('email', fn ($r) => $r->customer->email)
            ->addColumn('period_name', fn ($r) => $r->rafflePeriod->name)
            ->addColumn('prize_name', fn ($r) => $r->prize->name)
            ->addColumn('updated_at_formatted', fn ($r) => $r->updated_at->format('d M Y H:i'))
            ->make(true);
    }

    public function exportCustomerPointBalances(Request $request)
    {
        $this->authorizeExport();

        $validated = $request->validate([
            'period_id' => ['nullable', 'integer', 'exists:raffle_periods,id'],
            'prize_id' => ['nullable', 'integer', 'exists:prizes,id'],
            'from' => ['nullable', 'date'],
            'to' => ['nullable', 'date', 'after_or_equal:from'],
        ]);

        $export = new CustomerPointBalancesExport(
            $validated['period_id'] ?? null,
            $validated['prize_id'] ?? null,
        );

        return $this->downloadExcel($export->export(), 'laporan_saldo_poin_customer.xlsx');
    }

    // =====================================================
    // 3. Winners Report
    // =====================================================

    public function winnersIndex(Request $request)
    {
        abort_unless(
            $request->user()->can('view-reports') ||
            $request->user()->can('manage-prizes') ||
            $request->user()->can('manage-users'),
            403,
            'You do not have permission to view reports.'
        );

        $filterData = $this->getCommonFilterData();

        return view('admin.reports.winners', $filterData);
    }

    public function winnersData(Request $request)
    {
        abort_unless(
            $request->user()->can('view-reports') ||
            $request->user()->can('manage-prizes') ||
            $request->user()->can('manage-users'),
            403,
            'You do not have permission to view reports.'
        );

        $validated = $request->validate([
            'period_id' => ['nullable', 'integer', 'exists:raffle_periods,id'],
            'prize_id' => ['nullable', 'integer', 'exists:prizes,id'],
            'from' => ['nullable', 'date'],
            'to' => ['nullable', 'date', 'after_or_equal:from'],
            'published' => ['nullable', 'in:published,not_published'],
        ]);

        $query = Winner::query()
            ->with(['customer', 'prize', 'rafflePeriod']);

        if ($validated['period_id'] ?? null) {
            $query->where('raffle_period_id', $validated['period_id']);
        }
        if ($validated['prize_id'] ?? null) {
            $query->where('prize_id', $validated['prize_id']);
        }
        if ($validated['from'] ?? null) {
            $query->whereDate('won_at', '>=', $validated['from']);
        }
        if ($validated['to'] ?? null) {
            $query->whereDate('won_at', '<=', $validated['to']);
        }

        $publishedFilter = $validated['published'] ?? null;
        if ($publishedFilter === 'published') {
            $query->where('is_published', true);
        } elseif ($publishedFilter === 'not_published') {
            $query->where('is_published', false);
        }

        return DataTables::eloquent($query)
            ->addColumn('customer_name', fn ($r) => $r->customer->name)
            ->addColumn('phone', fn ($r) => $r->customer->phone)
            ->addColumn('identity_number', fn ($r) => $r->customer->identity_number)
            ->addColumn('email', fn ($r) => $r->customer->email)
            ->addColumn('period_name', fn ($r) => $r->rafflePeriod->name)
            ->addColumn('prize_name', fn ($r) => $r->prize->name)
            ->addColumn('won_at_formatted', fn ($r) => $r->won_at->format('d M Y H:i'))
            ->addColumn('published_status', fn ($r) => $r->is_published ? 'Dipublikasikan' : 'Belum')
            ->addColumn('published_at_formatted', fn ($r) => $r->published_at?->format('d M Y H:i') ?? '-')
            ->make(true);
    }

    public function exportWinners(Request $request)
    {
        $this->authorizeExport();

        $validated = $request->validate([
            'period_id' => ['nullable', 'integer', 'exists:raffle_periods,id'],
            'prize_id' => ['nullable', 'integer', 'exists:prizes,id'],
            'from' => ['nullable', 'date'],
            'to' => ['nullable', 'date', 'after_or_equal:from'],
            'published' => ['nullable', 'in:published,not_published'],
            'user_id' => ['nullable', 'integer', 'exists:users,id'],
        ]);

        $publishedFilter = $validated['published'] ?? null;
        $published = $publishedFilter === 'published'
            ? true
            : ($publishedFilter === 'not_published' ? false : null);

        $export = new WinnersExport(
            $validated['period_id'] ?? null,
            $validated['prize_id'] ?? null,
            $validated['from'] ?? null,
            $validated['to'] ?? null,
            $published,
        );

        return $this->downloadExcel($export->export(), 'laporan_pemenang_undian.xlsx');
    }

    // =====================================================
    // 4. Audit Logs Report (Auditor only)
    // =====================================================

    public function auditLogsIndex(Request $request)
    {
        abort_unless(
            $request->user()->can('view-audit-logs'),
            403,
            'You do not have permission to view audit logs.'
        );

        $actions = AuditLog::query()
            ->select('action')
            ->distinct()
            ->orderBy('action')
            ->pluck('action');
        $users = User::query()->orderBy('name')->get(['id', 'name']);

        return view('admin.reports.audit_logs', compact('actions', 'users'));
    }

    public function auditLogsData(Request $request)
    {
        abort_unless(
            $request->user()->can('view-audit-logs'),
            403,
            'You do not have permission to view audit logs.'
        );

        $validated = $request->validate([
            'action' => ['nullable', 'string', 'in:'.implode(',', self::ALLOWED_ACTIONS)],
            'user_id' => ['nullable', 'integer', 'exists:users,id'],
            'from' => ['nullable', 'date'],
            'to' => ['nullable', 'date', 'after_or_equal:from'],
        ]);

        $query = AuditLog::query()->with('user');

        if ($validated['action'] ?? null) {
            $query->where('action', $validated['action']);
        }
        if ($validated['user_id'] ?? null) {
            $query->where('user_id', $validated['user_id']);
        }
        if ($validated['from'] ?? null) {
            $query->whereDate('created_at', '>=', $validated['from']);
        }
        if ($validated['to'] ?? null) {
            $query->whereDate('created_at', '<=', $validated['to']);
        }

        return DataTables::eloquent($query->latest('created_at'))
            ->addColumn('user_name', fn (AuditLog $log) => $log->user ? $log->user->name : 'System')
            ->addColumn('created_at_formatted', fn (AuditLog $log) => $log->created_at->format('d M Y H:i:s'))
            ->addColumn('metadata_json', fn (AuditLog $log) => json_encode($log->metadata, JSON_UNESCAPED_UNICODE))
            ->addColumn('auditable', fn (AuditLog $log) => class_basename($log->auditable_type).' #'.$log->auditable_id)
            ->make(true);
    }

    public function exportAuditLogs(Request $request)
    {
        $this->authorizeExportAuditLogs();

        $validated = $request->validate([
            'action' => ['nullable', 'string', 'in:'.implode(',', self::ALLOWED_ACTIONS)],
            'user_id' => ['nullable', 'integer', 'exists:users,id'],
            'from' => ['nullable', 'date'],
            'to' => ['nullable', 'date', 'after_or_equal:from'],
        ]);

        $export = new AuditLogsExport(
            $validated['action'] ?? null,
            $validated['user_id'] ?? null,
            $validated['from'] ?? null,
            $validated['to'] ?? null,
        );

        return $this->downloadExcel($export->export(), 'laporan_audit_log.xlsx');
    }
}
