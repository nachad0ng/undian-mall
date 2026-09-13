<?php

namespace App\Exports;

use PhpOffice\PhpSpreadsheet\Style\Alignment;
use PhpOffice\PhpSpreadsheet\Style\Border;
use PhpOffice\PhpSpreadsheet\Style\Fill;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

class ExportHelper
{
    /**
     * Write header row with styling.
     */
    public static function writeHeaders(Worksheet $sheet, array $headers): void
    {
        $col = 1;
        foreach ($headers as $header) {
            $cell = self::cell($col, 1);
            $sheet->setCellValue($cell, $header);
            $sheet->getStyle($cell)->applyFromArray([
                'font' => ['bold' => true],
                'fill' => [
                    'fillType' => Fill::FILL_SOLID,
                    'startColor' => ['argb' => 'FF547C9F'],
                ],
                'alignment' => [
                    'fontColor' => ['argb' => 'FFFFFFFF'],
                    'horizontal' => Alignment::HORIZONTAL_CENTER,
                ],
            ]);
            $sheet->getColumnDimensionByColumn($col)->setWidth(20);
            $col++;
        }
    }

    /**
     * Apply border formatting to the used range.
     */
    public static function formatSheet(Worksheet $sheet, int $lastCol, int $lastRow): void
    {
        $styleArray = [
            'borders' => [
                'allBorders' => [
                    'borderStyle' => Border::BORDER_THIN,
                ],
            ],
        ];

        for ($i = 1; $i <= $lastRow; $i++) {
            for ($j = 1; $j <= $lastCol; $j++) {
                $sheet->getStyle(self::cell($j, $i))->applyFromArray($styleArray);
            }
        }
    }

    /**
     * Convert column number and row to A1 notation.
     */
    public static function cell(int $col, int $row): string
    {
        $letters = '';
        while ($col > 0) {
            $letters = chr(65 + ($col - 1) % 26).$letters;
            $col = intdiv($col - 1, 26);
        }

        return $letters.$row;
    }
}
