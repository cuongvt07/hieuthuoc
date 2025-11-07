<?php

namespace App\Http\Controllers;

use App\Models\Kho;
use App\Models\LichSuTonKho;
use App\Models\LoThuoc;
use App\Models\Thuoc;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use PhpOffice\PhpSpreadsheet\Style\Border;
use PhpOffice\PhpSpreadsheet\Style\Fill;

class BaoCaoTonKhoController extends Controller
{
    /**
     * Format số thành chuỗi có định dạng tiền tệ
     */
    private function formatNumber($number)
    {
        if (is_null($number)) {
            return '0';
        }
        $formatted = (string)((float)$number);
        if ($number > 0) {
            $parts = explode('.', $formatted);
            $integerPart = $parts[0];
            $decimalPart = isset($parts[1]) ? $parts[1] : '';

            // Thêm dấu phân cách hàng nghìn
            $integerPart = implode('.', str_split(strrev($integerPart), 3));
            $integerPart = strrev($integerPart);

            return $integerPart . ($decimalPart ? ',' . $decimalPart : '');
        }
        return $formatted;
    }

    /**
     * Format tiền (không có phần thập phân)
     */
    private function formatMoney($amount)
    {
        if (is_null($amount)) {
            return '0';
        }
        return number_format((float)$amount, 0, ',', '.');
    }

    /**
     * Hiển thị trang báo cáo tồn kho
     */
    public function index(Request $request)
    {
        $khos = Kho::orderBy('ten_kho')->get();
        $thuocs = Thuoc::orderBy('ten_thuoc')->get();
        $khachHangs = \App\Models\KhachHang::orderBy('ho_ten')->get();

        if ($request->has('export') && $request->export == 'excel') {
            return $this->exportExcel($request);
        }

        try {
        // For export we need full dataset (no pagination)
        $data = $this->getData($request, false);

            return view('bao-cao.ton-kho.index', [
                'khos' => $khos,
                'thuocs' => $thuocs,
                'khachHangs' => $khachHangs,
                'data' => $data,
                'filters' => $request->all()
            ]);
        } catch (\Exception $e) {
            \Log::error('Lỗi báo cáo tồn kho: ' . $e->getMessage());
            return view('bao-cao.ton-kho.index', [
                'khos' => $khos,
                'thuocs' => $thuocs,
                'khachHangs' => $khachHangs,
                'data' => collect(),
                'filters' => $request->all(),
                'error' => 'Có lỗi xảy ra khi tạo báo cáo. Vui lòng thử lại sau.'
            ]);
        }
    }

