<?php

namespace App\Http\Controllers;

use App\Models\DonBanLe;
use App\Models\ChiTietDonBanLe;
use App\Models\KhachHang;
use App\Models\Thuoc;
use App\Models\LoThuoc;
use App\Models\NguoiDung;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;
use Exception;

class DonBanLeController extends Controller
{
    /**
     * Hiển thị danh sách đơn bán lẻ
     */
    public function index(Request $request)
    {
        $query = DonBanLe::with(['nguoiDung', 'khachHang', 'chiTietDonBanLe.loThuoc.thuoc']);
        
        // Lọc theo từ khóa (mã đơn hoặc tên/sđt khách hàng)
        if ($request->has('keyword') && $request->keyword != '') {
            $keyword = $request->keyword;
            $query->where(function($q) use ($keyword) {
                $q->where('ma_don', 'like', "%{$keyword}%")
                  ->orWhereHas('khachHang', function($q) use ($keyword) {
                      $q->where('ho_ten', 'like', "%{$keyword}%")
                        ->orWhere('sdt', 'like', "%{$keyword}%");
                  });
            });
        }
        
        // Lọc theo ngày
        if ($request->has('from_date') && $request->from_date) {
            $query->whereDate('ngay_ban', '>=', $request->from_date);
        }
        
        if ($request->has('to_date') && $request->to_date) {
            $query->whereDate('ngay_ban', '<=', $request->to_date);
        }
        
        // Lọc theo trạng thái
        if ($request->has('status') && $request->status != '') {
            $query->where('trang_thai', $request->status);
        }
        
        // Lọc theo nhân viên
        if ($request->has('staff') && $request->staff != '') {
            $query->where('nguoi_dung_id', $request->staff);
        }

        // Sắp xếp kết quả
        $sortField = $request->input('sort_by', 'ngay_ban');
        $sortDirection = $request->input('sort_direction', 'desc');
        
        $query->orderBy($sortField, $sortDirection);
        
        $donBanLes = $query->paginate(10)->withQueryString();
        
        // Lấy danh sách nhân viên cho bộ lọc
        $nhanViens = NguoiDung::where('vai_tro', 'duoc_si')
            ->orderBy('ho_ten')
            ->get(['nguoi_dung_id', 'ho_ten']);

        if ($request->ajax()) {
            return response()->json([
                'data' => view('don-ban-le.partials._list', compact('donBanLes'))->render(),
                'pagination' => view('layouts.partials._pagination', ['paginator' => $donBanLes])->render()
            ]);
        }
        
        return view('don-ban-le.index', compact('donBanLes', 'nhanViens'));
    }
    
    /**
     * Tìm kiếm thuốc theo tên hoặc mã
     */
    public function searchThuoc(Request $request)
    {
        $keyword = $request->input('keyword', '');
        
        if (empty($keyword)) {
            return response()->json([
                'success' => false,
                'message' => 'Vui lòng nhập từ khóa tìm kiếm'
            ]);
        }
        
        // Tìm kiếm theo tên hoặc mã thuốc (không sử dụng eager loading với giaThuoc)
        $thuocs = Thuoc::where('ten_thuoc', 'like', "%{$keyword}%")
                ->orWhere('ma_thuoc', 'like', "%{$keyword}%")
                ->get();
                
        if ($thuocs->isEmpty()) {
            return response()->json([
                'success' => false,
                'message' => 'Không tìm thấy thuốc phù hợp'
            ]);
        }
        
        $results = $thuocs->map(function($thuoc) {
            // Lấy các lô thuốc còn hạn sử dụng và có tồn kho
            $loThuocs = $thuoc->getLoThuocConHang();
            
            // Lấy tổng số lượng còn trong tất cả các lô
            $tongTonKho = $loThuocs->sum('ton_kho_hien_tai');
            
            // Lấy giá bán hiện tại
            $giaBan = optional($thuoc->giaBanHienTai())->gia_ban ?? 0;
            
            return [
                'thuoc_id' => $thuoc->thuoc_id,
                'ten_thuoc' => $thuoc->ten_thuoc,
                'ma_thuoc' => $thuoc->ma_thuoc,
                'don_vi_ban' => $thuoc->don_vi_ban,
                'don_vi_goc' => $thuoc->don_vi_goc,
                'ti_le_quy_doi' => $thuoc->ti_le_quy_doi,
                'gia_ban' => $giaBan,
                'tong_ton_kho' => $tongTonKho,
                'co_hang' => $tongTonKho > 0,
                'vat' => $thuoc->vat ?? 0
            ];
        })->filter(function($item) {
            // Lọc ra các thuốc có tồn kho
            return $item['co_hang'];
        })->values();
        
        return response()->json([
            'success' => true,
            'thuocs' => $results
        ]);
    }
    
