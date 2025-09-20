<?php

namespace App\Http\Controllers;

use App\Models\DonBanLe;
use App\Models\ChiTietDonBanLe;
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
        $khachHangs = KhachHang::orderBy('ho_ten')->get();

        if ($request->has('export') && $request->export == 'excel') {
            return $this->exportExcel($request);
        }

        $query = DonBanLe::with(['chiTietDonBanLe.loThuoc.thuoc', 'khachHang'])
            ->whereNotNull('khach_hang_id');

        if ($request->filled('khach_hang_id')) {
            $query->where('khach_hang_id', $request->khach_hang_id);
        }

        if ($request->filled('tu_ngay')) {
            try {
                $tuNgay = Carbon::createFromFormat('Y-m-d', $request->tu_ngay, 'Asia/Ho_Chi_Minh')
                    ->startOfDay();
                $query->whereDate('ngay_tao', '>=', $tuNgay);
            } catch (\Exception $e) {
                \Log::warning('Invalid date format for tu_ngay: ' . $request->tu_ngay . ' - ' . $e->getMessage());
            }
        }

        if ($request->filled('den_ngay')) {
            try {
                $denNgay = Carbon::createFromFormat('Y-m-d', $request->den_ngay, 'Asia/Ho_Chi_Minh')
                    ->endOfDay();
                $query->whereDate('ngay_tao', '<=', $denNgay);
            } catch (\Exception $e) {
                \Log::warning('Invalid date format for den_ngay: ' . $request->den_ngay . ' - ' . $e->getMessage());
            }
        }

        $donHangs = $query->orderBy('ngay_tao', 'desc')->paginate(10);

        return view('bao-cao.khach-hang.index', compact('khachHangs', 'donHangs'));
    }

    private function exportExcel(Request $request)
    {
        $spreadsheet = new Spreadsheet();
        $sheet = $spreadsheet->getActiveSheet();

        // Tiêu đề báo cáo
        $sheet->setCellValue('A1', 'BÁO CÁO LỊCH SỬ MUA HÀNG KHÁCH HÀNG');
        $sheet->mergeCells('A1:F1');
        $sheet->getStyle('A1')->getFont()->setBold(true)->setSize(16);
        $sheet->getStyle('A1')->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);

        if ($request->filled('khach_hang_id')) {
            $khachHang = KhachHang::find($request->khach_hang_id);
            $sheet->setCellValue('A2', 'Khách hàng: ' . ($khachHang->ho_ten ?? 'N/A'));
            $sheet->mergeCells('A2:F2');
        }

        // Header (đã điều chỉnh)
        $sheet->setCellValue('A4', 'STT');
        $sheet->setCellValue('B4', 'Khách hàng');
        $sheet->setCellValue('C4', 'Mã đơn');
        $sheet->setCellValue('D4', 'Số lượng');
        $sheet->setCellValue('E4', 'Thành tiền');
        $sheet->setCellValue('F4', 'Ngày tạo đơn');

        $query = DonBanLe::with(['chiTietDonBanLe.loThuoc.thuoc', 'khachHang'])
            ->whereNotNull('khach_hang_id');

        if ($request->filled('khach_hang_id')) {
            $query->where('khach_hang_id', $request->khach_hang_id);
        }

        if ($request->filled('tu_ngay')) {
            try {
                $tuNgay = Carbon::createFromFormat('Y-m-d', $request->tu_ngay, 'Asia/Ho_Chi_Minh')
                    ->startOfDay();
                $query->whereDate('ngay_tao', '>=', $tuNgay);
            } catch (\Exception $e) {
                \Log::warning('Invalid date format for tu_ngay: ' . $request->tu_ngay . ' - ' . $e->getMessage());
            }
        }

        if ($request->filled('den_ngay')) {
            try {
                $denNgay = Carbon::createFromFormat('Y-m-d', $request->den_ngay, 'Asia/Ho_Chi_Minh')
                    ->endOfDay();
                $query->whereDate('ngay_tao', '<=', $denNgay);
            } catch (\Exception $e) {
                \Log::warning('Invalid date format for den_ngay: ' . $request->den_ngay . ' - ' . $e->getMessage());
            }
        }

        $donHangs = $query->orderBy('ngay_tao', 'desc')->get();

        $row = 5;
        $stt = 1;
        $tongSoLuong = 0;
        $tongTien = 0;

        foreach ($donHangs as $donHang) {
            foreach ($donHang->chiTietDonBanLe as $chiTiet) {
                $sheet->setCellValue('A' . $row, $stt++); // STT
                $sheet->setCellValue('B' . $row, $donHang->khachHang->ho_ten ?? 'N/A'); // Khách hàng
                $sheet->setCellValue('C' . $row, $donHang->ma_don ?? 'N/A'); // Mã đơn
                $sheet->setCellValue('D' . $row, $chiTiet->so_luong ?? 0); // Số lượng
                $sheet->setCellValue('E' . $row, number_format($chiTiet->thanh_tien ?? 0)); // Thành tiền
                $sheet->setCellValue('F' . $row, Carbon::parse($donHang->ngay_tao)->format('d/m/Y H:i')); // Ngày tạo đơn

                $tongSoLuong += $chiTiet->so_luong ?? 0;
                $tongTien += $chiTiet->thanh_tien ?? 0;

                $row++;
            }
        }

        // Thêm dòng tổng cộng
        $sheet->setCellValue('A' . $row, 'TỔNG CỘNG');
        $sheet->mergeCells('A' . $row . ':C' . $row);
        $sheet->getStyle('A' . $row)->getAlignment()->setHorizontal(Alignment::HORIZONTAL_RIGHT);
        $sheet->setCellValue('D' . $row, $tongSoLuong);
        $sheet->setCellValue('E' . $row, number_format($tongTien));
        $sheet->getStyle('A' . $row . ':E' . $row)->getFont()->setBold(true);

        // Auto size columns
        foreach (range('A', 'F') as $col) {
            $sheet->getColumnDimension($col)->setAutoSize(true);
        }

        $writer = new Xlsx($spreadsheet);
        $filename = 'bao-cao-khach-hang-' . date('Y-m-d-H-i-s') . '.xlsx';

        header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
        header('Content-Disposition: attachment;filename="' . $filename . '"');
        header('Cache-Control: max-age=0');

        $writer->save('php://output');
        exit;
    }
}