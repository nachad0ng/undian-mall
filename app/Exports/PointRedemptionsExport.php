<?php

namespace App\Exports;

use App\Models\PointRedemption;
use Illuminate\Database\Eloquent\Builder;
use PhpOffice\PhpSpreadsheet\Spreadsheet;

class PointRedemptionsExport
{
    public function __construct(
        private ?int $periodId = null,
        private ?int $prizeId = null,
        private ?string $from = null,
        private ?string $to = null,
    ) {}

    public function export(): Spreadsheet
    {
        $spreadsheet = new Spreadsheet;
        $sheet = $spreadsheet->getActiveSheet();
        $sheet->setTitle('Laporan Poin Redemption');

        $headers = [
            'No',
            'Tanggal Tukar',
            'Periode',
            'Hadiah',
            'Customer',
            'Nomor HP',
            'No. Struk',
            'Tenant',
            'Nominal Struk',
            'Tipe Pembayaran',
            'Nominal Per Poin',
            'Poin dari Nominal',
            'Bonus Pembayaran',
            'Total Poin',
            'Petugas CS',
            'Status',
            'Catatan',
        ];

        ExportHelper::writeHeaders($sheet, $headers);

        $row = 2;
        $no = 1;

        foreach ($this->getData()->cursor() as $item) {
            $col = 1;
            $sheet->setCellValue(ExportHelper::cell($col++, $row), $no++);
            $sheet->setCellValue(ExportHelper::cell($col++, $row), $item->redeemed_at?->format('d/m/Y H:i'));
            $sheet->setCellValue(ExportHelper::cell($col++, $row), $item->rafflePeriod?->name);
            $sheet->setCellValue(ExportHelper::cell($col++, $row), $item->prize?->name);
            $sheet->setCellValue(ExportHelper::cell($col++, $row), $item->customer?->name);
            $sheet->setCellValue(ExportHelper::cell($col++, $row), $item->customer?->phone);
            $sheet->setCellValue(ExportHelper::cell($col++, $row), $item->purchase?->receipt_number);
            $sheet->setCellValue(ExportHelper::cell($col++, $row), $item->purchase?->tenant?->name);
            $sheet->setCellValue(ExportHelper::cell($col++, $row), 'Rp '.number_format($item->nominal_struk, 0, ',', '.'));
            $sheet->setCellValue(ExportHelper::cell($col++, $row), $item->payment_type_name_snapshot ?? '-');
            $sheet->setCellValue(ExportHelper::cell($col++, $row), $item->nominal_per_poin_snapshot ?? '-');
            $sheet->setCellValue(ExportHelper::cell($col++, $row), $item->poin_dari_nominal ?? 0);
            $sheet->setCellValue(ExportHelper::cell($col++, $row), $item->poin_bonus_pembayaran ?? 0);
            $sheet->setCellValue(ExportHelper::cell($col++, $row), $item->total_poin_didapat);
            $sheet->setCellValue(ExportHelper::cell($col++, $row), $item->cs?->name ?? '-');
            $sheet->setCellValue(ExportHelper::cell($col++, $row), $item->status);
            $sheet->setCellValue(ExportHelper::cell($col++, $row), $item->notes ?? '-');
            $row++;
        }

        ExportHelper::formatSheet($sheet, count($headers), $row - 1);

        return $spreadsheet;
    }

    private function getData(): Builder
    {
        $query = PointRedemption::query()
            ->select('point_redemptions.*')
            ->with(['customer', 'prize', 'purchase.tenant', 'cs', 'rafflePeriod']);

        if ($this->periodId) {
            $query->where('raffle_period_id', $this->periodId);
        }
        if ($this->prizeId) {
            $query->where('prize_id', $this->prizeId);
        }
        if ($this->from) {
            $query->whereDate('redeemed_at', '>=', $this->from);
        }
        if ($this->to) {
            $query->whereDate('redeemed_at', '<=', $this->to);
        }

        return $query->orderByDesc('redeemed_at');
    }
}
