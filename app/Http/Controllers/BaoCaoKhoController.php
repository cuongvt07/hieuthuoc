<?php

namespace App\Http\Controllers;

use App\Models\Kho;
use App\Models\Thuoc;
use App\Models\LoThuoc;
use Illuminate\Http\Request;
use Carbon\Carbon;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use PhpOffice\PhpSpreadsheet\Style\Border;
use Illuminate\Support\Facades\DB;
use function number_format;

class BaoCaoKhoController extends Controller
{
public function index(Request $request)
{
    $khos = Kho::orderBy('ten_kho')->get();

    if ($request->has('export') && $request->export == 'excel') {
        return $this->exportExcel($request);
    }

    // Nếu chọn kho cụ thể
    if ($request->filled('kho_id')) {
        $now = Carbon::now();
        $sixMonthsLater = $now->copy()->addMonths(6);

        $thuocs = Thuoc::with(['loThuoc' => function($query) use ($request) {
            $query->where('kho_id', $request->kho_id)
                  ->where('ton_kho_hien_tai', '>', 0);
        }])
        ->select('thuoc.*')
        ->addSelect(DB::raw('(SELECT SUM(ton_kho_hien_tai) FROM lo_thuoc 
            WHERE lo_thuoc.thuoc_id = thuoc.thuoc_id 
            AND lo_thuoc.kho_id = ' . $request->kho_id . '
            AND lo_thuoc.ton_kho_hien_tai > 0) as tong_ton_kho'))
        ->addSelect(DB::raw('(SELECT SUM(ton_kho_hien_tai * gia_nhap_tb) FROM lo_thuoc 
            WHERE lo_thuoc.thuoc_id = thuoc.thuoc_id 
            AND lo_thuoc.kho_id = ' . $request->kho_id . '
            AND lo_thuoc.ton_kho_hien_tai > 0) as gia_tri_ton'))
        ->addSelect(DB::raw('(SELECT COUNT(*) FROM lo_thuoc 
            WHERE lo_thuoc.thuoc_id = thuoc.thuoc_id 
            AND lo_thuoc.kho_id = ' . $request->kho_id . '
            AND lo_thuoc.ton_kho_hien_tai > 0
            AND lo_thuoc.han_su_dung < "' . $sixMonthsLater->format('Y-m-d') . '"
            AND lo_thuoc.han_su_dung >= "' . $now->format('Y-m-d') . '") as sap_het_han'))
        ->addSelect(DB::raw('(SELECT COUNT(*) FROM lo_thuoc 
            WHERE lo_thuoc.thuoc_id = thuoc.thuoc_id 
            AND lo_thuoc.kho_id = ' . $request->kho_id . '
            AND lo_thuoc.ton_kho_hien_tai > 0
            AND lo_thuoc.han_su_dung < "' . $now->format('Y-m-d') . '") as da_het_han'))
        ->whereExists(function($query) use ($request) {
            $query->select(DB::raw(1))
                  ->from('lo_thuoc')
                  ->whereColumn('lo_thuoc.thuoc_id', 'thuoc.thuoc_id')
                  ->where('lo_thuoc.kho_id', $request->kho_id)
                  ->where('ton_kho_hien_tai', '>', 0);
        })
        ->orderBy('thuoc.ten_thuoc')
        ->paginate(10);

        return view('bao-cao.kho.chi-tiet', compact('khos', 'thuocs'));
    }

    // Nếu không chọn kho: tổng hợp tất cả kho
    $khoList = Kho::select('kho.*')
        ->selectRaw('COUNT(DISTINCT thuoc.thuoc_id) as so_mat_hang')
        ->selectRaw('SUM(lo_thuoc.ton_kho_hien_tai) as tong_ton_kho')
        ->selectRaw('SUM(lo_thuoc.ton_kho_hien_tai * lo_thuoc.gia_nhap_tb) as tong_gia_tri')
        ->leftJoin('lo_thuoc', 'kho.kho_id', '=', 'lo_thuoc.kho_id')
        ->leftJoin('thuoc', function($join) {
            $join->on('lo_thuoc.thuoc_id', '=', 'thuoc.thuoc_id')
                 ->where('thuoc.trang_thai', 1);
        })
        ->groupBy('kho.kho_id')
        ->orderBy('kho.ten_kho')
        ->get();

    return view('bao-cao.kho.tong-hop', compact('khos', 'khoList'));
}


    private function exportExcel(Request $request)
    {
        $spreadsheet = new Spreadsheet();
        $sheet = $spreadsheet->getActiveSheet();

        if ($request->filled('kho_id')) {
            // Export chi tiết một kho
            $kho = Kho::find($request->kho_id);
            
            $sheet->setCellValue('A1', 'BÁO CÁO CHI TIẾT KHO: ' . $kho->ten_kho);
            $sheet->mergeCells('A1:E1');
            
            // Headers
            $sheet->setCellValue('A3', 'STT');
            $sheet->setCellValue('B3', 'Tên thuốc');
            $sheet->setCellValue('C3', 'Đơn vị');
            $sheet->setCellValue('D3', 'Số lượng tồn');
            $sheet->setCellValue('E3', 'Giá trị tồn');

            $thuocs = Thuoc::select('thuoc.*')
                ->selectRaw('SUM(lo_thuoc.ton_kho_hien_tai) as tong_ton_kho')
                ->selectRaw('SUM(lo_thuoc.ton_kho_hien_tai * lo_thuoc.gia_nhap_tb) as gia_tri_ton')
                ->leftJoin('lo_thuoc', function($join) use ($request) {
                    $join->on('thuoc.thuoc_id', '=', 'lo_thuoc.thuoc_id')
                         ->where('lo_thuoc.kho_id', '=', $request->kho_id);
                })
                ->where('trang_thai', 1)
                ->groupBy('thuoc.thuoc_id')
                ->having('tong_ton_kho', '>', 0)
                ->orderBy('thuoc.ten_thuoc')
                ->get();

            $row = 4;
            $tongSoLuong = 0;
            $tongGiaTri = 0;
            
            foreach ($thuocs as $index => $thuoc) {
                $sheet->setCellValue('A' . $row, $index + 1);
                $sheet->setCellValue('B' . $row, $thuoc->ten_thuoc);
                $sheet->setCellValue('C' . $row, $thuoc->don_vi_goc);
                $sheet->setCellValue('D' . $row, $thuoc->tong_ton_kho);
                $sheet->setCellValue('E' . $row, number_format($thuoc->gia_tri_ton, 0, ',', '.'));
                
                $tongSoLuong += $thuoc->tong_ton_kho;
                $tongGiaTri += $thuoc->gia_tri_ton;
                
                $row++;
            }

            // Thêm dòng tổng
            $sheet->setCellValue('A' . $row, 'TỔNG CỘNG');
            $sheet->mergeCells('A' . $row . ':C' . $row);
            $sheet->setCellValue('D' . $row, $tongSoLuong);
            $sheet->setCellValue('E' . $row, number_format($tongGiaTri, 0, ',', '.'));
            $sheet->getStyle('A' . $row . ':E' . $row)->getFont()->setBold(true);

        } else {
            // Export tổng hợp tất cả kho
            $sheet->setCellValue('A1', 'BÁO CÁO TỔNG HỢP KHO');
            $sheet->mergeCells('A1:E1');
            
            // Headers
            $sheet->setCellValue('A3', 'STT');
            $sheet->setCellValue('B3', 'Tên kho');
            $sheet->setCellValue('C3', 'Số lượng mặt hàng');
            $sheet->setCellValue('D3', 'Tổng số lượng tồn');
            $sheet->setCellValue('E3', 'Tổng giá trị tồn');

            $khos = Kho::select('kho.*')
                ->selectRaw('COUNT(DISTINCT thuoc.thuoc_id) as so_mat_hang')
                ->selectRaw('SUM(lo_thuoc.ton_kho_hien_tai) as tong_ton_kho')
                ->selectRaw('SUM(lo_thuoc.ton_kho_hien_tai * lo_thuoc.gia_nhap_tb) as tong_gia_tri')
                ->leftJoin('lo_thuoc', 'kho.kho_id', '=', 'lo_thuoc.kho_id')
                ->leftJoin('thuoc', 'lo_thuoc.thuoc_id', '=', 'thuoc.thuoc_id')
                ->where('thuoc.trang_thai', 1)
                ->groupBy('kho.kho_id')
                ->orderBy('kho.ten_kho')
                ->get();

            $row = 4;
            $tongMatHang = 0;
            $tongSoLuong = 0;
            $tongGiaTri = 0;
            
            foreach ($khos as $index => $kho) {
                $sheet->setCellValue('A' . $row, $index + 1);
                $sheet->setCellValue('B' . $row, $kho->ten_kho);
                $sheet->setCellValue('C' . $row, $kho->so_mat_hang);
                $sheet->setCellValue('D' . $row, $kho->tong_ton_kho);
                $sheet->setCellValue('E' . $row, number_format($kho->tong_gia_tri, 0, ',', '.'));
                
                $tongMatHang += $kho->so_mat_hang;
                $tongSoLuong += $kho->tong_ton_kho;
                $tongGiaTri += $kho->tong_gia_tri;
                
                $row++;
            }

            // Thêm dòng tổng
            $sheet->setCellValue('A' . $row, 'TỔNG CỘNG');
            $sheet->mergeCells('A' . $row . ':B' . $row);
            $sheet->setCellValue('C' . $row, $tongMatHang);
            $sheet->setCellValue('D' . $row, $tongSoLuong);
            $sheet->setCellValue('E' . $row, number_format($tongGiaTri, 0, ',', '.'));
            $sheet->getStyle('A' . $row . ':E' . $row)->getFont()->setBold(true);
        }

        // Style chung
        $sheet->getStyle('A1')->getFont()->setBold(true)->setSize(16);
        $sheet->getStyle('A1')->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
        
        $sheet->getStyle('A3:E3')->getFont()->setBold(true);
        $sheet->getStyle('A3:E3')->getFill()->setFillType(\PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID)->getStartColor()->setRGB('CCCCCC');
        
        // Auto size columns
        foreach (range('A', 'E') as $col) {
            $sheet->getColumnDimension($col)->setAutoSize(true);
        }

        // Thêm dòng Người xuất ở cuối cùng
        $lastRow = $sheet->getHighestRow() + 2;
        $sheet->setCellValue('E' . $lastRow, 'Người xuất:');
        $sheet->getStyle('E' . $lastRow)->getFont()->setItalic(true);

        // Create the excel file
        $writer = new Xlsx($spreadsheet);
        $filename = 'bao-cao-kho-' . date('Y-m-d-H-i-s') . '.xlsx';

        header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
        header('Content-Disposition: attachment;filename="' . $filename . '"');
        header('Cache-Control: max-age=0');

        $writer->save('php://output');
        exit;
    }
}