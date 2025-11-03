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
            $limit = $request->input('limit', 5); // Số lượng thuốc top (used for non-paginated exports)
            $startDate = $request->filled('tu_ngay') ? Carbon::createFromFormat('d/m/Y', $request->tu_ngay) : Carbon::now()->startOfMonth();
            $endDate = $request->filled('den_ngay') ? Carbon::createFromFormat('d/m/Y', $request->den_ngay) : Carbon::now();
            // normalize to date strings to ensure DB comparisons work as expected
            $startDateStr = $startDate->toDateString();
            $endDateStr = $endDate->toDateString();

            // Build a subquery that returns distinct (thuoc_id, don_id, tong_cong)
            // so we can sum order totals (tong_cong) per product without double-counting
            $orderTotalsSub = DB::table('chi_tiet_don_ban_le')
                ->join('lo_thuoc', 'chi_tiet_don_ban_le.lo_id', '=', 'lo_thuoc.lo_id')
                ->join('don_ban_le', 'chi_tiet_don_ban_le.don_id', '=', 'don_ban_le.don_id')
                ->whereDate('don_ban_le.ngay_ban', '>=', $startDateStr)
                ->whereDate('don_ban_le.ngay_ban', '<=', $endDateStr)
                ->where('don_ban_le.trang_thai', 'hoan_tat')
                ->select('lo_thuoc.thuoc_id as thuoc_id', 'don_ban_le.don_id', 'don_ban_le.tong_cong')
                ->distinct();

            // Truy vấn dữ liệu doanh số - Sửa lỗi GROUP BY
            $query = Thuoc::select(
                'thuoc.thuoc_id',
                'thuoc.ma_thuoc',
                'thuoc.ten_thuoc',
                'thuoc.mo_ta',
                'thuoc.don_vi_goc',
                'thuoc.don_vi_ban',
                'thuoc.ti_le_quy_doi',
                'thuoc.trang_thai',
                'thuoc.created_at',
                'thuoc.nhom_id',
                'thuoc.kho_id'
            )
                ->selectRaw('COUNT(DISTINCT don_ban_le.don_id) as so_don')
                ->selectRaw('SUM(chi_tiet_don_ban_le.so_luong) as tong_so_luong')
                ->selectRaw('SUM(CASE WHEN chi_tiet_don_ban_le.don_vi = 1 THEN chi_tiet_don_ban_le.so_luong / COALESCE(NULLIF(thuoc.ti_le_quy_doi,0),1) ELSE chi_tiet_don_ban_le.so_luong END) as tong_so_luong_quy_doi')
                // join aggregated order totals subquery and sum order totals per product
                ->leftJoinSub($orderTotalsSub, 'ot', function($join) {
                    $join->on('ot.thuoc_id', '=', 'thuoc.thuoc_id');
                })
                ->selectRaw('SUM(ot.tong_cong) as doanh_so')
                ->leftJoin('lo_thuoc', 'thuoc.thuoc_id', '=', 'lo_thuoc.thuoc_id')
                ->leftJoin('chi_tiet_don_ban_le', 'lo_thuoc.lo_id', '=', 'chi_tiet_don_ban_le.lo_id')
                ->leftJoin('don_ban_le', 'chi_tiet_don_ban_le.don_id', '=', 'don_ban_le.don_id')
                ->whereDate('don_ban_le.ngay_ban', '>=', $startDateStr)
                ->whereDate('don_ban_le.ngay_ban', '<=', $endDateStr)
                ->where('don_ban_le.trang_thai', 'hoan_tat')
                ->groupBy(
                    'thuoc.thuoc_id',
                    'thuoc.ma_thuoc',
                    'thuoc.ten_thuoc',
                    'thuoc.mo_ta',
                    'thuoc.don_vi_goc',
                    'thuoc.don_vi_ban',
                    'thuoc.ti_le_quy_doi',
                    'thuoc.trang_thai',
                    'thuoc.created_at',
                    'thuoc.nhom_id',
                    'thuoc.kho_id'
                )
                ->orderBy('doanh_so', 'desc');

            // If a specific thuốc is selected, filter to it
            if ($request->filled('thuoc_id')) {
                $query->where('thuoc.thuoc_id', $request->thuoc_id);
            }

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

            // Use pagination for the on-screen report. If you want top-N export, exportExcel handles its own query.
            $perPage = 10;
            $thuocs = $query->paginate($perPage)->appends($request->query());

            return view('bao-cao.thuoc.index', compact('thuocs', 'startDate', 'endDate'));
        }
    }

    private function exportExcel(Request $request)
    {
        $spreadsheet = new Spreadsheet();
        $sheet = $spreadsheet->getActiveSheet();
        $loaiBaoCao = $request->input('loai_bao_cao', 'doanh_so');

        if ($loaiBaoCao == 'doanh_so') {
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

            // Tiêu đề báo cáo động theo bộ lọc
            $title = 'BÁO CÁO DOANH SỐ THUỐC';
            if ($request->filled('thuoc_id')) {
                $thuoc = \App\Models\Thuoc::find($request->thuoc_id);
                if ($thuoc) {
                    $title .= ' ' . $thuoc->ten_thuoc;
                }
            }
            $sheet->setCellValue('A5', $title);
            $sheet->mergeCells('A5:E5');
            $sheet->getStyle('A5')->getFont()->setBold(true)->setSize(16);
            $sheet->getStyle('A5')->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);

            // Thêm thông tin thời gian
            $startDate = $request->filled('tu_ngay') ? Carbon::createFromFormat('d/m/Y', $request->tu_ngay) : Carbon::now()->startOfMonth();
            $endDate = $request->filled('den_ngay') ? Carbon::createFromFormat('d/m/Y', $request->den_ngay) : Carbon::now();
            
            $sheet->setCellValue('A6', '(Từ ngày: ' . $startDate->format('d/m/Y') . ' - Đến ngày: ' . $endDate->format('d/m/Y') . ')');
            $sheet->mergeCells('A6:E6');
            $sheet->getStyle('A6')->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);

            // Headers
            $sheet->setCellValue('A7', 'STT');
            $sheet->setCellValue('B7', 'Tên sản phẩm');
            $sheet->setCellValue('C7', 'Số đơn hàng');
            $sheet->setCellValue('D7', 'Tổng số lượng');
            $sheet->setCellValue('E7', 'Doanh số');

            // Định dạng header
            $headerRange = 'A7:E7';
            $sheet->getStyle($headerRange)->getFont()->setBold(true);
            $sheet->getStyle($headerRange)->getFill()->setFillType(\PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID);
            $sheet->getStyle($headerRange)->getFill()->getStartColor()->setARGB('FFD9D9D9');

            // Truy vấn dữ liệu - Sửa lỗi GROUP BY
            // Build a subquery that returns distinct (thuoc_id, don_id, tong_cong)
            // so we can sum order totals (tong_cong) per product without double-counting
            $orderTotalsSub = DB::table('chi_tiet_don_ban_le')
                ->join('lo_thuoc', 'chi_tiet_don_ban_le.lo_id', '=', 'lo_thuoc.lo_id')
                ->join('don_ban_le', 'chi_tiet_don_ban_le.don_id', '=', 'don_ban_le.don_id')
                ->whereDate('don_ban_le.ngay_ban', '>=', $startDate->toDateString())
                ->whereDate('don_ban_le.ngay_ban', '<=', $endDate->toDateString())
                ->where('don_ban_le.trang_thai', 'hoan_tat')
                ->select('lo_thuoc.thuoc_id as thuoc_id', 'don_ban_le.don_id', 'don_ban_le.tong_cong')
                ->distinct();

            $query = Thuoc::select(
                'thuoc.thuoc_id',
                'thuoc.ma_thuoc',
                'thuoc.ten_thuoc',
                'thuoc.mo_ta',
                'thuoc.don_vi_goc',
                'thuoc.don_vi_ban',
                'thuoc.ti_le_quy_doi',
                'thuoc.trang_thai',
                'thuoc.created_at',
                'thuoc.nhom_id',
                'thuoc.kho_id'
            )
                ->selectRaw('COUNT(DISTINCT don_ban_le.don_id) as so_don')
                ->selectRaw('SUM(chi_tiet_don_ban_le.so_luong) as tong_so_luong')
                ->selectRaw('SUM(CASE WHEN chi_tiet_don_ban_le.don_vi = 1 THEN chi_tiet_don_ban_le.so_luong / COALESCE(NULLIF(thuoc.ti_le_quy_doi,0),1) ELSE chi_tiet_don_ban_le.so_luong END) as tong_so_luong_quy_doi')
                // join aggregated order totals subquery and sum order totals per product
                ->leftJoinSub($orderTotalsSub, 'ot', function($join) {
                    $join->on('ot.thuoc_id', '=', 'thuoc.thuoc_id');
                })
                ->selectRaw('SUM(ot.tong_cong) as doanh_so')
                ->leftJoin('lo_thuoc', 'thuoc.thuoc_id', '=', 'lo_thuoc.thuoc_id')
                ->leftJoin('chi_tiet_don_ban_le', 'lo_thuoc.lo_id', '=', 'chi_tiet_don_ban_le.lo_id')
                ->leftJoin('don_ban_le', 'chi_tiet_don_ban_le.don_id', '=', 'don_ban_le.don_id')
                ->whereDate('don_ban_le.ngay_ban', '>=', $startDate->toDateString())
                ->whereDate('don_ban_le.ngay_ban', '<=', $endDate->toDateString())
                ->where('don_ban_le.trang_thai', 'hoan_tat')
                ->groupBy(
                    'thuoc.thuoc_id',
                    'thuoc.ma_thuoc',
                    'thuoc.ten_thuoc',
                    'thuoc.mo_ta',
                    'thuoc.don_vi_goc',
                    'thuoc.don_vi_ban',
                    'thuoc.ti_le_quy_doi',
                    'thuoc.trang_thai',
                    'thuoc.created_at',
                    'thuoc.nhom_id',
                    'thuoc.kho_id'
                )
                ->orderBy('doanh_so', 'desc');

            // If a specific thuốc is selected for export, apply the filter to the query
            if ($request->filled('thuoc_id')) {
                $query->where('thuoc.thuoc_id', $request->thuoc_id);
            }

            $thuocs = $query->get();

            $row = 8;
            $tongDoanhSo = 0;
            $tongSoDon = 0;
            $tongSoLuongQuyDoi = 0;

            foreach ($thuocs as $index => $thuoc) {
                $sheet->setCellValue('A' . $row, $index + 1);
                $sheet->setCellValue('B' . $row, $thuoc->ten_thuoc);
                $sheet->setCellValue('C' . $row, $thuoc->so_don ?: 0);
                // show converted total quantity (in base unit)
                $val = $thuoc->tong_so_luong_quy_doi ?: 0;
                // write integer if whole, otherwise write float with up to 2 decimals
                if (floor($val) == $val) {
                    $sheet->setCellValue('D' . $row, (int) $val);
                    $sheet->getStyle('D' . $row)->getNumberFormat()->setFormatCode('#,##0');
                } else {
                    $sheet->setCellValue('D' . $row, (float) $val);
                    $sheet->getStyle('D' . $row)->getNumberFormat()->setFormatCode('#,##0.##');
                }
                // write numeric doanh_so and format as currency-like with VNĐ suffix
                $sheet->setCellValue('E' . $row, $thuoc->doanh_so ?: 0);
                $sheet->getStyle('E' . $row)->getNumberFormat()->setFormatCode('#,##0" VNĐ"');

                $tongDoanhSo += $thuoc->doanh_so ?: 0;
                $tongSoDon += $thuoc->so_don ?: 0;
                $tongSoLuongQuyDoi += $thuoc->tong_so_luong_quy_doi ?: 0;
                $row++;
            }
            // Thêm dòng tổng cộng (Số đơn hàng và tổng số lượng (quy đổi))
            $sheet->setCellValue('A' . $row, '');
            $sheet->setCellValue('B' . $row, 'TỔNG CỘNG');
            $sheet->setCellValue('C' . $row, $tongSoDon);
            // total: integer format if whole, otherwise up to 2 decimals
            if (floor($tongSoLuongQuyDoi) == $tongSoLuongQuyDoi) {
                $sheet->setCellValue('D' . $row, (int) $tongSoLuongQuyDoi);
                $sheet->getStyle('D' . $row)->getNumberFormat()->setFormatCode('#,##0');
            } else {
                $sheet->setCellValue('D' . $row, (float) $tongSoLuongQuyDoi);
                $sheet->getStyle('D' . $row)->getNumberFormat()->setFormatCode('#,##0.##');
            }
            $sheet->setCellValue('E' . $row, $tongDoanhSo);
            $sheet->getStyle('E' . $row)->getNumberFormat()->setFormatCode('#,##0" VNĐ"');
            
            // Định dạng dòng tổng cộng
            $totalRange = 'A' . $row . ':E' . $row;
            $sheet->getStyle($totalRange)->getFont()->setBold(true);
            
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
        }

        // Auto size columns
        foreach (range('A', 'E') as $col) {
            $sheet->getColumnDimension($col)->setAutoSize(true);
        }

        // Thêm border cho toàn bộ bảng dữ liệu
        $dataRange = 'A7:E' . ($row - 4);
        $sheet->getStyle($dataRange)->getBorders()->getAllBorders()->setBorderStyle(Border::BORDER_THIN);

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