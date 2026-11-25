<?php

namespace App\Exports;

use App\Services\LicenseReport;
use Carbon\CarbonInterface;
use PhpOffice\PhpSpreadsheet\Cell\Coordinate;
use PhpOffice\PhpSpreadsheet\Shared\Date as ExcelDate;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use PhpOffice\PhpSpreadsheet\Style\Border;
use PhpOffice\PhpSpreadsheet\Style\Fill;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;

/**
 * Laporan lisensi (.xlsx) untuk RAB, 3 sheet:
 * 1. Rekap RAB  — per item: Qty, lokasi, harga satuan (bisa diisi), jumlah = rumus Qty × harga.
 * 2. Per Bulan  — per bulan berakhir × item: kapan anggaran dibutuhkan.
 * 3. Rincian    — satu baris per lisensi (SN, lokasi, tanggal, status).
 */
class LicenseReportExcel
{
    private const MONEY = '#,##0';
    private const DATE = 'dd/mm/yyyy';
    private const HEADER_FILL = '1E3A5F';

    public function __construct(private readonly LicenseReport $report, private readonly string $printedBy) {}

    /** Simpan ke file sementara dan kembalikan path-nya. */
    public function store(): string
    {
        $book = new Spreadsheet();
        $book->getProperties()->setCreator(config('app.name'))->setTitle('Laporan Lisensi');

        $this->summarySheet($book->getActiveSheet());
        $this->monthlySheet($book->createSheet());
        $this->detailSheet($book->createSheet());
        $book->setActiveSheetIndex(0);

        $path = tempnam(sys_get_temp_dir(), 'lic').'.xlsx';
        (new Xlsx($book))->save($path);
        $book->disconnectWorksheets();

        return $path;
    }

    private function summarySheet(Worksheet $sheet): void
    {
        $sheet->setTitle('Rekap RAB');
        $headers = ['No', 'Vendor', 'Perangkat / Produk', 'Jenis Lisensi', 'Paket Lisensi', 'Qty', 'Satuan',
            'Berakhir Paling Awal', 'Berakhir Paling Akhir', 'Lokasi', 'Harga Satuan (Rp)', 'Jumlah Harga (Rp)'];
        $row = $this->title($sheet, 'Rekap Kebutuhan Perpanjangan Lisensi (RAB)', count($headers));
        $first = $this->headerRow($sheet, $row, $headers);
        $row = $first;

        foreach ($this->report->summary() as $i => $s) {
            $row++;
            $sheet->fromArray([
                $i + 1, $s['vendor'], $s['item'], $s['license_type'], $s['package'], $s['qty'], $s['unit'],
                $this->date($s['first_end']), $this->date($s['last_end']), implode(', ', $s['branches']),
                $s['unit_price'],
            ], null, 'A'.$row, true);
            $sheet->setCellValue('L'.$row, "=F{$row}*K{$row}");
        }

        $last = $row;
        if ($last > $first) {
            $row++;
            $sheet->setCellValue('A'.$row, 'TOTAL');
            $sheet->mergeCells("A{$row}:E{$row}");
            $sheet->setCellValue('F'.$row, '=SUM(F'.($first + 1).":F{$last})");
            $sheet->setCellValue('L'.$row, '=SUM(L'.($first + 1).":L{$last})");
            $sheet->getStyle("A{$row}:L{$row}")->getFont()->setBold(true);
            $sheet->getStyle("A{$row}:L{$row}")->getFill()->setFillType(Fill::FILL_SOLID)->getStartColor()->setRGB('E2E8F0');
        }

        $this->bodyStyle($sheet, $first, $row, 'L');
        $sheet->getStyle("H{$first}:I{$row}")->getNumberFormat()->setFormatCode(self::DATE);
        $sheet->getStyle("K{$first}:L{$row}")->getNumberFormat()->setFormatCode(self::MONEY);
        $sheet->getStyle("F{$first}:G{$row}")->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
        $sheet->getStyle('J'.($first + 1).":J{$row}")->getAlignment()->setWrapText(true);
        $sheet->getColumnDimension('J')->setAutoSize(false)->setWidth(45);

        $row += 2;
        $sheet->setCellValue('A'.$row, 'Catatan: kolom "Harga Satuan" diisi dari nilai lisensi yang tercatat (kosong bila belum ada). '
            .'Silakan isi/ubah harga satuan — kolom "Jumlah Harga" terhitung otomatis.');
        $sheet->getStyle('A'.$row)->getFont()->setItalic(true)->getColor()->setRGB('64748B');
    }

    private function monthlySheet(Worksheet $sheet): void
    {
        $sheet->setTitle('Per Bulan');
        $headers = ['Bulan Berakhir', 'Vendor', 'Perangkat / Produk', 'Paket Lisensi', 'Qty', 'Satuan', 'Lokasi'];
        $row = $this->title($sheet, 'Jadwal Lisensi Berakhir per Bulan', count($headers));
        $first = $this->headerRow($sheet, $row, $headers);
        $row = $first;

        foreach ($this->report->byMonth() as $m) {
            $row++;
            $sheet->fromArray([
                ucfirst($m['month']->translatedFormat('F Y')), $m['vendor'], $m['item'], $m['package'],
                $m['qty'], $m['unit'], implode(', ', $m['branches']),
            ], null, 'A'.$row, true);
        }

        $last = $row;
        if ($last > $first) {
            $row++;
            $sheet->setCellValue('A'.$row, 'TOTAL');
            $sheet->mergeCells("A{$row}:D{$row}");
            $sheet->setCellValue('E'.$row, '=SUM(E'.($first + 1).":E{$last})");
            $sheet->getStyle("A{$row}:G{$row}")->getFont()->setBold(true);
            $sheet->getStyle("A{$row}:G{$row}")->getFill()->setFillType(Fill::FILL_SOLID)->getStartColor()->setRGB('E2E8F0');
        }

        $this->bodyStyle($sheet, $first, $row, 'G');
        $sheet->getStyle("E{$first}:F{$row}")->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
        $sheet->getStyle('G'.($first + 1).":G{$row}")->getAlignment()->setWrapText(true);
        $sheet->getColumnDimension('G')->setAutoSize(false)->setWidth(45);
    }

