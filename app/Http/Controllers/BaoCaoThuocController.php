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

        // Kiểm tra loại báo cáo
        $loaiBaoCao = $request->input('loai_bao_cao', 'doanh_so');

        if ($loaiBaoCao == 'doanh_so') {
            // Báo cáo theo doanh số thuốc
            $limit = $request->input('limit', 5); // Số lượng thuốc top
            $startDate = $request->filled('tu_ngay') ? Carbon::createFromFormat('d/m/Y', $request->tu_ngay) : Carbon::now()->startOfMonth();
            $endDate = $request->filled('den_ngay') ? Carbon::createFromFormat('d/m/Y', $request->den_ngay) : Carbon::now();

            // Truy vấn dữ liệu doanh số
            $query = Thuoc::select('thuoc.*')
                ->selectRaw('COUNT(DISTINCT don_ban_le.don_id) as so_don')
                ->selectRaw('SUM(chi_tiet_don_ban_le.so_luong) as tong_so_luong')
                ->selectRaw('SUM(chi_tiet_don_ban_le.thanh_tien) as doanh_so')
                ->leftJoin('lo_thuoc', 'thuoc.thuoc_id', '=', 'lo_thuoc.thuoc_id')
                ->leftJoin('chi_tiet_don_ban_le', 'lo_thuoc.lo_id', '=', 'chi_tiet_don_ban_le.lo_id')
                ->leftJoin('don_ban_le', 'chi_tiet_don_ban_le.don_id', '=', 'don_ban_le.don_id')
                ->whereDate('don_ban_le.ngay_ban', '>=', $startDate)
                ->whereDate('don_ban_le.ngay_ban', '<=', $endDate)
                ->groupBy('thuoc.thuoc_id')
                ->orderBy('doanh_so', 'desc')
                ->limit($limit);

            // Nếu có thêm bộ lọc theo trạng thái, áp dụng vào query
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

            return view('bao-cao.thuoc.index', compact('thuocs', 'startDate', 'endDate'));
        }
    }

    private function exportExcel(Request $request)
    {
        $spreadsheet = new Spreadsheet();
        $sheet = $spreadsheet->getActiveSheet();
        $loaiBaoCao = $request->input('loai_bao_cao', 'doanh_so');

        if ($loaiBaoCao == 'doanh_so') {
            // Export báo cáo theo doanh số
            $sheet->setCellValue('A1', 'BÁO CÁO DOANH SỐ THUỐC');
            $sheet->mergeCells('A1:E1');
            
            // Headers
            $sheet->setCellValue('A3', 'STT');
            $sheet->setCellValue('B3', 'Tên thuốc');
            $sheet->setCellValue('C3', 'Số đơn hàng');
            $sheet->setCellValue('D3', 'Tổng số lượng');
            $sheet->setCellValue('E3', 'Doanh số');

            // Lọc theo khoảng thời gian
            $startDate = $request->filled('tu_ngay') ? Carbon::createFromFormat('d/m/Y', $request->tu_ngay) : Carbon::now()->startOfMonth();
            $endDate = $request->filled('den_ngay') ? Carbon::createFromFormat('d/m/Y', $request->den_ngay) : Carbon::now();

            $thuocs = Thuoc::select('thuoc.*')
                ->selectRaw('COUNT(DISTINCT don_ban_le.don_id) as so_don')
                ->selectRaw('SUM(chi_tiet_don_ban_le.so_luong) as tong_so_luong')
                ->selectRaw('SUM(chi_tiet_don_ban_le.thanh_tien) as doanh_so')
                ->leftJoin('lo_thuoc', 'thuoc.thuoc_id', '=', 'lo_thuoc.thuoc_id')
                ->leftJoin('chi_tiet_don_ban_le', 'lo_thuoc.lo_id', '=', 'chi_tiet_don_ban_le.lo_id')
                ->leftJoin('don_ban_le', 'chi_tiet_don_ban_le.don_id', '=', 'don_ban_le.don_id')
                ->whereDate('don_ban_le.ngay_ban', '>=', $startDate)
                ->whereDate('don_ban_le.ngay_ban', '<=', $endDate)
                ->groupBy('thuoc.thuoc_id')
                ->orderBy('doanh_so', 'desc')
                ->get();

            $row = 4;
            foreach ($thuocs as $index => $thuoc) {
                $sheet->setCellValue('A' . $row, $index + 1);
                $sheet->setCellValue('B' . $row, $thuoc->ten_thuoc);
                $sheet->setCellValue('C' . $row, $thuoc->so_don);
                $sheet->setCellValue('D' . $row, $thuoc->tong_so_luong);
                $sheet->setCellValue('E' . $row, $thuoc->doanh_so);
                $row++;
            }
        }

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
        $filename = 'bao-cao-doanh-so-thuoc-' . date('Y-m-d-H-i-s') . '.xlsx';

        header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
        header('Content-Disposition: attachment;filename="' . $filename . '"');
        header('Cache-Control: max-age=0');

        $writer->save('php://output');
        exit;
    }
}