<?php

namespace App\Exports;

use App\Models\AuditLog;
use Illuminate\Database\Eloquent\Builder;
use PhpOffice\PhpSpreadsheet\Spreadsheet;

class AuditLogsExport
{
    public function __construct(
        private ?string $action = null,
        private ?int $userId = null,
        private ?string $from = null,
        private ?string $to = null,
    ) {}

    public function export(): Spreadsheet
    {
        $spreadsheet = new Spreadsheet;
        $sheet = $spreadsheet->getActiveSheet();
        $sheet->setTitle('Laporan Audit Log');

        $headers = [
            'No',
            'Tanggal',
            'User',
            'Aksi',
            'Target Type',
            'Target ID',
            'Metadata',
        ];

        ExportHelper::writeHeaders($sheet, $headers);

        $row = 2;
        $no = 1;

        foreach ($this->getData()->cursor() as $item) {
            $col = 1;
            $sheet->setCellValue(ExportHelper::cell($col++, $row), $no++);
            $sheet->setCellValue(ExportHelper::cell($col++, $row), $item->created_at?->format('d/m/Y H:i:s'));
            $sheet->setCellValue(ExportHelper::cell($col++, $row), $item->user?->name ?? '-');
            $sheet->setCellValue(ExportHelper::cell($col++, $row), $item->action);
            $sheet->setCellValue(ExportHelper::cell($col++, $row), $item->auditable_type);
            $sheet->setCellValue(ExportHelper::cell($col++, $row), $item->auditable_id);
            $sheet->setCellValue(ExportHelper::cell($col++, $row), json_encode($item->metadata, JSON_PRETTY_PRINT));
            $row++;
        }

        ExportHelper::formatSheet($sheet, count($headers), $row - 1);

        $sheet->getColumnDimension('G')->setWidth(60);

        return $spreadsheet;
    }

    private function getData(): Builder
    {
        $query = AuditLog::query()
            ->with('user');

        if ($this->action) {
            $query->where('action', $this->action);
        }
        if ($this->userId) {
            $query->where('user_id', $this->userId);
        }
        if ($this->from) {
            $query->whereDate('created_at', '>=', $this->from);
        }
        if ($this->to) {
            $query->whereDate('created_at', '<=', $this->to);
        }

        return $query->orderByDesc('created_at');
    }
}
