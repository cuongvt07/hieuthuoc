<?php

namespace App\Http\Controllers;

use App\Models\LoThuoc;
use App\Models\Thuoc;
use App\Models\Kho;
use Illuminate\Http\Request;
use Carbon\Carbon;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use PhpOffice\PhpSpreadsheet\Style\Border;

class BaoCaoLoThuocController extends Controller
{
    public function index(Request $request)
    {
        $khos = Kho::orderBy('ten_kho')->get();
        $thuocs = Thuoc::orderBy('ten_thuoc')->get();

        if ($request->has('export') && $request->export == 'excel') {
            return $this->exportExcel($request);
        }

        $query = LoThuoc::with(['thuoc', 'kho'])
            ->where('ton_kho_hien_tai', '>', 0);

        if ($request->filled('thuoc_id')) {
            $query->where('thuoc_id', $request->thuoc_id);
        }

        if ($request->filled('kho_id')) {
            $query->where('kho_id', $request->kho_id);
        }

        if ($request->filled('trang_thai')) {
            $now = Carbon::now();
            
            switch($request->trang_thai) {
                case 'con_han':
                    $query->where('han_su_dung', '>', $now->copy()->addMonths(6));
                    break;
                case 'sap_het_han':
                    $query->where('han_su_dung', '<=', $now->copy()->addMonths(6))
                          ->where('han_su_dung', '>', $now);
                    break;
                case 'het_han':
                    $query->where('han_su_dung', '<=', $now);
                    break;
            }
        }

        $loThuocs = $query->orderBy('han_su_dung')->paginate(10);

        return view('bao-cao.lo-thuoc.index', compact('khos', 'thuocs', 'loThuocs'));
    }

    private function exportExcel(Request $request)
    {
        $query = LoThuoc::with(['thuoc', 'kho'])
            ->where('ton_kho_hien_tai', '>', 0);

        // Apply filters
        if ($request->filled('thuoc_id')) {
            $query->where('thuoc_id', $request->thuoc_id);
        }

        if ($request->filled('kho_id')) {
            $query->where('kho_id', $request->kho_id);
        }

        if ($request->filled('trang_thai')) {
            $now = Carbon::now();
            
            switch($request->trang_thai) {
                case 'con_han':
                    $query->where('han_su_dung', '>', $now->copy()->addMonths(6));
                    break;
                case 'sap_het_han':
                    $query->where('han_su_dung', '<=', $now->copy()->addMonths(6))
                          ->where('han_su_dung', '>', $now);
                    break;
                case 'het_han':
                    $query->where('han_su_dung', '<=', $now);
                    break;
            }
        }

        $loThuocs = $query->orderBy('han_su_dung')->get();

        $spreadsheet = new Spreadsheet();
        $sheet = $spreadsheet->getActiveSheet();

        // Set title
        $sheet->setCellValue('A1', 'BÁO CÁO LÔ THUỐC');
        $sheet->mergeCells('A1:F1');
        $sheet->getStyle('A1')->getFont()->setBold(true)->setSize(16);
        $sheet->getStyle('A1')->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);

        // Set filters if any
        $row = 2;
        if ($request->filled('kho_id')) {
            $kho = Kho::find($request->kho_id);
            $sheet->setCellValue('A' . $row, 'Kho: ' . $kho->ten_kho);
            $sheet->mergeCells('A' . $row . ':F' . $row);
            $row++;
        }

        if ($request->filled('thuoc_id')) {
            $thuoc = Thuoc::find($request->thuoc_id);
            $sheet->setCellValue('A' . $row, 'Thuốc: ' . $thuoc->ten_thuoc);
            $sheet->mergeCells('A' . $row . ':F' . $row);
            $row++;
        }

        // Add some spacing
        $row++;

        // Set headers
        $sheet->setCellValue('A' . $row, 'Mã lô');
        $sheet->setCellValue('B' . $row, 'Tên thuốc');
        $sheet->setCellValue('C' . $row, 'Kho');
        $sheet->setCellValue('D' . $row, 'Số lượng tồn');
        $sheet->setCellValue('E' . $row, 'Hạn sử dụng');
        $sheet->setCellValue('F' . $row, 'Trạng thái');

        // Style the header row
        $sheet->getStyle('A' . $row . ':F' . $row)->applyFromArray([
            'font' => ['bold' => true],
            'borders' => [
                'allBorders' => ['borderStyle' => Border::BORDER_THIN]
            ]
        ]);

        $row++;

        // Add data
        foreach ($loThuocs as $lo) {
            $now = Carbon::now();
            $hsd = Carbon::parse($lo->han_su_dung);
            $monthsDiff = $now->diffInMonths($hsd, false);
            
            if ($now > $hsd) {
                $trangThai = 'Hết hạn';
            } elseif ($monthsDiff <= 6) {
                $trangThai = 'Sắp hết hạn';
            } else {
                $trangThai = 'Còn hạn';
            }

            $sheet->setCellValue('A' . $row, $lo->so_lo);
            $sheet->setCellValue('B' . $row, $lo->thuoc->ten_thuoc);
            $sheet->setCellValue('C' . $row, $lo->kho->ten_kho);
            $sheet->setCellValue('D' . $row, $lo->ton_kho_hien_tai);
            $sheet->setCellValue('E' . $row, Carbon::parse($lo->han_su_dung)->format('d/m/Y'));
            $sheet->setCellValue('F' . $row, $trangThai);

            $sheet->getStyle('A' . $row . ':F' . $row)->applyFromArray([
                'borders' => [
                    'allBorders' => ['borderStyle' => Border::BORDER_THIN]
                ]
            ]);

            $row++;
        }

        // Auto size columns
        foreach (range('A', 'F') as $col) {
            $sheet->getColumnDimension($col)->setAutoSize(true);
        }

        // Create the excel file
        $writer = new Xlsx($spreadsheet);
        $filename = 'bao-cao-lo-thuoc-' . date('Y-m-d-H-i-s') . '.xlsx';

        header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
        header('Content-Disposition: attachment;filename="' . $filename . '"');
        header('Cache-Control: max-age=0');

        $writer->save('php://output');
        exit;
    }
}