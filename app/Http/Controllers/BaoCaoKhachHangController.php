<?php

namespace App\Http\Controllers;

use App\Models\DonBanLe;
use App\Models\KhachHang;
use Illuminate\Http\Request;
use Carbon\Carbon;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use PhpOffice\PhpSpreadsheet\Style\Alignment;

class BaoCaoKhachHangController extends Controller
{
    public function index(Request $request)
    {
        // Lấy danh sách khách hàng để hiển thị trong dropdown lọc
        $khachHangs = KhachHang::orderBy('ho_ten')->get();

        // Xử lý xuất Excel
        if ($request->has('export') && $request->export == 'excel') {
            return $this->exportExcel($request);
        }

        // Query tổng hợp theo khách hàng, chỉ lấy đơn hoàn tất
        $query = DonBanLe::selectRaw('
                don_ban_le.khach_hang_id,
                COUNT(DISTINCT don_ban_le.don_id) as so_luong_don,
                (
                    SELECT SUM(tong_cong)
                    FROM don_ban_le sub_don
                    WHERE sub_don.khach_hang_id = don_ban_le.khach_hang_id
                        AND sub_don.trang_thai = "hoan_tat"
                ) as tong_chi_tieu,
                COALESCE(SUM(chi_tiet_don_ban_le.so_luong), 0) as tong_so_luong
            ')
            ->leftJoin('chi_tiet_don_ban_le', 'don_ban_le.don_id', '=', 'chi_tiet_don_ban_le.don_id')
            ->with(['khachHang'])
            ->whereNotNull('don_ban_le.khach_hang_id')
            ->where('don_ban_le.trang_thai', 'hoan_tat')
            ->groupBy('don_ban_le.khach_hang_id');

        // Lọc theo khach_hang_id
        if ($request->filled('khach_hang_id')) {
            $query->where('don_ban_le.khach_hang_id', $request->khach_hang_id);
        }

        // Lọc theo khoảng thời gian
        if ($request->filled('tu_ngay')) {
            try {
                $tuNgay = Carbon::createFromFormat('Y-m-d', $request->tu_ngay, 'Asia/Ho_Chi_Minh')
                    ->startOfDay();
                $query->whereDate('don_ban_le.ngay_ban', '>=', $tuNgay);
            } catch (\Exception $e) {
                \Log::warning('Invalid date format for tu_ngay: ' . $request->tu_ngay . ' - ' . $e->getMessage());
            }
        }

        if ($request->filled('den_ngay')) {
            try {
                $denNgay = Carbon::createFromFormat('Y-m-d', $request->den_ngay, 'Asia/Ho_Chi_Minh')
                    ->endOfDay();
                $query->whereDate('don_ban_le.ngay_ban', '<=', $denNgay);
            } catch (\Exception $e) {
                \Log::warning('Invalid date format for den_ngay: ' . $request->den_ngay . ' - ' . $e->getMessage());
            }
        }

        // Lấy dữ liệu tổng hợp và phân trang
        $baoCaoKhachHangs = $query->orderBy('tong_chi_tieu', 'desc')->paginate(10);
        return view('bao-cao.khach-hang.index', compact('khachHangs', 'baoCaoKhachHangs'));
    }

    private function exportExcel(Request $request)
    {
        $spreadsheet = new Spreadsheet();
        $sheet = $spreadsheet->getActiveSheet();

        // Thông tin nhà thuốc ở đầu file
        $sheet->setCellValue('A1', 'NHÀ THUỐC AN TÂM');
        $sheet->mergeCells('A1:F1');
        $sheet->getStyle('A1')->getFont()->setBold(true)->setSize(14);
        $sheet->getStyle('A1')->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);

        $sheet->setCellValue('A2', 'Địa chỉ: Tầng 1 Tòa G3, Tổ hợp thương mại dịch vụ ADG-Garden, phường Vĩnh Tuy, Hà Nội.');
        $sheet->mergeCells('A2:F2');
        $sheet->getStyle('A2')->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);

