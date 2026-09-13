<?php

namespace App\Exports;

use App\Models\CustomerPointBalance;
use Illuminate\Database\Eloquent\Builder;
use PhpOffice\PhpSpreadsheet\Spreadsheet;

class CustomerPointBalancesExport
{
    public function __construct(
        private ?int $periodId = null,
        private ?int $prizeId = null,
    ) {}

    public function export(): Spreadsheet
    {
        $spreadsheet = new Spreadsheet;
        $sheet = $spreadsheet->getActiveSheet();
        $sheet->setTitle('Laporan Saldo Poin Customer');

        $headers = [
            'No',
            'Nama Customer',
            'Nomor HP',
            'No. Identitas',
            'Email',
            'Periode',
            'Hadiah',
            'Total Poin',
            'Tanggal Update',
        ];

        ExportHelper::writeHeaders($sheet, $headers);

        $row = 2;
        $no = 1;

        foreach ($this->getData()->cursor() as $item) {
            $col = 1;
            $sheet->setCellValue(ExportHelper::cell($col++, $row), $no++);
            $sheet->setCellValue(ExportHelper::cell($col++, $row), $item->customer?->name);
            $sheet->setCellValue(ExportHelper::cell($col++, $row), $item->customer?->phone);
            $sheet->setCellValue(ExportHelper::cell($col++, $row), $item->customer?->identity_number);
            $sheet->setCellValue(ExportHelper::cell($col++, $row), $item->customer?->email);
            $sheet->setCellValue(ExportHelper::cell($col++, $row), $item->rafflePeriod?->name);
            $sheet->setCellValue(ExportHelper::cell($col++, $row), $item->prize?->name);
            $sheet->setCellValue(ExportHelper::cell($col++, $row), $item->total_poin);
            $sheet->setCellValue(ExportHelper::cell($col++, $row), $item->updated_at?->format('d/m/Y H:i'));
            $row++;
        }

        ExportHelper::formatSheet($sheet, count($headers), $row - 1);

        $sheet->getColumnDimension('B')->setWidth(30);
        $sheet->getColumnDimension('E')->setWidth(30);
        $sheet->getColumnDimension('F')->setWidth(25);
        $sheet->getColumnDimension('G')->setWidth(30);

        return $spreadsheet;
    }

    private function getData(): Builder
    {
        $query = CustomerPointBalance::query()
            ->with(['customer', 'prize', 'rafflePeriod']);

        if ($this->periodId) {
            $query->where('raffle_period_id', $this->periodId);
        }
        if ($this->prizeId) {
            $query->where('prize_id', $this->prizeId);
        }

        return $query->orderBy('total_poin', 'desc')->orderBy('customer_id');
    }
}
