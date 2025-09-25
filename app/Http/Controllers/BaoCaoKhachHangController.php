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
            ->select('don_ban_le.don_id', 'don_ban_le.ngay_tao', 'don_ban_le.khach_hang_id')
            ->selectRaw('COUNT(DISTINCT don_ban_le.don_id) as so_don_hang')
            ->selectRaw('SUM(chi_tiet_don_ban_le.so_luong) as tong_so_luong')
            ->selectRaw('SUM(chi_tiet_don_ban_le.thanh_tien) as thanh_tien_don_hang')
            ->join('chi_tiet_don_ban_le', 'don_ban_le.don_id', '=', 'chi_tiet_don_ban_le.don_id')
            ->whereNotNull('khach_hang_id')
            ->groupBy('don_ban_le.don_id', 'don_ban_le.ngay_tao', 'don_ban_le.khach_hang_id');

        if ($request->filled('khach_hang_id')) {
            $query->where('khach_hang_id', $request->khach_hang_id);
        }

        if ($request->filled('tu_ngay')) {
            $query->whereDate('ngay_tao', '>=', Carbon::createFromFormat('d/m/Y', $request->tu_ngay));
        }

        if ($request->filled('den_ngay')) {
            $query->whereDate('ngay_tao', '<=', Carbon::createFromFormat('d/m/Y', $request->den_ngay));
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
            $sheet->setCellValue('A2', 'Khách hàng: ' . $khachHang->ho_ten);
            $sheet->mergeCells('A2:F2');
        }

        // Header
        $sheet->setCellValue('A4', 'STT');
        $sheet->setCellValue('B4', 'Ngày mua');
        $sheet->setCellValue('C4', 'Tên thuốc');
        $sheet->setCellValue('D4', 'Số lượng');
        $sheet->setCellValue('E4', 'Đơn giá');
        $sheet->setCellValue('F4', 'Thành tiền');

        $query = DonBanLe::with(['chiTietDonBanLe.loThuoc.thuoc'])
            ->whereNotNull('khach_hang_id');

        if ($request->filled('khach_hang_id')) {
            $query->where('khach_hang_id', $request->khach_hang_id);
        }

        if ($request->filled('tu_ngay')) {
            $query->whereDate('ngay_tao', '>=', Carbon::createFromFormat('d/m/Y', $request->tu_ngay));
        }

        if ($request->filled('den_ngay')) {
            $query->whereDate('ngay_tao', '<=', Carbon::createFromFormat('d/m/Y', $request->den_ngay));
        }

        $donHangs = $query->orderBy('ngay_tao', 'desc')->get();

        $row = 5;
        $stt = 1;
        $tongSoLuong = 0;
        $tongTien = 0;
        
        foreach ($donHangs as $donHang) {
            foreach ($donHang->chiTietDonBanLe as $chiTiet) {
                $sheet->setCellValue('A' . $row, $stt++);
                $sheet->setCellValue('B' . $row, Carbon::parse($donHang->ngay_tao)->format('d/m/Y'));
                $sheet->setCellValue('C' . $row, $chiTiet->loThuoc->thuoc->ten_thuoc);
                $sheet->setCellValue('D' . $row, $chiTiet->so_luong);
                $sheet->setCellValue('E' . $row, number_format($chiTiet->don_gia));
                $sheet->setCellValue('F' . $row, number_format($chiTiet->thanh_tien));
                
                $tongSoLuong += $chiTiet->so_luong;
                $tongTien += $chiTiet->thanh_tien;
                
                $row++;
            }
        }
        
        // Thêm dòng tổng cộng
        $sheet->setCellValue('A' . $row, 'TỔNG CỘNG');
        $sheet->mergeCells('A' . $row . ':C' . $row);
        $sheet->getStyle('A' . $row)->getAlignment()->setHorizontal(Alignment::HORIZONTAL_RIGHT);
        $sheet->setCellValue('D' . $row, $tongSoLuong);
        $sheet->setCellValue('F' . $row, number_format($tongTien));
        $sheet->getStyle('A' . $row . ':F' . $row)->getFont()->setBold(true);

        // Auto size columns
        foreach (range('A', 'F') as $col) {
            $sheet->getColumnDimension($col)->setAutoSize(true);
        }

        // Thêm dòng Người xuất ở cuối cùng
        $lastRow = $sheet->getHighestRow() + 2;
        $sheet->setCellValue('F' . $lastRow, 'Người xuất:');
        $sheet->getStyle('F' . $lastRow)->getFont()->setItalic(true);

        $writer = new Xlsx($spreadsheet);
        $filename = 'bao-cao-khach-hang-' . date('Y-m-d-H-i-s') . '.xlsx';

        header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
        header('Content-Disposition: attachment;filename="' . $filename . '"');
        header('Cache-Control: max-age=0');

        $writer->save('php://output');
        exit;
    }
}
