<?php

namespace App\Exports;

use App\Models\Winner;
use Illuminate\Database\Eloquent\Builder;
use PhpOffice\PhpSpreadsheet\Spreadsheet;

class WinnersExport
{
    public function __construct(
        private ?int $periodId = null,
        private ?int $prizeId = null,
        private ?string $from = null,
        private ?string $to = null,
        private ?bool $published = null,
    ) {}

    public function export(): Spreadsheet
    {
        $spreadsheet = new Spreadsheet;
        $sheet = $spreadsheet->getActiveSheet();
        $sheet->setTitle('Laporan Pemenang Undian');

        $headers = [
            'No',
            'Tanggal Menang',
            'Periode',
            'Hadiah',
            'Nomor Undian',
            'Nama Customer',
            'Nomor HP',
            'No. Identitas',
            'Email',
            'Status Publikasi',
            'Tanggal Dipublikasikan',
            'Catatan',
        ];

        ExportHelper::writeHeaders($sheet, $headers);

        $row = 2;
        $no = 1;

        foreach ($this->getData()->cursor() as $item) {
            $col = 1;
            $sheet->setCellValue(ExportHelper::cell($col++, $row), $no++);
            $sheet->setCellValue(ExportHelper::cell($col++, $row), $item->won_at?->format('d/m/Y H:i'));
            $sheet->setCellValue(ExportHelper::cell($col++, $row), $item->rafflePeriod?->name);
            $sheet->setCellValue(ExportHelper::cell($col++, $row), $item->prize?->name);
            $sheet->setCellValue(ExportHelper::cell($col++, $row), $item->winning_number ?? $item->raffleTicket?->ticket_number ?? '-');
            $sheet->setCellValue(ExportHelper::cell($col++, $row), $item->customer?->name);
            $sheet->setCellValue(ExportHelper::cell($col++, $row), $item->customer?->phone);
            $sheet->setCellValue(ExportHelper::cell($col++, $row), $item->customer?->identity_number);
            $sheet->setCellValue(ExportHelper::cell($col++, $row), $item->customer?->email);
            $sheet->setCellValue(ExportHelper::cell($col++, $row), $item->is_published ? 'Dipublikasikan' : 'Belum Dipublikasikan');
            $sheet->setCellValue(ExportHelper::cell($col++, $row), $item->published_at?->format('d/m/Y H:i'));
            $sheet->setCellValue(ExportHelper::cell($col++, $row), $item->notes ?? '-');
            $row++;
        }

        ExportHelper::formatSheet($sheet, count($headers), $row - 1);

        $sheet->getColumnDimension('E')->setWidth(30);
        $sheet->getColumnDimension('H')->setWidth(30);

        return $spreadsheet;
    }

    private function getData(): Builder
    {
        $query = Winner::query()
            ->with(['customer', 'prize', 'rafflePeriod', 'raffleTicket']);

        if ($this->periodId) {
            $query->where('raffle_period_id', $this->periodId);
        }
        if ($this->prizeId) {
            $query->where('prize_id', $this->prizeId);
        }
        if ($this->from) {
            $query->whereDate('won_at', '>=', $this->from);
        }
        if ($this->to) {
            $query->whereDate('won_at', '<=', $this->to);
        }
        if ($this->published !== null) {
            $query->where('is_published', $this->published);
        }

        return $query->orderByDesc('won_at');
    }
}
