<?php

namespace App\Http\Controllers\Admin;

use App\Exports\AuditLogsExport;
use App\Exports\CustomerPointBalancesExport;
use App\Exports\PointRedemptionsExport;
use App\Exports\WinnersExport;
use App\Http\Controllers\Controller;
use App\Models\AuditLog;
use App\Models\Prize;
use App\Models\RafflePeriod;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use PhpOffice\PhpSpreadsheet\IOFactory;

class ReportController extends Controller
{
    private const ALLOWED_ACTIONS = [
        'point_redemption.created',
        'drawing.completed',
        'winner.published',
        'winner.unpublished',
    ];

    public function index(Request $request)
    {
        abort_unless(
            $request->user()->can('view-reports') ||
            $request->user()->can('view-audit-logs') ||
            $request->user()->can('manage-prizes') ||
            $request->user()->can('manage-users'),
            403,
            'You do not have permission to view reports.'
        );

        $periods = RafflePeriod::query()
            ->orderByDesc('start_at')
            ->get(['id', 'name', 'code']);

        $prizes = Prize::query()
            ->with('rafflePeriod')
            ->orderBy('name')
            ->get(['id', 'raffle_period_id', 'name']);

        $auditActions = AuditLog::query()
            ->select('action')
            ->distinct()
            ->orderBy('action')
            ->pluck('action');

        $activePeriod = $periods->firstWhere('status', 'active');

        return view('admin.reports.index', compact(
            'periods',
            'prizes',
            'activePeriod',
            'auditActions',
        ));
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

    public function exportCustomerPointBalances(Request $request)
    {
        $this->authorizeExport();
        $validated = $request->validate([
            'period_id' => ['nullable', 'integer', 'exists:raffle_periods,id'],
            'prize_id' => ['nullable', 'integer', 'exists:prizes,id'],
        ]);

        $export = new CustomerPointBalancesExport(
            $validated['period_id'] ?? null,
            $validated['prize_id'] ?? null,
        );

        return $this->downloadExcel($export->export(), 'laporan_saldo_poin_customer.xlsx');
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

    public function exportAuditLogs(Request $request)
    {
        abort_unless(
            $request->user()->can('view-audit-logs'),
            403,
            'Only auditors can export audit logs.'
        );

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

    private function authorizeExport(): void
    {
        $user = request()->user();
        if (! $user->can('view-reports') && ! $user->can('view-audit-logs') && ! $user->can('manage-prizes') && ! $user->can('manage-users')) {
            abort(403, 'You do not have permission to export reports.');
        }
    }

    private function downloadExcel($spreadsheet, string $filename): Response
    {
        $writer = IOFactory::createWriter($spreadsheet, 'Xlsx');

        // Stream the Excel content to a temporary file
        $tmpFile = tempnam(sys_get_temp_dir(), 'export_');

        try {
            $writer->save($tmpFile);

            $response = new Response;
            $response->header('Content-Type', 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
            $response->header('Content-Disposition', 'attachment; filename="'.$filename.'"');
            $response->header('Cache-Control', 'max-age=0');

            $content = file_get_contents($tmpFile);
            $response->setContent($content);

            return $response;
        } finally {
            if (file_exists($tmpFile)) {
                unlink($tmpFile);
            }
        }
    }
}