    /**
     * Lấy thông tin thuốc cho form tạo đơn
     */
    public function getThuocInfo(Request $request)
    {
        $thuoc = Thuoc::find($request->thuoc_id);
        
        if (!$thuoc) {
            return response()->json([
                'success' => false,
                'message' => 'Không tìm thấy thông tin thuốc'
            ], 404);
        }
        
        // Lấy các lô thuốc còn hạn sử dụng và có tồn kho, ưu tiên lô nhập cũ nhất
        $loThuocs = $thuoc->getLoThuocConHang();
        
        // Lấy tổng số lượng còn trong tất cả các lô
        $tongTonKho = $loThuocs->sum('ton_kho_hien_tai');
        
        // Lấy giá bán hiện tại thông qua method (không sử dụng relation)
        $giaThuoc = $thuoc->giaBanHienTai();
        $giaBan = $giaThuoc ? $giaThuoc->gia_ban : 0;
        
        return response()->json([
            'success' => true,
            'thuoc' => [
                'thuoc_id' => $thuoc->thuoc_id,
                'ten_thuoc' => $thuoc->ten_thuoc,
                'ma_thuoc' => $thuoc->ma_thuoc,
                'don_vi_ban' => $thuoc->don_vi_ban,
                'don_vi_goc' => $thuoc->don_vi_goc,
                'ti_le_quy_doi' => $thuoc->ti_le_quy_doi,
                'vat' => $thuoc->vat ?? 0,
                'gia_ban' => $giaBan,
                'tong_ton_kho' => $tongTonKho
            ],
            'lo_thuocs' => $loThuocs->map(function($lo) {
                return [
                    'lo_id' => $lo->lo_id,
                    'ma_lo' => $lo->ma_lo,
                    'han_su_dung' => $lo->han_su_dung,
                    'ton_kho_hien_tai' => $lo->ton_kho_hien_tai
                ];
            })
        ]);
    }
    