        $sheet->setCellValue('A3', 'Điện thoại:024 2243 0103 - Email: info@antammed.com');
        $sheet->mergeCells('A3:F3');
        $sheet->getStyle('A3')->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);

        // Cách 1 dòng
        $sheet->setCellValue('A4', '');

        // Tiêu đề báo cáo
        $sheet->setCellValue('A5', 'BÁO CÁO DOANH THU KHÁCH HÀNG');
        $sheet->mergeCells('A5:F5');
        $sheet->getStyle('A5')->getFont()->setBold(true)->setSize(16);
        $sheet->getStyle('A5')->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);

        // Thêm dòng từ ngày ... đến ngày ... ngay dưới tiêu đề
        $rowHeader = 6;
        $tuNgayStr = '';
        $denNgayStr = '';
        if ($request->filled('tu_ngay')) {
            $tuNgayStr = Carbon::parse($request->tu_ngay)->format('d/m/Y');
        }
        if ($request->filled('den_ngay')) {
            $denNgayStr = Carbon::parse($request->den_ngay)->format('d/m/Y');
        }
        $dateRangeText = '';
        if ($tuNgayStr && $denNgayStr) {
            $dateRangeText = '(Từ ngày ' . $tuNgayStr . ' đến ngày ' . $denNgayStr . ')';
        } elseif ($tuNgayStr) {
            $dateRangeText = '(Từ ngày ' . $tuNgayStr . ')';
        } elseif ($denNgayStr) {
            $dateRangeText = '(Đến ngày ' . $denNgayStr . ')';
        }
        if ($dateRangeText) {
            $sheet->setCellValue('A' . $rowHeader, $dateRangeText);
            $sheet->mergeCells('A' . $rowHeader . ':F' . $rowHeader);
            $sheet->getStyle('A' . $rowHeader)->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
            $rowHeader++;
        }

        // Thêm dòng khách hàng nếu có lọc
        if ($request->filled('khach_hang_id')) {
            $khachHang = KhachHang::find($request->khach_hang_id);
            $sheet->setCellValue('A' . $rowHeader, 'Khách hàng: ' . ($khachHang->ho_ten ?? 'N/A'));
            $sheet->mergeCells('A' . $rowHeader . ':F' . $rowHeader);
            $sheet->getStyle('A' . $rowHeader)->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
            $rowHeader++;
        }

        // Header
        $sheet->setCellValue('A' . $rowHeader, 'STT');
        $sheet->setCellValue('B' . $rowHeader, 'Khách hàng');
        $sheet->setCellValue('C' . $rowHeader, 'Số điện thoại');
        $sheet->setCellValue('D' . $rowHeader, 'Số lượng đơn');
        $sheet->setCellValue('E' . $rowHeader, 'Doanh thu');
        $sheet->getStyle('A' . $rowHeader . ':E' . $rowHeader)->getFont()->setBold(true);
        $sheet->getStyle('A' . $rowHeader . ':E' . $rowHeader)->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);

        // Query tổng hợp theo khách hàng, chỉ lấy đơn hoàn tất
        $query = DonBanLe::selectRaw('
                don_ban_le.khach_hang_id,
                COUNT(DISTINCT don_ban_le.don_id) as so_luong_don,
                (
                    SELECT SUM(tong_cong)
                    FROM don_ban_le sub_don
                    WHERE sub_don.khach_hang_id = don_ban_le.khach_hang_id
                        AND sub_don.trang_thai = "hoan_tat"
                ) as tong_chi_tieu,
                COALESCE(SUM(chi_tiet_don_ban_le.so_luong), 0) as tong_so_luong
            ')
            ->leftJoin('chi_tiet_don_ban_le', 'don_ban_le.don_id', '=', 'chi_tiet_don_ban_le.don_id')
            ->with(['khachHang'])
            ->whereNotNull('don_ban_le.khach_hang_id')
            ->where('don_ban_le.trang_thai', 'hoan_tat')
            ->groupBy('don_ban_le.khach_hang_id');

        // Áp dụng bộ lọc
        if ($request->filled('khach_hang_id')) {
            $query->where('don_ban_le.khach_hang_id', $request->khach_hang_id);
        }

        if ($request->filled('tu_ngay')) {
            try {
                $tuNgay = Carbon::createFromFormat('Y-m-d', $request->tu_ngay, 'Asia/Ho_Chi_Minh')
                    ->startOfDay();
                $query->whereDate('don_ban_le.ngay_ban', '>=', $tuNgay);
                // Áp dụng bộ lọc cho subquery
                $query->whereRaw('EXISTS (
                    SELECT 1 FROM don_ban_le sub_don
                    WHERE sub_don.khach_hang_id = don_ban_le.khach_hang_id
                        AND sub_don.trang_thai = "hoan_tat"
                        AND sub_don.ngay_ban >= ?
                )', [$tuNgay]);
            } catch (\Exception $e) {
                \Log::warning('Invalid date format for tu_ngay: ' . $request->tu_ngay . ' - ' . $e->getMessage());
            }
        }

        if ($request->filled('den_ngay')) {
            try {
                $denNgay = Carbon::createFromFormat('Y-m-d', $request->den_ngay, 'Asia/Ho_Chi_Minh')
                    ->endOfDay();
                $query->whereDate('don_ban_le.ngay_ban', '<=', $denNgay);
                // Áp dụng bộ lọc cho subquery
                $query->whereRaw('EXISTS (
                    SELECT 1 FROM don_ban_le sub_don
                    WHERE sub_don.khach_hang_id = don_ban_le.khach_hang_id
                        AND sub_don.trang_thai = "hoan_tat"
                        AND sub_don.ngay_ban <= ?
                )', [$denNgay]);
            } catch (\Exception $e) {
                \Log::warning('Invalid date format for den_ngay: ' . $request->den_ngay . ' - ' . $e->getMessage());
            }
        }

        $baoCaoKhachHangs = $query->orderBy('tong_chi_tieu', 'desc')->get();

        // Điền dữ liệu
        $row = $rowHeader + 1;
        $stt = 1;
        $tongSoLuongDon = 0;
        $tongDoanhThu = 0;

        foreach ($baoCaoKhachHangs as $baoCao) {
            $sheet->setCellValue('A' . $row, $stt++);
            $sheet->setCellValue('B' . $row, $baoCao->khachHang ? $baoCao->khachHang->ho_ten : 'N/A');
            $sheet->setCellValue('C' . $row, $baoCao->khachHang ? ($baoCao->khachHang->sdt ?? 'N/A') : 'N/A');
            $sheet->setCellValue('D' . $row, $baoCao->so_luong_don);
            $sheet->setCellValue('E' . $row, number_format($baoCao->tong_chi_tieu, 0, ',', '.') . ' VNĐ');

            $tongSoLuongDon += $baoCao->so_luong_don;
            $tongDoanhThu += $baoCao->tong_chi_tieu;
            $row++;
        }

        // Thêm dòng tổng cộng
        $sheet->setCellValue('A' . $row, 'TỔNG CỘNG');
        $sheet->mergeCells('A' . $row . ':C' . $row);
        $sheet->getStyle('A' . $row)->getAlignment()->setHorizontal(Alignment::HORIZONTAL_RIGHT);
        $sheet->setCellValue('D' . $row, $tongSoLuongDon);
        $sheet->setCellValue('E' . $row, number_format($tongDoanhThu, 0, ',', '.') . ' VNĐ');
        $sheet->getStyle('A' . $row . ':E' . $row)->getFont()->setBold(true);

        // Auto size columns
        foreach (range('A', 'E') as $col) {
            $sheet->getColumnDimension($col)->setAutoSize(true);
        }

        // Thêm phần cuối: Hà Nội, ngày ... tháng ... năm ...
        $row += 2;
        $now = Carbon::now();
        $sheet->setCellValue('E' . $row, 'Hà Nội, ngày ' . $now->day . ' tháng ' . $now->month . ' năm ' . $now->year);
        $sheet->mergeCells('E' . $row . ':F' . $row);
        $sheet->getStyle('E' . $row)->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);

        $row += 2;
        $sheet->setCellValue('E' . $row, 'Người lập');
        $sheet->mergeCells('E' . $row . ':F' . $row);
        $sheet->getStyle('E' . $row)->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);

        $row += 1;
        $sheet->setCellValue('E' . $row, '(Ký và ghi rõ họ tên)');
        $sheet->mergeCells('E' . $row . ':F' . $row);
        $sheet->getStyle('E' . $row)->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);

        $writer = new Xlsx($spreadsheet);
        $filename = 'bao-cao-khach-hang-' . date('Y-m-d-H-i-s') . '.xlsx';

        header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
        header('Content-Disposition: attachment;filename="' . $filename . '"');
        header('Cache-Control: max-age=0');

        $writer->save('php://output');
        exit;
    }
}