    private function detailSheet(Worksheet $sheet): void
    {
        $sheet->setTitle('Rincian');
        $headers = ['No', 'Kode', 'Vendor', 'Perangkat / Produk', 'Serial Number', 'Lokasi', 'Jenis Lisensi', 'Paket Lisensi',
            'Qty', 'Mulai', 'Berakhir', 'Sisa Hari', 'Status', 'Nilai (Rp)'];
        $row = $this->title($sheet, 'Rincian Lisensi', count($headers));
        $first = $this->headerRow($sheet, $row, $headers);
        $row = $first;

        foreach ($this->report->details() as $i => $d) {
            $row++;
            $sheet->fromArray([
                $i + 1, $d['code'], $d['vendor'], $d['item'], null, $d['branch'], $d['license_type'], $d['package'],
                $d['qty'], $this->date($d['start_date']), $this->date($d['end_date']), $d['days_remaining'], $d['status'], $d['cost'],
            ], null, 'A'.$row, true);
            // SN disimpan sebagai teks supaya angka di depan (mis. 0212...) tidak hilang
            $sheet->setCellValueExplicit('E'.$row, (string) $d['serial_number'], \PhpOffice\PhpSpreadsheet\Cell\DataType::TYPE_STRING);
        }

        $this->bodyStyle($sheet, $first, $row, 'N');
        $sheet->getStyle("J{$first}:K{$row}")->getNumberFormat()->setFormatCode(self::DATE);
        $sheet->getStyle("N{$first}:N{$row}")->getNumberFormat()->setFormatCode(self::MONEY);
        $sheet->getStyle("I{$first}:I{$row}")->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
        $sheet->getStyle("L{$first}:L{$row}")->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
    }

    /** Judul + keterangan filter di atas tabel. Mengembalikan baris header tabel. */
    private function title(Worksheet $sheet, string $title, int $cols): int
    {
        $lastCol = Coordinate::stringFromColumnIndex($cols);
        $sheet->setCellValue('A1', strtoupper($title));
        $sheet->mergeCells("A1:{$lastCol}1");
        $sheet->getStyle('A1')->getFont()->setBold(true)->setSize(14);

        // Keterangan filter digabung satu sel & di-merge selebar tabel → tidak ikut menghitung lebar kolom (kolom "No" tetap kecil)
        $row = 2;
        foreach ($this->report->filterLabels() + ['Dicetak' => tgl_waktu(now()).' oleh '.$this->printedBy] as $label => $value) {
            $text = new \PhpOffice\PhpSpreadsheet\RichText\RichText();
            $text->createTextRun($label.': ')->getFont()->setBold(true);
            $text->createText($value);
            $sheet->setCellValue('A'.$row, $text);
            $sheet->mergeCells("A{$row}:{$lastCol}{$row}");
            $row++;
        }

        return $row + 1;
    }

    private function headerRow(Worksheet $sheet, int $row, array $headers): int
    {
        $sheet->fromArray($headers, null, 'A'.$row);
        $lastCol = Coordinate::stringFromColumnIndex(count($headers));
        $range = "A{$row}:{$lastCol}{$row}";

        $style = $sheet->getStyle($range);
        $style->getFont()->setBold(true)->getColor()->setRGB('FFFFFF');
        $style->getFill()->setFillType(Fill::FILL_SOLID)->getStartColor()->setRGB(self::HEADER_FILL);
        $style->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER)->setVertical(Alignment::VERTICAL_CENTER)->setWrapText(true);
        $sheet->getRowDimension($row)->setRowHeight(30);

        $sheet->freezePane('A'.($row + 1));
        $sheet->setAutoFilter($range);

        foreach (range(1, count($headers)) as $c) {
            $sheet->getColumnDimension(Coordinate::stringFromColumnIndex($c))->setAutoSize(true);
        }
        if ($headers[0] === 'No') {
            $sheet->getColumnDimension('A')->setAutoSize(false)->setWidth(5);
        }

        return $row;
    }

    private function bodyStyle(Worksheet $sheet, int $headerRow, int $lastRow, string $lastCol): void
    {
        if ($lastRow === $headerRow) {
            $sheet->setCellValue('A'.($headerRow + 1), 'Tidak ada lisensi yang berakhir pada periode ini.');
            $sheet->getStyle('A'.($headerRow + 1))->getFont()->setItalic(true);

            return;
        }

        $sheet->getStyle("A{$headerRow}:{$lastCol}{$lastRow}")->getBorders()->getAllBorders()
            ->setBorderStyle(Border::BORDER_THIN)->getColor()->setRGB('CBD5E1');
        $sheet->getStyle('A'.($headerRow + 1).":{$lastCol}{$lastRow}")->getAlignment()->setVertical(Alignment::VERTICAL_TOP);
    }

    private function date(?CarbonInterface $date): ?float
    {
        return $date ? ExcelDate::PHPToExcel($date->copy()->startOfDay()) : null;
    }
}
