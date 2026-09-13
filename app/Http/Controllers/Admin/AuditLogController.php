<?php

namespace App\Http\Controllers\Admin;

use App\Exports\AuditLogsExport;
use App\Http\Controllers\Controller;
use App\Models\AuditLog;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use PhpOffice\PhpSpreadsheet\IOFactory;

class AuditLogController extends Controller
{
    public function index(Request $request)
    {
        $validated = $request->validate([
            'action' => ['nullable', 'string', 'max:100'],
            'user_id' => ['nullable', 'integer', 'exists:users,id'],
            'from' => ['nullable', 'date'],
            'to' => ['nullable', 'date', 'after_or_equal:from'],
        ]);

        $logs = AuditLog::query()
            ->with('user')
            ->when($validated['action'] ?? null, fn ($query, $action) => $query->where('action', $action))
            ->when($validated['user_id'] ?? null, fn ($query, $userId) => $query->where('user_id', $userId))
            ->when($validated['from'] ?? null, fn ($query, $from) => $query->whereDate('created_at', '>=', $from))
            ->when($validated['to'] ?? null, fn ($query, $to) => $query->whereDate('created_at', '<=', $to))
            ->latest('created_at')
            ->paginate(25)
            ->withQueryString();

        $actions = AuditLog::query()->select('action')->distinct()->orderBy('action')->pluck('action');
        $users = User::query()->orderBy('name')->get(['id', 'name']);

        return view('admin.audit-logs.index', compact('logs', 'actions', 'users'));
    }

    public function export(Request $request)
    {
        $validated = $request->validate([
            'action' => ['nullable', 'string', 'max:100'],
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

        $writer = IOFactory::createWriter($export->export(), 'Xlsx');
        $tmpFile = tempnam(sys_get_temp_dir(), 'audit_export_');

        try {
            $writer->save($tmpFile);

            $response = new Response;
            $response->header('Content-Type', 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
            $response->header('Content-Disposition', 'attachment; filename="audit_logs.xlsx"');
            $response->header('Cache-Control', 'max-age=0');
            $response->setContent(file_get_contents($tmpFile));

            return $response;
        } finally {
            if (file_exists($tmpFile)) {
                unlink($tmpFile);
            }
        }
    }
}
