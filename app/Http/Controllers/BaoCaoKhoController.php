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
        
        // Lấy filter ngày
        $tuNgay = $request->filled('tu_ngay') ? Carbon::parse($request->tu_ngay)->startOfDay() : null;
        $denNgay = $request->filled('den_ngay') ? Carbon::parse($request->den_ngay)->endOfDay() : null;
        
        // Nếu chọn kho cụ thể
        if ($request->filled('kho_id')) {
            $now = Carbon::now();
            $sixMonthsLater = $now->copy()->addMonths(6);
            
            $thuocs = Thuoc::with(['loThuoc' => function($query) use ($request, $tuNgay, $denNgay) {
                $query->where('kho_id', $request->kho_id)
                    ->where('ton_kho_hien_tai', '>', 0);
                
                // Thêm filter theo ngày tạo lô thuốc
                if ($tuNgay) {
                    $query->where('ngay_tao', '>=', $tuNgay);
                }
                if ($denNgay) {
                    $query->where('ngay_tao', '<=', $denNgay);
                }
            }])
            ->select('thuoc.*')
            ->addSelect(DB::raw('(SELECT SUM(ton_kho_hien_tai) FROM lo_thuoc 
                WHERE lo_thuoc.thuoc_id = thuoc.thuoc_id 
                AND lo_thuoc.kho_id = ' . $request->kho_id . ' 
                AND lo_thuoc.ton_kho_hien_tai > 0' . 
                ($tuNgay ? ' AND lo_thuoc.ngay_tao >= "' . $tuNgay->format('Y-m-d H:i:s') . '"' : '') .
                ($denNgay ? ' AND lo_thuoc.ngay_tao <= "' . $denNgay->format('Y-m-d H:i:s') . '"' : '') . 
                ') as tong_ton_kho'))
            ->addSelect(DB::raw('(SELECT SUM(ton_kho_hien_tai * gia_nhap_tb) FROM lo_thuoc 
                WHERE lo_thuoc.thuoc_id = thuoc.thuoc_id 
                AND lo_thuoc.kho_id = ' . $request->kho_id . ' 
                AND lo_thuoc.ton_kho_hien_tai > 0' . 
                ($tuNgay ? ' AND lo_thuoc.ngay_tao >= "' . $tuNgay->format('Y-m-d H:i:s') . '"' : '') .
                ($denNgay ? ' AND lo_thuoc.ngay_tao <= "' . $denNgay->format('Y-m-d H:i:s') . '"' : '') . 
                ') as gia_tri_ton'))
            ->addSelect(DB::raw('(SELECT COUNT(*) FROM lo_thuoc 
                WHERE lo_thuoc.thuoc_id = thuoc.thuoc_id 
                AND lo_thuoc.kho_id = ' . $request->kho_id . ' 
                AND lo_thuoc.ton_kho_hien_tai > 0 
                AND lo_thuoc.han_su_dung < "' . $sixMonthsLater->format('Y-m-d') . '" 
                AND lo_thuoc.han_su_dung >= "' . $now->format('Y-m-d') . '"' . 
                ($tuNgay ? ' AND lo_thuoc.ngay_tao >= "' . $tuNgay->format('Y-m-d H:i:s') . '"' : '') .
                ($denNgay ? ' AND lo_thuoc.ngay_tao <= "' . $denNgay->format('Y-m-d H:i:s') . '"' : '') . 
                ') as sap_het_han'))
            ->addSelect(DB::raw('(SELECT COUNT(*) FROM lo_thuoc 
                WHERE lo_thuoc.thuoc_id = thuoc.thuoc_id 
                AND lo_thuoc.kho_id = ' . $request->kho_id . ' 
                AND lo_thuoc.ton_kho_hien_tai > 0 
                AND lo_thuoc.han_su_dung < "' . $now->format('Y-m-d') . '"' . 
                ($tuNgay ? ' AND lo_thuoc.ngay_tao >= "' . $tuNgay->format('Y-m-d H:i:s') . '"' : '') .
                ($denNgay ? ' AND lo_thuoc.ngay_tao <= "' . $denNgay->format('Y-m-d H:i:s') . '"' : '') . 
                ') as da_het_han'))
            ->whereExists(function($query) use ($request, $tuNgay, $denNgay) {
                $query->select(DB::raw(1))
                    ->from('lo_thuoc')
                    ->whereColumn('lo_thuoc.thuoc_id', 'thuoc.thuoc_id')
                    ->where('lo_thuoc.kho_id', $request->kho_id)
                    ->where('ton_kho_hien_tai', '>', 0);
                
                // Thêm filter ngày cho whereExists
                if ($tuNgay) {
                    $query->where('lo_thuoc.ngay_tao', '>=', $tuNgay);
                }
                if ($denNgay) {
                    $query->where('lo_thuoc.ngay_tao', '<=', $denNgay);
                }
            })
            ->orderBy('thuoc.ten_thuoc')
            ->paginate(10);
            
            return view('bao-cao.kho.chi-tiet', compact('khos', 'thuocs'));
        }
        
        // Nếu không chọn kho: tổng hợp tất cả kho
        $khoListQuery = Kho::select('kho.*')
            ->selectRaw('COUNT(DISTINCT thuoc.thuoc_id) as so_mat_hang')
            ->selectRaw('SUM(lo_thuoc.ton_kho_hien_tai) as tong_ton_kho')  
            ->selectRaw('SUM(lo_thuoc.ton_kho_hien_tai * lo_thuoc.gia_nhap_tb) as tong_gia_tri')
            ->leftJoin('lo_thuoc', function($join) use ($tuNgay, $denNgay) {
                $join->on('kho.kho_id', '=', 'lo_thuoc.kho_id');
                // Thêm filter ngày vào join
                if ($tuNgay) {
                    $join->where('lo_thuoc.ngay_tao', '>=', $tuNgay);
                }
                if ($denNgay) {
                    $join->where('lo_thuoc.ngay_tao', '<=', $denNgay);
                }
            })
            ->leftJoin('thuoc', function($join) {
                $join->on('lo_thuoc.thuoc_id', '=', 'thuoc.thuoc_id')
                    ->where('thuoc.trang_thai', 1);
            })
            ->groupBy([
                'kho.kho_id',
                'kho.ten_kho',
                'kho.dia_chi',
                'kho.ghi_chu'
            ])
            ->orderBy('kho.ten_kho');
        
        $khoList = $khoListQuery->get();
        
        return view('bao-cao.kho.tong-hop', compact('khos', 'khoList'));
    }


    private function exportExcel(Request $request)
    {
        $spreadsheet = new Spreadsheet();
        $sheet = $spreadsheet->getActiveSheet();

        // Thông tin nhà thuốc ở đầu file
        $sheet->setCellValue('A1', 'NHÀ THUỐC AN TÂM');
        $sheet->mergeCells('A1:E1');
        $sheet->getStyle('A1')->getFont()->setBold(true)->setSize(14);
        $sheet->getStyle('A1')->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);

        $sheet->setCellValue('A2', 'Địa chỉ: Tầng 1 Tòa G3, Tổ hợp thương mại dịch vụ ADG-Garden, phường Vĩnh Tuy, Hà Nội.');
        $sheet->mergeCells('A2:E2');
        $sheet->getStyle('A2')->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);

        $sheet->setCellValue('A3', 'Điện thoại:024 2243 0103 - Email: info@antammed.com');
        $sheet->mergeCells('A3:E3');
        $sheet->getStyle('A3')->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);

        // Cách 1 dòng
        $sheet->setCellValue('A4', '');

        if ($request->filled('kho_id')) {
            // Export chi tiết một kho
            $kho = Kho::find($request->kho_id);
            $sheet->setCellValue('A5', 'BÁO CÁO CHI TIẾT KHO: ' . $kho->ten_kho);
            $sheet->mergeCells('A5:E5');

            // Dòng từ ngày ... đến ngày ...
            $tuNgay = $request->filled('tu_ngay') ? Carbon::parse($request->tu_ngay)->format('d/m/Y') : '';
            $denNgay = $request->filled('den_ngay') ? Carbon::parse($request->den_ngay)->format('d/m/Y') : '';
            $dateRange = '';
            if ($tuNgay && $denNgay) {
                $dateRange = '(Từ ngày ' . $tuNgay . ' đến ngày ' . $denNgay . ')';
            } elseif ($tuNgay) {
                $dateRange = '(Từ ngày ' . $tuNgay . ')';
            } elseif ($denNgay) {
                $dateRange = '(Đến ngày ' . $denNgay . ')';
            }
            $sheet->setCellValue('A6', $dateRange);
            $sheet->mergeCells('A6:E6');
            $sheet->getStyle('A6')->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);

            // Headers
            $sheet->setCellValue('A7', 'STT');
            $sheet->setCellValue('B7', 'Tên sản phẩm');
            $sheet->setCellValue('C7', 'Đơn vị');
            $sheet->setCellValue('D7', 'Số lượng tồn');
            $sheet->setCellValue('E7', 'Giá trị tồn');

            $thuocs = Thuoc::select('thuoc.*')
                ->selectRaw('SUM(lo_thuoc.ton_kho_hien_tai) as tong_ton_kho')
                ->selectRaw('SUM(lo_thuoc.ton_kho_hien_tai * lo_thuoc.gia_nhap_tb) as gia_tri_ton')
                ->leftJoin('lo_thuoc', function($join) use ($request) {
                    $join->on('thuoc.thuoc_id', '=', 'lo_thuoc.thuoc_id')
                         ->where('lo_thuoc.kho_id', '=', $request->kho_id);
                })
                ->where('trang_thai', 1)
                ->groupBy('thuoc.thuoc_id', 'thuoc.kho_id')
                ->having('tong_ton_kho', '>', 0)
                ->orderBy('thuoc.ten_thuoc')
                ->get();

            $row = 8;
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
            $sheet->setCellValue('A5', 'BÁO CÁO TỒN KHO');
            $sheet->mergeCells('A5:E5');

            // Headers
            $sheet->setCellValue('A7', 'STT');
            $sheet->setCellValue('B7', 'Tên kho');
            $sheet->setCellValue('C7', 'Số lượng mặt hàng');
            $sheet->setCellValue('D7', 'Tổng số lượng tồn');
            $sheet->setCellValue('E7', 'Tổng giá trị tồn');

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

            $row = 8;
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
        $sheet->getStyle('A7:E7')->getFont()->setBold(true);
        $sheet->getStyle('A7:E7')->getFill()->setFillType(\PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID)->getStartColor()->setRGB('CCCCCC');

        // Auto size columns
        foreach (range('A', 'E') as $col) {
            $sheet->getColumnDimension($col)->setAutoSize(true);
        }

        // Thêm phần cuối: Hà Nội, ngày ... tháng ... năm ...
        $row += 2;
        $now = Carbon::now();
        $sheet->setCellValue('D' . $row, 'Hà Nội, ngày ' . $now->day . ' tháng ' . $now->month . ' năm ' . $now->year);
        $sheet->mergeCells('D' . $row . ':E' . $row);
        $sheet->getStyle('D' . $row)->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);

        $row += 2;
        $sheet->setCellValue('D' . $row, 'Người lập');
        $sheet->mergeCells('D' . $row . ':E' . $row);
        $sheet->getStyle('D' . $row)->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);

        $row += 1;
        $sheet->setCellValue('D' . $row, '(Ký và ghi rõ họ tên)');
        $sheet->mergeCells('D' . $row . ':E' . $row);
        $sheet->getStyle('D' . $row)->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);

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