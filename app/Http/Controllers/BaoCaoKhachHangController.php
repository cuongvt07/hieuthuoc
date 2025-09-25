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

        // Tiêu đề báo cáo
        $sheet->setCellValue('A1', 'BÁO CÁO LỊCH SỬ MUA HÀNG KHÁCH HÀNG');
        $sheet->mergeCells('A1:F1');
        $sheet->getStyle('A1')->getFont()->setBold(true)->setSize(16);
        $sheet->getStyle('A1')->getAlignment()->setHorizontal(\PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER);

        // Hiển thị tên khách hàng nếu lọc theo khach_hang_id
        if ($request->filled('khach_hang_id')) {
            $khachHang = KhachHang::find($request->khach_hang_id);
            $sheet->setCellValue('A2', 'Khách hàng: ' . ($khachHang->ho_ten ?? 'N/A'));
            $sheet->mergeCells('A2:F2');
            $sheet->getStyle('A2')->getAlignment()->setHorizontal(\PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER);
        }

        // Header
        $sheet->setCellValue('A4', 'STT');
        $sheet->setCellValue('B4', 'Khách hàng');
        $sheet->setCellValue('C4', 'Số điện thoại');
        $sheet->setCellValue('D4', 'Số lượng đơn');
        $sheet->setCellValue('E4', 'Tổng số lượng');
        $sheet->setCellValue('F4', 'Tổng chi tiêu');
        $sheet->getStyle('A4:F4')->getFont()->setBold(true);
        $sheet->getStyle('A4:F4')->getAlignment()->setHorizontal(\PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER);

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
        $row = 5;
        $stt = 1;
        $tongSoLuongDon = 0;
        $tongSoLuong = 0;
        $tongChiTieu = 0;

        foreach ($baoCaoKhachHangs as $baoCao) {
            $sheet->setCellValue('A' . $row, $stt++);
            $sheet->setCellValue('B' . $row, $baoCao->khachHang ? $baoCao->khachHang->ho_ten : 'N/A');
            $sheet->setCellValue('C' . $row, $baoCao->khachHang ? ($baoCao->khachHang->sdt ?? 'N/A') : 'N/A');
            $sheet->setCellValue('D' . $row, $baoCao->so_luong_don);
            $sheet->setCellValue('E' . $row, $baoCao->tong_so_luong);
            $sheet->setCellValue('F' . $row, number_format($baoCao->tong_chi_tieu, 0, ',', '.') . ' VNĐ');

            $tongSoLuongDon += $baoCao->so_luong_don;
            $tongSoLuong += $baoCao->tong_so_luong;
            $tongChiTieu += $baoCao->tong_chi_tieu;

            $row++;
        }

        // Thêm dòng tổng cộng
        $sheet->setCellValue('A' . $row, 'TỔNG CỘNG');
        $sheet->mergeCells('A' . $row . ':C' . $row);
        $sheet->getStyle('A' . $row)->getAlignment()->setHorizontal(\PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_RIGHT);
        $sheet->setCellValue('D' . $row, $tongSoLuongDon);
        $sheet->setCellValue('E' . $row, $tongSoLuong);
        $sheet->setCellValue('F' . $row, number_format($tongChiTieu, 0, ',', '.') . ' VNĐ');
        $sheet->getStyle('A' . $row . ':F' . $row)->getFont()->setBold(true);

        // Auto size columns
        foreach (range('A', 'F') as $col) {
            $sheet->getColumnDimension($col)->setAutoSize(true);
        }

        $writer = new \PhpOffice\PhpSpreadsheet\Writer\Xlsx($spreadsheet);
        $filename = 'bao-cao-khach-hang-' . date('Y-m-d-H-i-s') . '.xlsx';

        header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
        header('Content-Disposition: attachment;filename="' . $filename . '"');
        header('Cache-Control: max-age=0');

        $writer->save('php://output');
        exit;
    }
}