    /**
     * Lấy dữ liệu tồn kho từ bảng lịch sử tồn kho
     */
    private function getData(Request $request, $paginate = true)
    {
        // Xác định loại báo cáo, mặc định là theo lô
        $loaiBaoCao = $request->input('loai_bao_cao', 'lo');
        
        // Lấy các bản ghi lịch sử tồn kho mới nhất cho mỗi lô thuốc
        $latestHistorySubquery = LichSuTonKho::select(
                'lo_id',
                DB::raw('MAX(created_at) as latest_date')
            )
            ->groupBy('lo_id');
        
        // Truy vấn cơ bản
        $query = LichSuTonKho::join('lo_thuoc', 'lich_su_ton_kho.lo_id', '=', 'lo_thuoc.lo_id')
            ->joinSub($latestHistorySubquery, 'latest_history', function ($join) {
                $join->on('lich_su_ton_kho.lo_id', '=', 'latest_history.lo_id')
                    ->on('lich_su_ton_kho.created_at', '=', 'latest_history.latest_date');
            })
            ->join('thuoc', 'lo_thuoc.thuoc_id', '=', 'thuoc.thuoc_id')
            ->join('kho', 'lo_thuoc.kho_id', '=', 'kho.kho_id')
            ->leftJoin('gia_thuoc', function($join) {
                $join->on('thuoc.thuoc_id', '=', 'gia_thuoc.thuoc_id')
                    ->whereRaw('gia_thuoc.gia_id = (SELECT MAX(g2.gia_id) FROM gia_thuoc g2 WHERE g2.thuoc_id = thuoc.thuoc_id)');
            });
            
        // Tùy theo loại báo cáo, chọn các trường cần thiết
        switch ($loaiBaoCao) {
            case 'thuoc':
                $query->select(
                    DB::raw('thuoc.thuoc_id'),
                    DB::raw('thuoc.ma_thuoc'),
                    DB::raw('thuoc.ten_thuoc'),
                    DB::raw('thuoc.don_vi_goc as don_vi_tinh'),
                    DB::raw('SUM(lich_su_ton_kho.ton_kho_moi) as ton_kho_moi'),
                    DB::raw('AVG(gia_thuoc.gia_ban) as don_gia')
                )
                ->groupBy('thuoc.thuoc_id', 'thuoc.ma_thuoc', 'thuoc.ten_thuoc', 'thuoc.don_vi_goc');
                break;
                
            case 'kho':
                $query->select(
                    DB::raw('kho.kho_id'),
                    DB::raw('kho.ten_kho'),
                    DB::raw('COUNT(DISTINCT thuoc.thuoc_id) as so_loai_thuoc'),
                    DB::raw('SUM(lich_su_ton_kho.ton_kho_moi) as ton_kho_moi'),
                    DB::raw('SUM(lich_su_ton_kho.ton_kho_moi * IFNULL(gia_thuoc.gia_ban, 0)) as gia_tri_ton')
                )
                ->groupBy('kho.kho_id', 'kho.ten_kho');
                break;
                
            case 'khach_hang':
                $query->leftJoin('don_ban_le', 'lich_su_ton_kho.don_ban_le_id', '=', 'don_ban_le.don_id')
                      ->leftJoin('khach_hang', 'don_ban_le.khach_hang_id', '=', 'khach_hang.khach_hang_id')
                      ->select(
                          DB::raw('khach_hang.khach_hang_id'),
                          DB::raw('khach_hang.ho_ten'),
                          DB::raw('COUNT(DISTINCT don_ban_le.don_id) as so_don'),
                          DB::raw('SUM(CASE WHEN lich_su_ton_kho.loai_thay_doi = "ban" THEN ABS(lich_su_ton_kho.so_luong_thay_doi) ELSE 0 END) as so_luong_mua'),
                          DB::raw('SUM(CASE WHEN lich_su_ton_kho.loai_thay_doi = "ban" THEN ABS(lich_su_ton_kho.so_luong_thay_doi * IFNULL(gia_thuoc.gia_ban, 0)) ELSE 0 END) as gia_tri_mua')
                      )
                      ->whereNotNull('khach_hang.khach_hang_id')
                      ->groupBy('khach_hang.khach_hang_id', 'khach_hang.ho_ten');
                break;
                
            default: // 'lo' - báo cáo theo lô thuốc (mặc định)
                $query->select(
                    'lich_su_ton_kho.*',
                    'lo_thuoc.*',
                    'thuoc.ma_thuoc',
                    'thuoc.ten_thuoc',
                    'thuoc.don_vi_goc as don_vi_tinh',
                    'kho.ten_kho',
                    'gia_thuoc.gia_ban as don_gia'
                );
                break;
        }

        // Áp dụng các bộ lọc chung
        if ($request->filled('kho_id')) {
            $query->where('lo_thuoc.kho_id', $request->kho_id);
        }

        if ($request->filled('thuoc_id')) {
            $query->where('thuoc.thuoc_id', $request->thuoc_id);
        }

        if ($request->filled('khach_hang_id')) {
            $query->whereExists(function($q) use ($request) {
                $q->select(DB::raw(1))
                  ->from('don_ban_le')
                  ->whereColumn('don_ban_le.don_id', 'lich_su_ton_kho.don_ban_le_id')
                  ->where('don_ban_le.khach_hang_id', $request->khach_hang_id);
            });
        }

        if ($request->filled('ngay_bao_cao')) {
            $ngayBaoCao = Carbon::createFromFormat('d/m/Y', $request->ngay_bao_cao)->endOfDay();
            $query->where('lich_su_ton_kho.created_at', '<=', $ngayBaoCao);
        }

        if ($request->has('con_ton') && $request->con_ton == 1) {
            // Chỉ áp dụng lọc tồn kho dương cho báo cáo theo lô hoặc theo thuốc
            if (in_array($loaiBaoCao, ['lo', 'thuoc'])) {
                $query->where('ton_kho_moi', '>', 0);
            }
        }
        
        // Sắp xếp kết quả dựa trên loại báo cáo
        switch ($loaiBaoCao) {
            case 'thuoc':
                $query->orderBy('thuoc.ten_thuoc', 'asc');
                break;
                
            case 'kho':
                $query->orderBy('kho.ten_kho', 'asc');
                break;
                
            case 'khach_hang':
                $query->orderBy('khach_hang.ho_ten', 'asc');
                break;
                
            default: // 'lo'
                $query->orderBy('lo_thuoc.han_su_dung', 'asc');
                break;
        }

        if ($paginate) {
            // Paginate on-screen results and preserve query string filters
            return $query->paginate(10)->appends($request->query());
        }

        // For exports or when full dataset is needed
        return $query->get();
    }

