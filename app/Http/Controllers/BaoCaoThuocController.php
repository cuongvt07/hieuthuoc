<?php

namespace App\Http\Controllers;

use App\Models\Thuoc;
use App\Models\ChiTietDonBanLe;
use App\Models\LoThuoc;
use Illuminate\Http\Request;
use Carbon\Carbon;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use PhpOffice\PhpSpreadsheet\Style\Border;
use Illuminate\Support\Facades\DB;

class BaoCaoThuocController extends Controller
{
    public function index(Request $request)
    {
        if ($request->has('export') && $request->export == 'excel') {
            return $this->exportExcel($request);
        }

        $loaiBaoCao = $request->input('loai_bao_cao', 'trang_thai');
        
        if ($loaiBaoCao == 'trang_thai') {
            // Báo cáo theo trạng thái HSD
            $query = Thuoc::with(['loThuocs' => function($q) {
                $q->where('ton_kho_hien_tai', '>', 0);
            }])
            ->where('trang_thai', 1)
            ->select('thuoc.*')
            ->selectRaw('
                SUM(CASE 
                    WHEN lo_thuoc.han_su_dung <= NOW() THEN lo_thuoc.ton_kho_hien_tai 
                    ELSE 0 
                END) as sl_het_han,
                SUM(CASE 
                    WHEN lo_thuoc.han_su_dung > NOW() AND lo_thuoc.han_su_dung <= DATE_ADD(NOW(), INTERVAL 6 MONTH) THEN lo_thuoc.ton_kho_hien_tai 
                    ELSE 0 
                END) as sl_sap_het_han,
                SUM(CASE 
                    WHEN lo_thuoc.han_su_dung > DATE_ADD(NOW(), INTERVAL 6 MONTH) THEN lo_thuoc.ton_kho_hien_tai 
                    ELSE 0 
                END) as sl_con_han,
                SUM(lo_thuoc.ton_kho_hien_tai) as tong_ton_kho
            ')
            ->leftJoin('lo_thuoc', 'thuoc.thuoc_id', '=', 'lo_thuoc.thuoc_id')
            ->groupBy('thuoc.thuoc_id');

            // Lọc theo trạng thái HSD nếu có
            if ($request->filled('trang_thai')) {
                switch ($request->trang_thai) {
                    case 'het_han':
                        $query->havingRaw('sl_het_han > 0');
                        break;
                    case 'sap_het_han':
                        $query->havingRaw('sl_sap_het_han > 0');
                        break;
                    case 'con_han':
                        $query->havingRaw('sl_con_han > 0');
                        break;
                }
            }

            $thuocs = $query->paginate(10);
            
            return view('bao-cao.thuoc.trang-thai', compact('thuocs'));

        } else {
            // Báo cáo top bán chạy/bán ế
            $limit = $request->input('limit', 5);
            $sort = $request->input('sort', 'ban_chay');
            $startDate = $request->filled('tu_ngay') ? Carbon::createFromFormat('d/m/Y', $request->tu_ngay) : Carbon::now()->startOfMonth();
            $endDate = $request->filled('den_ngay') ? Carbon::createFromFormat('d/m/Y', $request->den_ngay) : Carbon::now();

            $query = Thuoc::select('thuoc.*')
                ->selectRaw('COUNT(DISTINCT don_ban_le.don_id) as so_don')
                ->selectRaw('SUM(chi_tiet_don_ban_le.so_luong) as tong_so_luong')
                ->selectRaw('SUM(chi_tiet_don_ban_le.thanh_tien) as doanh_so')
                ->leftJoin('lo_thuoc', 'thuoc.thuoc_id', '=', 'lo_thuoc.thuoc_id')
                ->leftJoin('chi_tiet_don_ban_le', 'lo_thuoc.lo_id', '=', 'chi_tiet_don_ban_le.lo_id')
                ->leftJoin('don_ban_le', 'chi_tiet_don_ban_le.don_id', '=', 'don_ban_le.don_id')
                ->whereDate('don_ban_le.created_at', '>=', $startDate)
                ->whereDate('don_ban_le.created_at', '<=', $endDate)
                ->groupBy('thuoc.thuoc_id')
                ->orderBy('doanh_so', $sort === 'ban_chay' ? 'desc' : 'asc')
                ->limit($limit);

            $thuocs = $query->get();
            
            return view('bao-cao.thuoc.ban-chay', compact('thuocs', 'startDate', 'endDate'));
        }
    }

    private function exportExcel(Request $request)
    {
        $spreadsheet = new Spreadsheet();
        $sheet = $spreadsheet->getActiveSheet();
        $loaiBaoCao = $request->input('loai_bao_cao', 'trang_thai');

        if ($loaiBaoCao == 'trang_thai') {
            // Export báo cáo theo trạng thái HSD
            $sheet->setCellValue('A1', 'BÁO CÁO TRẠNG THÁI THUỐC THEO HẠN SỬ DỤNG');
            $sheet->mergeCells('A1:F1');
            
            // Headers
            $sheet->setCellValue('A3', 'STT');
            $sheet->setCellValue('B3', 'Tên thuốc');
            $sheet->setCellValue('C3', 'Tổng tồn kho');
            $sheet->setCellValue('D3', 'SL Hết hạn');
            $sheet->setCellValue('E3', 'SL Sắp hết hạn');
            $sheet->setCellValue('F3', 'SL Còn hạn');

            // Get data
            $query = Thuoc::with(['loThuocs'])
                ->where('trang_thai', 1)
                ->select('thuoc.*')
                ->selectRaw('
                    SUM(CASE 
                        WHEN lo_thuoc.han_su_dung <= NOW() THEN lo_thuoc.ton_kho_hien_tai 
                        ELSE 0 
                    END) as sl_het_han,
                    SUM(CASE 
                        WHEN lo_thuoc.han_su_dung > NOW() AND lo_thuoc.han_su_dung <= DATE_ADD(NOW(), INTERVAL 6 MONTH) THEN lo_thuoc.ton_kho_hien_tai 
                        ELSE 0 
                    END) as sl_sap_het_han,
                    SUM(CASE 
                        WHEN lo_thuoc.han_su_dung > DATE_ADD(NOW(), INTERVAL 6 MONTH) THEN lo_thuoc.ton_kho_hien_tai 
                        ELSE 0 
                    END) as sl_con_han,
                    SUM(lo_thuoc.ton_kho_hien_tai) as tong_ton_kho
                ')
                ->leftJoin('lo_thuoc', 'thuoc.thuoc_id', '=', 'lo_thuoc.thuoc_id')
                ->groupBy('thuoc.thuoc_id');

            // Apply filters
            if ($request->filled('trang_thai')) {
                switch ($request->trang_thai) {
                    case 'het_han':
                        $query->havingRaw('sl_het_han > 0');
                        break;
                    case 'sap_het_han':
                        $query->havingRaw('sl_sap_het_han > 0');
                        break;
                    case 'con_han':
                        $query->havingRaw('sl_con_han > 0');
                        break;
                }
            }

            $thuocs = $query->get();
            
            $row = 4;
            foreach ($thuocs as $index => $thuoc) {
                $sheet->setCellValue('A' . $row, $index + 1);
                $sheet->setCellValue('B' . $row, $thuoc->ten_thuoc);
                $sheet->setCellValue('C' . $row, $thuoc->tong_ton_kho);
                $sheet->setCellValue('D' . $row, $thuoc->sl_het_han);
                $sheet->setCellValue('E' . $row, $thuoc->sl_sap_het_han);
                $sheet->setCellValue('F' . $row, $thuoc->sl_con_han);
                $row++;
            }
        } else {
            // Export báo cáo top bán chạy/bán ế
            $limit = $request->input('limit', 5);
            $sort = $request->input('sort', 'ban_chay');
            $startDate = $request->filled('tu_ngay') ? Carbon::createFromFormat('d/m/Y', $request->tu_ngay) : Carbon::now()->startOfMonth();
            $endDate = $request->filled('den_ngay') ? Carbon::createFromFormat('d/m/Y', $request->den_ngay) : Carbon::now();

            $title = $sort === 'ban_chay' ? 'BÁO CÁO TOP ' . $limit . ' THUỐC BÁN CHẠY NHẤT' : 'BÁO CÁO TOP ' . $limit . ' THUỐC BÁN CHẬM NHẤT';
            $sheet->setCellValue('A1', $title);
            $sheet->mergeCells('A1:E1');
            
            $sheet->setCellValue('A2', 'Từ ngày: ' . $startDate->format('d/m/Y') . ' đến ngày: ' . $endDate->format('d/m/Y'));
            $sheet->mergeCells('A2:E2');

            // Headers
            $sheet->setCellValue('A4', 'STT');
            $sheet->setCellValue('B4', 'Tên thuốc');
            $sheet->setCellValue('C4', 'Số đơn hàng');
            $sheet->setCellValue('D4', 'Tổng số lượng');
            $sheet->setCellValue('E4', 'Doanh số');

            $thuocs = Thuoc::select('thuoc.*')
                ->selectRaw('COUNT(DISTINCT don_ban_le.don_id) as so_don')
                ->selectRaw('SUM(chi_tiet_don_ban_le.so_luong) as tong_so_luong')
                ->selectRaw('SUM(chi_tiet_don_ban_le.thanh_tien) as doanh_so')
                ->leftJoin('lo_thuoc', 'thuoc.thuoc_id', '=', 'lo_thuoc.thuoc_id')
                ->leftJoin('chi_tiet_don_ban_le', 'lo_thuoc.lo_id', '=', 'chi_tiet_don_ban_le.lo_id')
                ->leftJoin('don_ban_le', 'chi_tiet_don_ban_le.don_id', '=', 'don_ban_le.don_id')
                ->whereDate('don_ban_le.created_at', '>=', $startDate)
                ->whereDate('don_ban_le.created_at', '<=', $endDate)
                ->groupBy('thuoc.thuoc_id')
                ->orderBy('doanh_so', $sort === 'ban_chay' ? 'desc' : 'asc')
                ->limit($limit)
                ->get();

            $row = 5;
            foreach ($thuocs as $index => $thuoc) {
                $sheet->setCellValue('A' . $row, $index + 1);
                $sheet->setCellValue('B' . $row, $thuoc->ten_thuoc);
                $sheet->setCellValue('C' . $row, $thuoc->so_don);
                $sheet->setCellValue('D' . $row, $thuoc->tong_so_luong);
                $sheet->setCellValue('E' . $row, number_format($thuoc->doanh_so, 0, ',', '.'));
                $row++;
            }
        }

        // Auto size columns
        foreach (range('A', 'F') as $col) {
            $sheet->getColumnDimension($col)->setAutoSize(true);
        }

        // Create the excel file
        $writer = new Xlsx($spreadsheet);
        $filename = 'bao-cao-thuoc-' . date('Y-m-d-H-i-s') . '.xlsx';

        header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
        header('Content-Disposition: attachment;filename="' . $filename . '"');
        header('Cache-Control: max-age=0');

        $writer->save('php://output');
        exit;
    }
}