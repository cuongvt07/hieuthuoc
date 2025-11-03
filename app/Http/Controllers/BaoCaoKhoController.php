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
use Illuminate\Support\Facades\DB;

class BaoCaoKhoController extends Controller
{
    public function index(Request $request) 
    {
        $khos = Kho::orderBy('ten_kho')->get();

        if ($request->has('export') && $request->export == 'excel') {
            return $this->exportExcel($request);
        }

        // --- Filter thời gian ---
        $tuNgay = $request->filled('tu_ngay') ? Carbon::parse($request->tu_ngay)->startOfDay() : null;
        $denNgay = $request->filled('den_ngay') ? Carbon::parse($request->den_ngay)->endOfDay() : null;

        // --- Nếu chọn kho cụ thể ---
        if ($request->filled('kho_id')) {
            $now = Carbon::now();
            $sixMonthsLater = $now->copy()->addMonths(6);

            $thuocs = Thuoc::with(['loThuoc' => function($query) use ($request, $tuNgay, $denNgay) {
                $query->where('kho_id', $request->kho_id)
                    ->where('ton_kho_hien_tai', '>', 0);
                if ($tuNgay) $query->where('ngay_tao', '>=', $tuNgay);
                if ($denNgay) $query->where('ngay_tao', '<=', $denNgay);
            }])
            ->select('thuoc.*')
            ->addSelect(DB::raw('(SELECT SUM(ton_kho_hien_tai) FROM lo_thuoc 
                WHERE lo_thuoc.thuoc_id = thuoc.thuoc_id 
                AND lo_thuoc.kho_id = ' . $request->kho_id . ' 
                AND lo_thuoc.ton_kho_hien_tai > 0' . 
                ($tuNgay ? ' AND lo_thuoc.created_at >= "' . $tuNgay->format('Y-m-d H:i:s') . '"' : '') .
                ($denNgay ? ' AND lo_thuoc.created_at <= "' . $denNgay->format('Y-m-d H:i:s') . '"' : '') . 
                ') as tong_ton_kho'))
            ->addSelect(DB::raw('(SELECT SUM(ton_kho_hien_tai * gia_nhap_tb) FROM lo_thuoc 
                WHERE lo_thuoc.thuoc_id = thuoc.thuoc_id 
                AND lo_thuoc.kho_id = ' . $request->kho_id . ' 
                AND lo_thuoc.ton_kho_hien_tai > 0' . 
                ($tuNgay ? ' AND lo_thuoc.created_at >= "' . $tuNgay->format('Y-m-d H:i:s') . '"' : '') .
                ($denNgay ? ' AND lo_thuoc.created_at <= "' . $denNgay->format('Y-m-d H:i:s') . '"' : '') . 
                ') as gia_tri_ton'))
            ->whereExists(function($query) use ($request, $tuNgay, $denNgay) {
                $query->select(DB::raw(1))
                    ->from('lo_thuoc')
                    ->whereColumn('lo_thuoc.thuoc_id', 'thuoc.thuoc_id')
                    ->where('lo_thuoc.kho_id', $request->kho_id)
                    ->where('ton_kho_hien_tai', '>', 0);
                if ($tuNgay) $query->where('lo_thuoc.created_at', '>=', $tuNgay);
                if ($denNgay) $query->where('lo_thuoc.created_at', '<=', $denNgay);
            })
            ->orderBy('thuoc.ten_thuoc')
            ->paginate(10);

            return view('bao-cao.kho.chi-tiet', compact('khos', 'thuocs'));
        }

        // --- Nếu không chọn kho cụ thể: tổng hợp ---
        $khoListQuery = Kho::select(
            'kho.kho_id',
            'kho.ten_kho',
            'kho.dia_chi',
            'kho.ghi_chu'
        )
        ->selectRaw('COUNT(DISTINCT thuoc.thuoc_id) as so_mat_hang')
        ->selectRaw('SUM(lo_thuoc.ton_kho_hien_tai) as tong_ton_kho')
        ->selectRaw('SUM(lo_thuoc.ton_kho_hien_tai * lo_thuoc.gia_nhap_tb) as tong_gia_tri')
        ->leftJoin('lo_thuoc', function($join) use ($tuNgay, $denNgay) {
            $join->on('kho.kho_id', '=', 'lo_thuoc.kho_id');
            if ($tuNgay) $join->where('lo_thuoc.created_at', '>=', $tuNgay);
            if ($denNgay) $join->where('lo_thuoc.created_at', '<=', $denNgay);
        })
        ->leftJoin('thuoc', function($join) {
            $join->on('lo_thuoc.thuoc_id', '=', 'thuoc.thuoc_id')
                 ->where('thuoc.trang_thai', 1);
        })
        ->groupBy('kho.kho_id', 'kho.ten_kho', 'kho.dia_chi', 'kho.ghi_chu')
        ->orderBy('kho.ten_kho');

        $khoList = $khoListQuery->get();

        return view('bao-cao.kho.tong-hop', compact('khos', 'khoList'));
    }