    /**
     * Xuất báo cáo tồn kho ra Excel
     */
    private function exportExcel(Request $request)
    {
    // For export we need the full dataset; exportExcel will call getData(..., false)
    $data = $this->getData($request);
        $loaiBaoCao = $request->input('loai_bao_cao', 'lo');

        $spreadsheet = new Spreadsheet();
        $sheet = $spreadsheet->getActiveSheet();

        // Tiêu đề báo cáo tùy theo loại báo cáo
        $tieudeBaoCao = 'BÁO CÁO TỒN KHO';
        switch($loaiBaoCao) {
            case 'thuoc':
                $tieudeBaoCao = 'BÁO CÁO TỒN KHO THEO THUỐC';
                break;
            case 'kho':
                $tieudeBaoCao = 'BÁO CÁO TỒN KHO THEO KHO';
                break;
            case 'khach_hang':
                $tieudeBaoCao = 'BÁO CÁO THEO KHÁCH HÀNG';
                break;
            default:
                $tieudeBaoCao = 'BÁO CÁO TỒN KHO THEO LÔ';
                break;
        }
        
        $sheet->setCellValue('A1', $tieudeBaoCao);
        $sheet->mergeCells('A1:F1');
        $sheet->getStyle('A1')->getFont()->setBold(true)->setSize(16);
        $sheet->getStyle('A1')->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);