    /**
     * Tạo đơn bán lẻ mới
     */
    public function store(Request $request)
    {
        // Validate đầu vào
        $request->validate([
            'khach_hang_id' => 'nullable|exists:khach_hang,khach_hang_id',
            'khach_hang_moi.ho_ten' => 'required_without:khach_hang_id|string|max:100',
            'khach_hang_moi.sdt' => 'required_without:khach_hang_id|string|max:20',
            'items' => 'required|array|min:1',
            'items.*.thuoc_id' => 'required|exists:thuoc,thuoc_id',
            'items.*.lo_id' => 'required|exists:lo_thuoc,lo_id',
            'items.*.so_luong' => 'required|numeric|min:0.01',
            'items.*.don_vi' => 'required|string',
            'items.*.gia_ban' => 'required|numeric|min:0',
            'items.*.thanh_tien' => 'required|numeric|min:0',
        ]);

        DB::beginTransaction();
        
        try {
            // Xử lý thông tin khách hàng
            $khachHangId = $request->khach_hang_id;
            
            if (!$khachHangId && isset($request->khach_hang_moi)) {
                // Tạo khách hàng mới
                $khachHang = KhachHang::create([
                    'ho_ten' => $request->khach_hang_moi['ho_ten'],
                    'sdt' => $request->khach_hang_moi['sdt'],
                ]);
                $khachHangId = $khachHang->khach_hang_id;
            }
            
            // Tạo mã đơn bán lẻ (DBL + yyyymmdd + 4 số tự động tăng)
            $today = Carbon::now()->format('Ymd');
            $latestDon = DonBanLe::where('ma_don', 'like', "DBL{$today}%")
                ->orderBy('ma_don', 'desc')
                ->first();
                
            if ($latestDon) {
                $lastNumber = intval(substr($latestDon->ma_don, -4));
                $newNumber = $lastNumber + 1;
                $maDon = "DBL{$today}" . str_pad($newNumber, 4, '0', STR_PAD_LEFT);
            } else {
                $maDon = "DBL{$today}0001";
            }
            
            // Tính tổng tiền và VAT
            $tongTien = 0;
            $tongVat = 0;
            
            foreach ($request->items as $item) {
                $tongTien += $item['thanh_tien'];
                $tongVat += $item['tien_thue'] ?? 0;
            }
            
            // Tạo đơn bán lẻ
            $donBanLe = DonBanLe::create([
                'ma_don' => $maDon,
                'nguoi_dung_id' => Auth::id(),
                'khach_hang_id' => $khachHangId,
                'ngay_ban' => Carbon::now(),
                'trang_thai' => 'hoan_thanh',
                'tong_tien' => $tongTien - $tongVat,
                'vat' => $tongVat,
                'tong_cong' => $tongTien,
            ]);
            
            // Thêm chi tiết đơn bán lẻ và cập nhật tồn kho
            foreach ($request->items as $item) {
                $loThuoc = LoThuoc::find($item['lo_id']);
                
                if (!$loThuoc || $loThuoc->ton_kho_hien_tai < $item['so_luong']) {
                    throw new Exception('Số lượng tồn kho không đủ cho sản phẩm ' . $item['ten_thuoc']);
                }
                
                // Tạo chi tiết đơn hàng
                ChiTietDonBanLe::create([
                    'don_id' => $donBanLe->don_id,
                    'lo_id' => $item['lo_id'],
                    'don_vi' => $item['don_vi'],
                    'so_luong' => $item['so_luong'],
                    'gia_ban' => $item['gia_ban'],
                    'thue_suat' => $item['thue_suat'] ?? 0,
                    'tien_thue' => $item['tien_thue'] ?? 0,
                    'thanh_tien' => $item['thanh_tien'],
                ]);
                
                // Cập nhật tồn kho
                $loThuoc->ton_kho_hien_tai -= $item['so_luong'];
                $loThuoc->save();
            }
            
            DB::commit();
            
            return response()->json([
                'success' => true,
                'message' => 'Đơn hàng đã được tạo thành công',
                'don_id' => $donBanLe->don_id,
                'ma_don' => $donBanLe->ma_don
            ]);
            
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'success' => false,
                'message' => 'Lỗi: ' . $e->getMessage()
            ], 422);
        }
    }
    
    /**
     * Hiển thị chi tiết đơn hàng
     */
    public function show(DonBanLe $donBanLe)
    {
        $donBanLe->load([
            'nguoiDung', 
            'khachHang', 
            'chiTietDonBanLe.loThuoc.thuoc'
        ]);
        
        if (request()->ajax()) {
            return response()->json([
                'success' => true,
                'donBanLe' => $donBanLe,
            ]);
        }
        
        return view('don-ban-le.show', compact('donBanLe'));
    }
    
    /**
     * Hủy đơn bán lẻ
     */
    public function cancel(DonBanLe $donBanLe)
    {
        // Kiểm tra xem đơn có thể hủy không
        if ($donBanLe->trang_thai != 'hoan_thanh') {
            return response()->json([
                'success' => false,
                'message' => 'Chỉ có thể hủy đơn hàng ở trạng thái hoàn thành'
            ], 422);
        }
        
        DB::beginTransaction();
        
        try {
            // Cập nhật trạng thái đơn
            $donBanLe->trang_thai = 'da_huy';
            $donBanLe->save();
            
            // Hoàn lại tồn kho
            foreach ($donBanLe->chiTietDonBanLe as $chiTiet) {
                $loThuoc = $chiTiet->loThuoc;
                $loThuoc->ton_kho_hien_tai += $chiTiet->so_luong;
                $loThuoc->save();
            }
            
            DB::commit();
            
            return response()->json([
                'success' => true,
                'message' => 'Đơn hàng đã được hủy thành công'
            ]);
            
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'success' => false,
                'message' => 'Lỗi: ' . $e->getMessage()
            ], 422);
        }
    }
    
    /**
     * In đơn bán lẻ
     */
    public function print(DonBanLe $donBanLe)
    {
        $donBanLe->load([
            'nguoiDung', 
            'khachHang', 
            'chiTietDonBanLe.loThuoc.thuoc'
        ]);
        
        return view('don-ban-le.print', compact('donBanLe'));
    }
    
    /**
     * Báo cáo doanh số
     */
    public function report(Request $request)
    {
        $fromDate = $request->input('from_date', Carbon::now()->startOfMonth()->format('Y-m-d'));
        $toDate = $request->input('to_date', Carbon::now()->format('Y-m-d'));
        
        // Thống kê doanh thu theo ngày
        $doanhThuTheoNgay = DonBanLe::whereDate('ngay_ban', '>=', $fromDate)
            ->whereDate('ngay_ban', '<=', $toDate)
            ->where('trang_thai', 'hoan_thanh')
            ->selectRaw('DATE(ngay_ban) as ngay, COUNT(*) as so_don, SUM(tong_cong) as doanh_thu')
            ->groupBy(DB::raw('DATE(ngay_ban)'))
            ->orderBy('ngay')
            ->get();
            
        // Thống kê doanh thu theo nhân viên
        $doanhThuTheoNV = DonBanLe::whereDate('ngay_ban', '>=', $fromDate)
            ->whereDate('ngay_ban', '<=', $toDate)
            ->where('trang_thai', 'hoan_thanh')
            ->with('nguoiDung:nguoi_dung_id,ho_ten')
            ->selectRaw('nguoi_dung_id, COUNT(*) as so_don, SUM(tong_cong) as doanh_thu')
            ->groupBy('nguoi_dung_id')
            ->orderBy('doanh_thu', 'desc')
            ->get();
            
        // Thống kê sản phẩm bán chạy
        $sanPhamBanChay = ChiTietDonBanLe::join('don_ban_le', 'chi_tiet_don_ban_le.don_id', '=', 'don_ban_le.don_id')
            ->join('lo_thuoc', 'chi_tiet_don_ban_le.lo_id', '=', 'lo_thuoc.lo_id')
            ->join('thuoc', 'lo_thuoc.thuoc_id', '=', 'thuoc.thuoc_id')
            ->whereDate('don_ban_le.ngay_ban', '>=', $fromDate)
            ->whereDate('don_ban_le.ngay_ban', '<=', $toDate)
            ->where('don_ban_le.trang_thai', 'hoan_thanh')
            ->selectRaw('thuoc.thuoc_id, thuoc.ten_thuoc, SUM(chi_tiet_don_ban_le.so_luong) as so_luong, SUM(chi_tiet_don_ban_le.thanh_tien) as doanh_thu')
            ->groupBy('thuoc.thuoc_id', 'thuoc.ten_thuoc')
            ->orderBy('doanh_thu', 'desc')
            ->limit(10)
            ->get();
            
        // Tổng doanh thu trong khoảng thời gian
        $tongDoanhThu = DonBanLe::whereDate('ngay_ban', '>=', $fromDate)
            ->whereDate('ngay_ban', '<=', $toDate)
            ->where('trang_thai', 'hoan_thanh')
            ->sum('tong_cong');
            
        // Tổng số đơn hàng
        $tongSoDon = DonBanLe::whereDate('ngay_ban', '>=', $fromDate)
            ->whereDate('ngay_ban', '<=', $toDate)
            ->where('trang_thai', 'hoan_thanh')
            ->count();
            
        return view('don-ban-le.report', compact(
            'fromDate', 
            'toDate',
            'doanhThuTheoNgay',
            'doanhThuTheoNV',
            'sanPhamBanChay',
            'tongDoanhThu',
            'tongSoDon'
        ));
    }
}