    private function exportExcel(Request $request)
    {
        $spreadsheet = new Spreadsheet();
        $sheet = $spreadsheet->getActiveSheet();

        // --- Header ---
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

    $sheet->setCellValue('A4', '');

    // Normalize date range for display in the report (be tolerant for different input formats)
    $startDate = Carbon::now()->startOfMonth();
    if ($request->filled('tu_ngay') && strlen(trim($request->tu_ngay)) > 0) {
        $raw = trim($request->tu_ngay);
        try {
            $startDate = Carbon::createFromFormat('d/m/Y', $raw);
        } catch (\Exception $e) {
            try {
                $startDate = Carbon::parse($raw);
            } catch (\Exception $e2) {
                $startDate = Carbon::now()->startOfMonth();
            }
        }
    }

    $endDate = Carbon::now();
    if ($request->filled('den_ngay') && strlen(trim($request->den_ngay)) > 0) {
        $raw = trim($request->den_ngay);
        try {
            $endDate = Carbon::createFromFormat('d/m/Y', $raw);
        } catch (\Exception $e) {
            try {
                $endDate = Carbon::parse($raw);
            } catch (\Exception $e2) {
                $endDate = Carbon::now();
            }
        }
    }
        if ($request->filled('kho_id')) {
            // --- Báo cáo chi tiết một kho ---
            $kho = Kho::find($request->kho_id);
            $sheet->setCellValue('A5', 'BÁO CÁO CHI TIẾT KHO: ' . $kho->ten_kho);
            $sheet->mergeCells('A5:E5');
            $sheet->getStyle('A5')->getFont()->setBold(true)->setSize(16);
            $sheet->getStyle('A5')->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);

            // Thông tin thời gian
            $sheet->setCellValue('A6', '(Từ ngày: ' . $startDate->format('d/m/Y') . ' - Đến ngày: ' . $endDate->format('d/m/Y') . ')');
            $sheet->mergeCells('A6:E6');
            $sheet->getStyle('A6')->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);

            $sheet->fromArray(['STT', 'Tên sản phẩm', 'Đơn vị', 'Số lượng tồn', 'Giá trị tồn'], null, 'A7');

            $thuocs = Thuoc::select('thuoc.thuoc_id', 'thuoc.ten_thuoc', 'thuoc.don_vi_goc')
                ->selectRaw('SUM(lo_thuoc.ton_kho_hien_tai) as tong_ton_kho')
                ->selectRaw('SUM(lo_thuoc.ton_kho_hien_tai * lo_thuoc.gia_nhap_tb) as gia_tri_ton')
                ->leftJoin('lo_thuoc', function($join) use ($request) {
                    $join->on('thuoc.thuoc_id', '=', 'lo_thuoc.thuoc_id')
                        ->where('lo_thuoc.kho_id', '=', $request->kho_id);
                })
                ->where('thuoc.trang_thai', 1)
                ->groupBy('thuoc.thuoc_id', 'thuoc.ten_thuoc', 'thuoc.don_vi_goc')
                ->having('tong_ton_kho', '>', 0)
                ->orderBy('thuoc.ten_thuoc')
                ->get();

            $row = 8;
            $tongSL = 0;
            $tongGT = 0;