        $ngayBaoCao = $request->filled('ngay_bao_cao')
            ? Carbon::createFromFormat('d/m/Y', $request->ngay_bao_cao)->format('d/m/Y')
            : Carbon::now()->format('d/m/Y');
        $dateRange = '';
        if ($request->filled('tu_ngay') && $request->filled('den_ngay')) {
            $dateRange = '(Từ ngày ' . Carbon::createFromFormat('d/m/Y', $request->tu_ngay)->format('d/m/Y') . ' đến ngày ' . Carbon::createFromFormat('d/m/Y', $request->den_ngay)->format('d/m/Y') . ')';
        } elseif ($request->filled('tu_ngay')) {
            $dateRange = '(Từ ngày ' . Carbon::createFromFormat('d/m/Y', $request->tu_ngay)->format('d/m/Y') . ')';
        } elseif ($request->filled('den_ngay')) {
            $dateRange = '(Đến ngày ' . Carbon::createFromFormat('d/m/Y', $request->den_ngay)->format('d/m/Y') . ')';
        } else {
            $dateRange = '(Ngày: ' . $ngayBaoCao . ')';
        }
        $sheet->setCellValue('A2', $dateRange);
        $sheet->mergeCells('A2:F2');
        $sheet->getStyle('A2')->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);

        if ($request->filled('kho_id')) {
            $kho = Kho::find($request->kho_id);
            if ($kho) {
                $sheet->setCellValue('A3', 'Kho: ' . $kho->ten_kho);
                $sheet->mergeCells('A3:F3');
                $sheet->getStyle('A3')->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
            }
        }

        $row = $request->filled('kho_id') ? 5 : 4;
        
        // Cài đặt header style chung
        $headerStyle = [
            'font' => ['bold' => true],
            'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER],
            'borders' => ['allBorders' => ['borderStyle' => Border::BORDER_THIN]],
            'fill' => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['rgb' => 'EEEEEE']],
        ];
        
        $totalValue = 0;
        $totalQuantity = 0;
        
        // Thiết lập tiêu đề cột tùy theo loại báo cáo
        switch($loaiBaoCao) {
            case 'thuoc':
                $sheet->setCellValue('A'.$row, 'Mã thuốc');
                $sheet->setCellValue('B'.$row, 'Tên sản phẩm');
                $sheet->setCellValue('C'.$row, 'Đvt');
                $sheet->setCellValue('D'.$row, 'Tồn kho');
                $sheet->setCellValue('E'.$row, 'Đơn giá TB');
                $sheet->setCellValue('F'.$row, 'Giá trị');
                $sheet->getStyle('A'.$row.':F'.$row)->applyFromArray($headerStyle);
                break;
                
            case 'kho':
                $sheet->setCellValue('A'.$row, 'Mã kho');
                $sheet->setCellValue('B'.$row, 'Tên kho');
                $sheet->setCellValue('C'.$row, 'Số loại thuốc');
                $sheet->setCellValue('D'.$row, 'Tổng SL tồn kho');
                $sheet->setCellValue('E'.$row, 'Giá trị tồn');
                $sheet->getStyle('A'.$row.':E'.$row)->applyFromArray($headerStyle);
                break;
                
            case 'khach_hang':
                $sheet->setCellValue('A'.$row, 'Mã KH');
                $sheet->setCellValue('B'.$row, 'Tên khách hàng');
                $sheet->setCellValue('C'.$row, 'Số đơn hàng');
                $sheet->setCellValue('D'.$row, 'Tổng SL mua');
                $sheet->setCellValue('E'.$row, 'Giá trị mua');
                $sheet->getStyle('A'.$row.':E'.$row)->applyFromArray($headerStyle);
                break;
                
            default: // 'lo' - báo cáo theo lô thuốc (mặc định)
                $sheet->setCellValue('A'.$row, 'Mã thuốc');
                $sheet->setCellValue('B'.$row, 'Tên mặt hàng');
                $sheet->setCellValue('C'.$row, 'Đvt');
                $sheet->setCellValue('D'.$row, 'Kho');
                $sheet->setCellValue('E'.$row, 'Lô');
                $sheet->setCellValue('F'.$row, 'Tồn kho');
                $sheet->setCellValue('G'.$row, 'Đơn giá');
                $sheet->setCellValue('H'.$row, 'Giá trị');
                $sheet->getStyle('A'.$row.':H'.$row)->applyFromArray($headerStyle);
                break;
        }
        
        $row++;

        // Xử lý dữ liệu theo loại báo cáo
        switch($loaiBaoCao) {
            case 'thuoc':
                foreach ($data as $item) {
                    $tonKho = $item->ton_kho_moi ?? 0;
                    $giaBan = $item->don_gia ?? 0;
                    $giaTriTon = $tonKho * $giaBan;
                    $totalValue += $giaTriTon;
                    $totalQuantity += $tonKho;
                    
                    $sheet->setCellValue('A'.$row, $item->ma_thuoc ?? '');
                    $sheet->setCellValue('B'.$row, $item->ten_thuoc ?? '');
                    $sheet->setCellValue('C'.$row, $item->don_vi_tinh ?? '');
                    $sheet->setCellValue('D'.$row, $tonKho);
                    $sheet->setCellValue('E'.$row, $this->formatMoney($giaBan));
                    $sheet->setCellValue('F'.$row, $this->formatMoney($giaTriTon));
                    
                    // Thêm style cho hàng dữ liệu
                    $sheet->getStyle('A'.$row.':F'.$row)->applyFromArray([
                        'borders' => [
                            'allBorders' => ['borderStyle' => Border::BORDER_THIN],
                        ],
                    ]);
                    $sheet->getStyle('D'.$row.':F'.$row)->getAlignment()->setHorizontal(Alignment::HORIZONTAL_RIGHT);
                    $row++;
                }
                break;
                
            case 'kho':
                foreach ($data as $item) {
                    $tonKho = $item->ton_kho_moi ?? 0;
                    $giaTriTon = $item->gia_tri_ton ?? 0;
                    $totalValue += $giaTriTon;
                    $totalQuantity += $tonKho;
                    
                    $sheet->setCellValue('A'.$row, $item->ma_kho ?? '');
                    $sheet->setCellValue('B'.$row, $item->ten_kho ?? '');
                    $sheet->setCellValue('C'.$row, $item->so_loai_thuoc ?? 0);
                    $sheet->setCellValue('D'.$row, $tonKho);
                    $sheet->setCellValue('E'.$row, $this->formatMoney($giaTriTon));
                    
                    $sheet->getStyle('A'.$row.':E'.$row)->applyFromArray([
                        'borders' => ['allBorders' => ['borderStyle' => Border::BORDER_THIN]],
                    ]);
                    $sheet->getStyle('C'.$row.':E'.$row)->getAlignment()->setHorizontal(Alignment::HORIZONTAL_RIGHT);
                    $row++;
                }
                break;
                
            case 'khach_hang':
                foreach ($data as $item) {
                    $soLuongMua = $item->so_luong_mua ?? 0;
                    $giaTriMua = $item->gia_tri_mua ?? 0;
                    $totalValue += $giaTriMua;
                    $totalQuantity += $soLuongMua;
                    
                    $sheet->setCellValue('A'.$row, $item->ma_khach_hang ?? '');
                    $sheet->setCellValue('B'.$row, $item->ho_ten ?? '');
                    $sheet->setCellValue('C'.$row, $item->so_don ?? 0);
                    $sheet->setCellValue('D'.$row, $soLuongMua);
                    $sheet->setCellValue('E'.$row, $this->formatMoney($giaTriMua));
                    
                    $sheet->getStyle('A'.$row.':E'.$row)->applyFromArray([
                        'borders' => ['allBorders' => ['borderStyle' => Border::BORDER_THIN]],
                    ]);
                    $sheet->getStyle('C'.$row.':E'.$row)->getAlignment()->setHorizontal(Alignment::HORIZONTAL_RIGHT);
                    $row++;
                }
                break;
                
            default: // 'lo'
                // Nhóm dữ liệu theo lô thuốc
                $groupedData = $data->groupBy(function($item) {
                    return $item->thuoc_id . '_' . $item->lo_id;
                });
                
                foreach ($groupedData as $group) {
                    $item = $group->first();
                    
                    $tonKho = $item->ton_kho_moi ?? 0;
                    $giaBan = $item->don_gia ?? 0;
                    $giaTriTon = $tonKho * $giaBan;
                    $totalValue += $giaTriTon;
                    $totalQuantity += $tonKho;
                    
                    $sheet->setCellValue('A'.$row, $item->ma_thuoc ?? '');
                    $sheet->setCellValue('B'.$row, $item->ten_thuoc ?? '');
                    $sheet->setCellValue('C'.$row, $item->don_vi_tinh ?? '');
                    $sheet->setCellValue('D'.$row, $item->ten_kho ?? '');
                    $sheet->setCellValue('E'.$row, $item->ma_lo ?? '');
                    $sheet->setCellValue('F'.$row, $tonKho);
                    $sheet->setCellValue('G'.$row, $this->formatMoney($giaBan));
                    $sheet->setCellValue('H'.$row, $this->formatMoney($giaTriTon));
                    
                    $sheet->getStyle('A'.$row.':H'.$row)->applyFromArray([
                        'borders' => ['allBorders' => ['borderStyle' => Border::BORDER_THIN]],
                    ]);
                    $sheet->getStyle('F'.$row.':H'.$row)->getAlignment()->setHorizontal(Alignment::HORIZONTAL_RIGHT);
                    $row++;
                }
                break;
        }

        // Tổng cộng tùy theo loại báo cáo
        switch($loaiBaoCao) {
            case 'thuoc':
                $sheet->setCellValue('A'.$row, 'TỔNG CỘNG');
                $sheet->mergeCells('A'.$row.':C'.$row);
                $sheet->setCellValue('D'.$row, $totalQuantity);
                $sheet->setCellValue('F'.$row, $this->formatMoney($totalValue));
                $sheet->getStyle('A'.$row.':F'.$row)->applyFromArray([
                    'font' => ['bold' => true],
                    'borders' => ['allBorders' => ['borderStyle' => Border::BORDER_THIN]],
                ]);
                $sheet->getStyle('A'.$row)->getAlignment()->setHorizontal(Alignment::HORIZONTAL_RIGHT);
                $sheet->getStyle('D'.$row.':F'.$row)->getAlignment()->setHorizontal(Alignment::HORIZONTAL_RIGHT);
                
                foreach(range('A', 'F') as $column) {
                    $sheet->getColumnDimension($column)->setAutoSize(true);
                }
                break;
                
            case 'kho':
            case 'khach_hang':
                $sheet->setCellValue('A'.$row, 'TỔNG CỘNG');
                $sheet->mergeCells('A'.$row.':C'.$row);
                $sheet->setCellValue('D'.$row, $totalQuantity);
                $sheet->setCellValue('E'.$row, $this->formatMoney($totalValue));
                $sheet->getStyle('A'.$row.':E'.$row)->applyFromArray([
                    'font' => ['bold' => true],
                    'borders' => ['allBorders' => ['borderStyle' => Border::BORDER_THIN]],
                ]);
                $sheet->getStyle('A'.$row)->getAlignment()->setHorizontal(Alignment::HORIZONTAL_RIGHT);
                $sheet->getStyle('D'.$row.':E'.$row)->getAlignment()->setHorizontal(Alignment::HORIZONTAL_RIGHT);
                
                foreach(range('A', 'E') as $column) {
                    $sheet->getColumnDimension($column)->setAutoSize(true);
                }
                break;
                
            default: // 'lo'
                $sheet->setCellValue('A'.$row, 'TỔNG CỘNG');
                $sheet->mergeCells('A'.$row.':E'.$row);
                $sheet->setCellValue('F'.$row, $totalQuantity);
                $sheet->setCellValue('H'.$row, $this->formatMoney($totalValue));
                $sheet->getStyle('A'.$row.':H'.$row)->applyFromArray([
                    'font' => ['bold' => true],
                    'borders' => ['allBorders' => ['borderStyle' => Border::BORDER_THIN]],
                ]);
                $sheet->getStyle('A'.$row)->getAlignment()->setHorizontal(Alignment::HORIZONTAL_RIGHT);
                $sheet->getStyle('F'.$row.':H'.$row)->getAlignment()->setHorizontal(Alignment::HORIZONTAL_RIGHT);
                
                foreach(range('A', 'H') as $column) {
                    $sheet->getColumnDimension($column)->setAutoSize(true);
                }
                break;
        }

        // Sau khi ghi dòng tổng cộng
        $row++;
        // Thêm phần cuối: Hà Nội, ngày ... tháng ... năm ...
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

        $fileName = 'bao-cao-ton-kho-' . $ngayBaoCao . '.xlsx';
        $writer = new Xlsx($spreadsheet);

        header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
        header('Content-Disposition: attachment; filename="'. urlencode($fileName).'"');
        header('Cache-Control: max-age=0');

        $writer->save('php://output');
        exit;
    }
}
