<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Transaction;
use App\Models\Setting;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use PhpOffice\PhpSpreadsheet\Style\Border;
use PhpOffice\PhpSpreadsheet\Style\Fill;
use PhpOffice\PhpSpreadsheet\Style\Font;

class TransactionController extends Controller
{
    public function index()
    {
        $transactions = Transaction::with('user')
            ->orderBy('created_at', 'desc')
            ->paginate(10);

        $allTransactions = Transaction::with('user')->orderBy('created_at', 'desc')->get();
        $totalSales  = $allTransactions->sum('total_price');
        $totalProfit = $allTransactions->sum('profit');
        $totalCount  = $allTransactions->count();
            
        return view('admin.transactions.index', compact('transactions', 'totalSales', 'totalProfit', 'totalCount'));
    }

    public function show(Transaction $transaction)
    {
        $transaction->load(['user', 'items.product']);
        return view('admin.transactions.show', compact('transaction'));
    }

    public function exportCsv()
    {
        $settings = Setting::pluck('value', 'key')->toArray();
        $storeName = $settings['store_name'] ?? 'Kasir Zamiel';
        $storeAddress = $settings['store_address'] ?? '-';
        $storePhone = $settings['store_phone'] ?? '-';

        $transactions = Transaction::with('user')
            ->orderBy('created_at', 'desc')
            ->get();

        $spreadsheet = new Spreadsheet();
        $sheet = $spreadsheet->getActiveSheet();
        $sheet->setTitle('Laporan Transaksi');

        // ── Column widths ──────────────────────────────────────────────
        $sheet->getColumnDimension('A')->setWidth(6);
        $sheet->getColumnDimension('B')->setWidth(26);
        $sheet->getColumnDimension('C')->setWidth(20);
        $sheet->getColumnDimension('D')->setWidth(18);
        $sheet->getColumnDimension('E')->setWidth(16);
        $sheet->getColumnDimension('F')->setWidth(18);
        $sheet->getColumnDimension('G')->setWidth(16);
        $sheet->getColumnDimension('H')->setWidth(16);

        // ── HEADER BLOCK ───────────────────────────────────────────────
        // Row 1: Store name (big, merged)
        $sheet->mergeCells('A1:H1');
        $sheet->setCellValue('A1', strtoupper($storeName));
        $sheet->getStyle('A1')->applyFromArray([
            'font'      => ['bold' => true, 'size' => 16, 'color' => ['rgb' => 'FFFFFF']],
            'fill'      => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['rgb' => '1A3C5E']],
            'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER, 'vertical' => Alignment::VERTICAL_CENTER],
        ]);
        $sheet->getRowDimension(1)->setRowHeight(32);