            foreach ($thuocs as $i => $t) {
                $sheet->setCellValue("A$row", $i + 1);
                $sheet->setCellValue("B$row", $t->ten_thuoc);
                $sheet->setCellValue("C$row", $t->don_vi_goc);
                $sheet->setCellValue("D$row", $t->tong_ton_kho);
                $sheet->setCellValue("E$row", $t->gia_tri_ton);
                $tongSL += $t->tong_ton_kho;
                $tongGT += $t->gia_tri_ton;
                $row++;
            }

            $sheet->setCellValue("A$row", 'TỔNG CỘNG');
            $sheet->mergeCells("A$row:C$row");
            $sheet->setCellValue("D$row", $tongSL);
            $sheet->setCellValue("E$row", $tongGT);
            $sheet->getStyle("A$row:E$row")->getFont()->setBold(true);

            // Ký tên: Hà Nội, ngày ...
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
        } else {
            // --- Báo cáo tổng hợp tất cả kho ---
            $sheet->setCellValue('A5', 'BÁO CÁO TỒN KHO');
            $sheet->mergeCells('A5:E5');
            $sheet->getStyle('A5')->getFont()->setBold(true)->setSize(16);
            $sheet->getStyle('A5')->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);

            // Thông tin thời gian
            $sheet->setCellValue('A6', '(Từ ngày: ' . $startDate->format('d/m/Y') . ' - Đến ngày: ' . $endDate->format('d/m/Y') . ')');
            $sheet->mergeCells('A6:E6');
            $sheet->getStyle('A6')->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
            $sheet->fromArray(['STT', 'Tên kho', 'Số lượng mặt hàng', 'Tổng số lượng tồn', 'Tổng giá trị tồn'], null, 'A7');

            $khos = Kho::select('kho.kho_id', 'kho.ten_kho', 'kho.dia_chi', 'kho.ghi_chu')
                ->selectRaw('COUNT(DISTINCT thuoc.thuoc_id) as so_mat_hang')
                ->selectRaw('SUM(lo_thuoc.ton_kho_hien_tai) as tong_ton_kho')
                ->selectRaw('SUM(lo_thuoc.ton_kho_hien_tai * lo_thuoc.gia_nhap_tb) as tong_gia_tri')
                ->leftJoin('lo_thuoc', 'kho.kho_id', '=', 'lo_thuoc.kho_id')
                ->leftJoin('thuoc', 'lo_thuoc.thuoc_id', '=', 'thuoc.thuoc_id')
                ->where('thuoc.trang_thai', 1)
                ->groupBy('kho.kho_id', 'kho.ten_kho', 'kho.dia_chi', 'kho.ghi_chu')
                ->orderBy('kho.ten_kho')
                ->get();

            $row = 8;
            $tongMatHang = $tongSL = $tongGT = 0;

            foreach ($khos as $i => $k) {
                $sheet->setCellValue("A$row", $i + 1);
                $sheet->setCellValue("B$row", $k->ten_kho);
                $sheet->setCellValue("C$row", $k->so_mat_hang);
                $sheet->setCellValue("D$row", $k->tong_ton_kho);
                $sheet->setCellValue("E$row", $k->tong_gia_tri);
                $tongMatHang += $k->so_mat_hang;
                $tongSL += $k->tong_ton_kho;
                $tongGT += $k->tong_gia_tri;
                $row++;
            }

            $sheet->setCellValue("A$row", 'TỔNG CỘNG');
            $sheet->mergeCells("A$row:B$row");
            $sheet->setCellValue("C$row", $tongMatHang);
            $sheet->setCellValue("D$row", $tongSL);
            $sheet->setCellValue("E$row", $tongGT);
            $sheet->getStyle("A$row:E$row")->getFont()->setBold(true);

            // Ký tên: Hà Nội, ngày ...
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

        // --- Style chung ---
        foreach (range('A', 'E') as $col) {
            $sheet->getColumnDimension($col)->setAutoSize(true);
        }

        $filename = 'bao-cao-kho-' . date('Y-m-d-H-i-s') . '.xlsx';
        $writer = new Xlsx($spreadsheet);

        header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
        header("Content-Disposition: attachment; filename=\"$filename\"");
        header('Cache-Control: max-age=0');

        $writer->save('php://output');
        exit;
    }
}