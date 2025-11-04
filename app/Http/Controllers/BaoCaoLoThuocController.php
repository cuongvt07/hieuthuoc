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
            ->select('lo_thuoc.*')
            ->selectRaw('(ton_kho_hien_tai * lo_thuoc.gia_nhap_tb) as gia_tri_ton');

        if ($request->filled('thuoc_id')) {
            $query->where('thuoc_id', $request->thuoc_id);
        }

        if ($request->filled('kho_id')) {
            $query->where('kho_id', $request->kho_id);
        }

        // Filter theo ngày tạo
        if ($request->filled('tu_ngay')) {
            $tuNgay = Carbon::parse($request->tu_ngay)->startOfDay();
            $query->where('ngay_tao', '>=', $tuNgay);
        }

        if ($request->filled('den_ngay')) {
            $denNgay = Carbon::parse($request->den_ngay)->endOfDay();
            $query->where('ngay_tao', '<=', $denNgay);
        }

        if ($request->filled('trang_thai')) {
            $now = Carbon::now();

            switch($request->trang_thai) {
                case 'con_han':
                    $query->where('han_su_dung', '>', $now->copy()->addMonths(1))
                          ->whereDoesntHave('lichSuTonKho', function($q) {
                              $q->where('loai_thay_doi', 'dieu_chinh');
                          });
                    break;
                case 'sap_het_han':
                    $query->where('han_su_dung', '<=', $now->copy()->addMonths(1))
                          ->where('han_su_dung', '>', $now)
                          ->whereDoesntHave('lichSuTonKho', function($q) {
                              $q->where('loai_thay_doi', 'dieu_chinh');
                          });
                    break;
                case 'het_han_chua_huy':
                    $query->where('han_su_dung', '<=', $now)
                          ->where('ton_kho_hien_tai', '>', 0)
                          ->whereDoesntHave('lichSuTonKho', function($q) {
                              $q->where('loai_thay_doi', 'dieu_chinh');
                          });
                    break;
                case 'het_han_da_huy':
                    $query->where('han_su_dung', '<=', $now)
                          ->whereHas('lichSuTonKho', function($q) {
                              $q->where('loai_thay_doi', 'dieu_chinh');
                          });
                    break;
            }
        }

        // Paginate on-screen results and preserve current query string so filters persist across pages
        $loThuocs = $query->orderBy('han_su_dung')->paginate(10)->appends($request->query());

        return view('bao-cao.lo-thuoc.index', compact('khos', 'thuocs', 'loThuocs'));
    }

    private function exportExcel(Request $request)
    {
        // Sử dụng cùng logic filter như index()
        $query = LoThuoc::with(['thuoc', 'kho'])
            ->select('lo_thuoc.*')
            ->selectRaw('(ton_kho_hien_tai * lo_thuoc.gia_nhap_tb) as gia_tri_ton');

        // Apply filters giống hệt index()
        if ($request->filled('thuoc_id')) {
            $query->where('thuoc_id', $request->thuoc_id);
        }

        if ($request->filled('kho_id')) {
            $query->where('kho_id', $request->kho_id);
        }

        // Filter theo ngày tạo
        if ($request->filled('tu_ngay')) {
            $tuNgay = Carbon::parse($request->tu_ngay)->startOfDay();
            $query->where('ngay_tao', '>=', $tuNgay);
        }

        if ($request->filled('den_ngay')) {
            $denNgay = Carbon::parse($request->den_ngay)->endOfDay();
            $query->where('ngay_tao', '<=', $denNgay);
        }

        if ($request->filled('trang_thai')) {
            $now = Carbon::now();
            switch($request->trang_thai) {
                case 'con_han':
                    $query->where('han_su_dung', '>', $now->copy()->addMonths(1))
                          ->whereDoesntHave('lichSuTonKho', function($q) {
                              $q->where('loai_thay_doi', 'dieu_chinh');
                          });
                    break;
                case 'sap_het_han':
                    $query->where('han_su_dung', '<=', $now->copy()->addMonths(1))
                          ->where('han_su_dung', '>', $now)
                          ->whereDoesntHave('lichSuTonKho', function($q) {
                              $q->where('loai_thay_doi', 'dieu_chinh');
                          });
                    break;
                case 'het_han_chua_huy':
                    $query->where('han_su_dung', '<=', $now)
                          ->where('ton_kho_hien_tai', '>', 0)
                          ->whereDoesntHave('lichSuTonKho', function($q) {
                              $q->where('loai_thay_doi', 'dieu_chinh');
                          });
                    break;
                case 'het_han_da_huy':
                    $query->where('han_su_dung', '<=', $now)
                          ->whereHas('lichSuTonKho', function($q) {
                              $q->where('loai_thay_doi', 'dieu_chinh');
                          });
                    break;
            }
        }

        $loThuocs = $query->orderBy('han_su_dung')->get();

        $spreadsheet = new Spreadsheet();
        $sheet = $spreadsheet->getActiveSheet();

        // Thông tin nhà thuốc ở đầu file
        $sheet->setCellValue('A1', 'NHÀ THUỐC AN TÂM');
        $sheet->mergeCells('A1:I1');
        $sheet->getStyle('A1')->getFont()->setBold(true)->setSize(14);
        $sheet->getStyle('A1')->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);

        $sheet->setCellValue('A2', 'Địa chỉ: Tầng 1 Tòa G3, Tổ hợp thương mại dịch vụ ADG-Garden, phường Vĩnh Tuy, Hà Nội.');
        $sheet->mergeCells('A2:I2');
        $sheet->getStyle('A2')->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);

        $sheet->setCellValue('A3', 'Điện thoại:024 2243 0103 - Email: info@antammed.com');
        $sheet->mergeCells('A3:I3');
        $sheet->getStyle('A3')->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);

        // Cách 1 dòng
        $sheet->setCellValue('A4', '');

        // Set title
        $title = 'BÁO CÁO LÔ THUỐC';
        if ($request->filled('trang_thai')) {
            switch($request->trang_thai) {
                case 'het_han_chua_huy':
                    $title .= ' HẾT HẠN (CHƯA HỦY)';
                    break;
                case 'het_han_da_huy':
                    $title .= ' HẾT HẠN (ĐÃ HỦY)';
                    break;
                case 'sap_het_han':
                    $title .= ' SẮP HẾT HẠN (≤ 1 THÁNG)';
                    break;
                case 'con_han':
                    $title .= ' CÒN HẠN';
                    break;
            }
        }
        $sheet->setCellValue('A5', $title);
        $sheet->mergeCells('A5:I5');
        $sheet->getStyle('A5')->getFont()->setBold(true)->setSize(16);
        $sheet->getStyle('A5')->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);

        // Set filters if any
        $row = 6;
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

        // Hiển thị filter ngày trong Excel
        if ($request->filled('tu_ngay') || $request->filled('den_ngay')) {
            $dateRange = '(';
            if ($request->filled('tu_ngay')) {
                $dateRange .= 'Từ ngày ' . Carbon::parse($request->tu_ngay)->format('d/m/Y');
            }
            if ($request->filled('den_ngay')) {
                $dateRange .= ($request->filled('tu_ngay') ? ' đến ngày ' : 'Đến ngày ') . Carbon::parse($request->den_ngay)->format('d/m/Y');
            }
            $dateRange .= ')';
            $sheet->setCellValue('A' . $row, $dateRange);
            $sheet->mergeCells('A' . $row . ':I' . $row);
            $sheet->getStyle('A' . $row)->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
            $row++;
        }

        // Add some spacing
        $row++;

        // Set headers
        $sheet->setCellValue('A' . $row, 'Mã lô');
        $sheet->setCellValue('B' . $row, 'Tên sản phẩm');
        $sheet->setCellValue('C' . $row, 'Kho');
        $sheet->setCellValue('D' . $row, 'Số lượng tồn');
        $sheet->setCellValue('E' . $row, 'Giá vốn');
        $sheet->setCellValue('F' . $row, 'Thành tiền');
        $sheet->setCellValue('G' . $row, 'Hạn sử dụng');
        $sheet->setCellValue('H' . $row, 'Trạng thái');

        // Style the header row
        $sheet->getStyle('A' . $row . ':H' . $row)->applyFromArray([
            'font' => ['bold' => true],
            'borders' => [
                'allBorders' => ['borderStyle' => Border::BORDER_THIN]
            ]
        ]);

        $row++;
        $tongSoLuong = 0;
        $tongGiaTri = 0;

        // Add data
        foreach ($loThuocs as $lo) {
            $now = Carbon::now()->startOfDay();
            $hsd = Carbon::parse($lo->han_su_dung)->startOfDay();
            $daysDiff = $hsd->diffInDays($now, false);
            if ($lo->da_huy) {
                $trangThai = 'Hết hạn (đã hủy)';
            } elseif ($now >= $hsd) {
                $trangThai = 'Hết hạn (chưa hủy)';
            } elseif ($daysDiff <= 30) {
                $trangThai = 'Sắp hết hạn (còn ' . sprintf('%02d', $daysDiff) . ' ngày)';
            } else {
                $trangThai = 'Còn hạn';
            }

            $thanhTien = $lo->ton_kho_hien_tai * $lo->gia_nhap_tb;
            $tongSoLuong += $lo->ton_kho_hien_tai;
            $tongGiaTri += $thanhTien;

            $sheet->setCellValue('A' . $row, $lo->ma_lo);
            $sheet->setCellValue('B' . $row, $lo->thuoc->ten_thuoc);
            $sheet->setCellValue('C' . $row, $lo->kho->ten_kho);
            $sheet->setCellValue('D' . $row, $lo->ton_kho_hien_tai);
            $sheet->setCellValue('E' . $row, is_null($lo->gia_nhap_tb) ? 0 : (float)$lo->gia_nhap_tb);
            $sheet->getStyle('E' . $row)->getNumberFormat()->setFormatCode('#,##0');
            $sheet->setCellValue('F' . $row, (float)$thanhTien);
            $sheet->getStyle('F' . $row)->getNumberFormat()->setFormatCode('#,##0');
            $sheet->setCellValue('G' . $row, Carbon::parse($lo->han_su_dung)->format('d/m/Y'));
            $sheet->setCellValue('H' . $row, $trangThai);

            $sheet->getStyle('A' . $row . ':H' . $row)->applyFromArray([
                'borders' => [
                    'allBorders' => ['borderStyle' => Border::BORDER_THIN]
                ]
            ]);

            $row++;
        }

        // Add totals row
        $sheet->setCellValue('A' . $row, 'Tổng cộng');
        $sheet->mergeCells('A' . $row . ':C' . $row);
        $sheet->setCellValue('D' . $row, $tongSoLuong);
        $sheet->mergeCells('E' . $row . ':E' . $row);
        $sheet->setCellValue('F' . $row, \number_format($tongGiaTri, 0, ',', '.'));
        $sheet->mergeCells('G' . $row . ':H' . $row);

        // Style the totals row
        $sheet->getStyle('A' . $row . ':H' . $row)->applyFromArray([
            'font' => ['bold' => true],
            'borders' => [
                'allBorders' => ['borderStyle' => Border::BORDER_THIN]
            ],
            'fill' => [
                'fillType' => \PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID,
                'startColor' => ['rgb' => 'E6E6E6']
            ]
        ]);

        // Auto size columns
        foreach (range('A', 'H') as $col) {
            $sheet->getColumnDimension($col)->setAutoSize(true);
        }

        // Thêm phần cuối: Hà Nội, ngày ... tháng ... năm ...
        $row += 2;
        $now = Carbon::now();
        $sheet->setCellValue('F' . $row, 'Hà Nội, ngày ' . $now->day . ' tháng ' . $now->month . ' năm ' . $now->year);
        $sheet->mergeCells('F' . $row . ':H' . $row);
        $sheet->getStyle('F' . $row)->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);

        $row += 2;
        $sheet->setCellValue('F' . $row, 'Người lập');
        $sheet->mergeCells('F' . $row . ':H' . $row);
        $sheet->getStyle('F' . $row)->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);

        // Generate filename
        $filename = 'bao-cao-lo-thuoc-' . Carbon::now()->format('YmdHis') . '.xlsx';

        // Create writer
        $writer = new Xlsx($spreadsheet);

        // Output file
        header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
        header('Content-Disposition: attachment;filename="' . $filename . '"');
        header('Cache-Control: max-age=0');

        $writer->save('php://output');
        exit;
    }
}