        // Row 2: Address & phone
        $sheet->mergeCells('A2:H2');
        $sheet->setCellValue('A2', $storeAddress . '  |  Telp: ' . $storePhone);
        $sheet->getStyle('A2')->applyFromArray([
            'font'      => ['size' => 10, 'color' => ['rgb' => 'FFFFFF']],
            'fill'      => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['rgb' => '1A3C5E']],
            'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER],
        ]);
        $sheet->getRowDimension(2)->setRowHeight(18);

        // Row 3: Report title
        $sheet->mergeCells('A3:H3');
        $sheet->setCellValue('A3', 'LAPORAN PENJUALAN DAN KEUNTUNGAN');
        $sheet->getStyle('A3')->applyFromArray([
            'font'      => ['bold' => true, 'size' => 12, 'color' => ['rgb' => 'FFFFFF']],
            'fill'      => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['rgb' => '2563EB']],
            'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER, 'vertical' => Alignment::VERTICAL_CENTER],
        ]);
        $sheet->getRowDimension(3)->setRowHeight(22);

        // Row 4: Print date
        $sheet->mergeCells('A4:H4');
        $sheet->setCellValue('A4', 'Dicetak pada: ' . now()->format('d F Y, H:i') . ' WIB');
        $sheet->getStyle('A4')->applyFromArray([
            'font'      => ['italic' => true, 'size' => 9, 'color' => ['rgb' => '374151']],
            'fill'      => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['rgb' => 'EFF6FF']],
            'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER],
        ]);
        $sheet->getRowDimension(4)->setRowHeight(16);

        // Row 5: Summary stats
        $totalSales  = $transactions->sum('total_price');
        $totalProfit = $transactions->sum('profit');
        $totalCount  = $transactions->count();

        $sheet->mergeCells('A5:H5');
        $sheet->setCellValue('A5',
            'Total Transaksi: ' . $totalCount . '   |   '
            . 'Total Belanja: Rp ' . number_format($totalSales, 0, ',', '.')
            . '   |   '
            . 'Total Keuntungan: Rp ' . number_format($totalProfit, 0, ',', '.')
        );
        $sheet->getStyle('A5')->applyFromArray([
            'font'      => ['bold' => true, 'size' => 10, 'color' => ['rgb' => '1E40AF']],
            'fill'      => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['rgb' => 'DBEAFE']],
            'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER, 'vertical' => Alignment::VERTICAL_CENTER],
        ]);
        $sheet->getRowDimension(5)->setRowHeight(20);

        // Row 6: Spacer
        $sheet->getRowDimension(6)->setRowHeight(6);

        // ── TABLE HEADER (Row 7) ───────────────────────────────────────
        $headers = ['No', 'No. Invoice', 'Tanggal & Waktu', 'Kasir', 'Metode Pembayaran', 'Total Belanja', 'Diskon', 'Keuntungan'];
        $col = 'A';
        foreach ($headers as $header) {
            $sheet->setCellValue($col . '7', $header);
            $col++;
        }
        $sheet->getStyle('A7:H7')->applyFromArray([
            'font'      => ['bold' => true, 'size' => 10, 'color' => ['rgb' => 'FFFFFF']],
            'fill'      => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['rgb' => '1E40AF']],
            'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER, 'vertical' => Alignment::VERTICAL_CENTER],
            'borders'   => [
                'allBorders' => ['borderStyle' => Border::BORDER_THIN, 'color' => ['rgb' => 'BFDBFE']],
            ],
        ]);
        $sheet->getRowDimension(7)->setRowHeight(20);

        // ── DATA ROWS ──────────────────────────────────────────────────
        $row = 8;
        foreach ($transactions as $i => $tx) {
            $isEven = ($i % 2 === 0);
            $bgColor = $isEven ? 'F0F7FF' : 'FFFFFF';

            $sheet->setCellValue('A' . $row, $i + 1);
            $sheet->setCellValue('B' . $row, $tx->invoice_number);
            $sheet->setCellValue('C' . $row, $tx->created_at->format('d/m/Y H:i'));
            $sheet->setCellValue('D' . $row, $tx->user->name);
            $sheet->setCellValue('E' . $row, $tx->payment_method);
            $sheet->setCellValue('F' . $row, (float) $tx->total_price);
            $sheet->setCellValue('G' . $row, (float) $tx->discount);
            $sheet->setCellValue('H' . $row, (float) $tx->profit);

            // Number format for currency columns
            $currencyFmt = '#,##0';
            $sheet->getStyle('F' . $row)->getNumberFormat()->setFormatCode($currencyFmt);
            $sheet->getStyle('G' . $row)->getNumberFormat()->setFormatCode($currencyFmt);
            $sheet->getStyle('H' . $row)->getNumberFormat()->setFormatCode($currencyFmt);

            // Row styling
            $sheet->getStyle('A' . $row . ':H' . $row)->applyFromArray([
                'fill'    => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['rgb' => $bgColor]],
                'borders' => [
                    'allBorders' => ['borderStyle' => Border::BORDER_THIN, 'color' => ['rgb' => 'BFDBFE']],
                ],
                'alignment' => ['vertical' => Alignment::VERTICAL_CENTER],
            ]);

            // Highlight profit in green
            $sheet->getStyle('H' . $row)->getFont()->getColor()->setRGB('15803D');
            $sheet->getStyle('H' . $row)->getFont()->setBold(true);

            // Center No, Metode
            $sheet->getStyle('A' . $row)->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
            $sheet->getStyle('E' . $row)->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);

            // Right-align currency
            $sheet->getStyle('F' . $row . ':H' . $row)->getAlignment()->setHorizontal(Alignment::HORIZONTAL_RIGHT);

            $sheet->getRowDimension($row)->setRowHeight(16);
            $row++;
        }

        // ── FOOTER ROW ─────────────────────────────────────────────────
        $sheet->mergeCells('A' . $row . ':E' . $row);
        $sheet->setCellValue('A' . $row, 'TOTAL');
        $sheet->setCellValue('F' . $row, (float) $totalSales);
        $sheet->setCellValue('G' . $row, '');
        $sheet->setCellValue('H' . $row, (float) $totalProfit);
        $sheet->getStyle('F' . $row)->getNumberFormat()->setFormatCode('#,##0');
        $sheet->getStyle('H' . $row)->getNumberFormat()->setFormatCode('#,##0');
        $sheet->getStyle('A' . $row . ':H' . $row)->applyFromArray([
            'font'      => ['bold' => true, 'size' => 10, 'color' => ['rgb' => 'FFFFFF']],
            'fill'      => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['rgb' => '1E40AF']],
            'borders'   => [
                'allBorders' => ['borderStyle' => Border::BORDER_THIN, 'color' => ['rgb' => '1E40AF']],
            ],
            'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER, 'vertical' => Alignment::VERTICAL_CENTER],
        ]);
        $sheet->getStyle('F' . $row . ':H' . $row)->getAlignment()->setHorizontal(Alignment::HORIZONTAL_RIGHT);
        $sheet->getRowDimension($row)->setRowHeight(20);

        // ── FREEZE PANES & OUTPUT ──────────────────────────────────────
        $sheet->freezePane('A8');

        $filename = 'laporan-transaksi-' . date('Y-m-d') . '.xlsx';
        $writer = new Xlsx($spreadsheet);

        return response()->streamDownload(function() use ($writer) {
            $writer->save('php://output');
        }, $filename, [
            'Content-Type' => 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
        ]);
    }
}
