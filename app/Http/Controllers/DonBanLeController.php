<?php

namespace App\Http\Controllers;

use App\Models\DonBanLe;
use App\Models\ChiTietDonBanLe;
use App\Models\KhachHang;
use App\Models\Thuoc;
use App\Models\LoThuoc;
use App\Models\LichSuTonKho;
use App\Models\NguoiDung;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;
use Exception;

class DonBanLeController extends Controller
{
    /**
     * Hiển thị danh sách hóa đơn
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
            // Calculate summary data for AJAX response
            $totalOrders = $donBanLes->total();
            
            // Create a fresh query for the summaries to avoid filter issues
            $summaryQuery = DonBanLe::query();
            
            // Apply the same filters as the main query except pagination
            if ($request->has('keyword') && $request->keyword != '') {
                $keyword = $request->keyword;
                $summaryQuery->where(function($q) use ($keyword) {
                    $q->where('ma_don', 'like', "%{$keyword}%")
                      ->orWhereHas('khachHang', function($q) use ($keyword) {
                          $q->where('ho_ten', 'like', "%{$keyword}%")
                            ->orWhere('sdt', 'like', "%{$keyword}%");
                      });
                });
            }
            
            if ($request->has('from_date') && $request->from_date) {
                $summaryQuery->whereDate('ngay_ban', '>=', $request->from_date);
            }
            
            if ($request->has('to_date') && $request->to_date) {
                $summaryQuery->whereDate('ngay_ban', '<=', $request->to_date);
            }
            
            if ($request->has('staff') && $request->staff != '') {
                $summaryQuery->where('nguoi_dung_id', $request->staff);
            }
            
            $completedOrders = (clone $summaryQuery)->whereIn('trang_thai', ['hoan_thanh', 'hoan_tat'])->count();
            $cancelledOrders = (clone $summaryQuery)->whereIn('trang_thai', ['da_huy', 'huy'])->count();
            $totalRevenue = (clone $summaryQuery)->whereIn('trang_thai', ['hoan_thanh', 'hoan_tat'])->sum('tong_cong');
            
            return response()->json([
                'data' => view('don-ban-le.partials._list', compact('donBanLes'))->render(),
                'pagination' => view('layouts.partials._pagination', ['paginator' => $donBanLes])->render(),
                'summaries' => [
                    'totalOrders' => $totalOrders,
                    'completedOrders' => $completedOrders,
                    'cancelledOrders' => $cancelledOrders,
                    'totalRevenue' => $totalRevenue,
                ]
            ]);
        }
        
        return view('don-ban-le.index', compact('donBanLes', 'nhanViens'));
    }
    
    /**
     * Tìm kiếm thuốc theo tên hoặc mã
     */
    public function searchThuoc(Request $request)
    {
        $keyword = trim($request->input('keyword', ''));

        if (empty($keyword)) {
            return response()->json([
                'success' => false,
                'message' => 'Vui lòng nhập từ khóa tìm kiếm'
            ]);
        }

        // Chuẩn hóa Unicode (nếu có ext intl)
        if (class_exists('\Normalizer')) {
            $keyword = \Normalizer::normalize($keyword, \Normalizer::FORM_C);
        }

        // Truy vấn để lấy thông tin thuốc với giá mới nhất - CHỈ THUỐC CÓ HÀNG
        $thuocs = DB::select("
            SELECT 
                t.thuoc_id,
                t.ten_thuoc,
                t.ma_thuoc,
                t.don_vi_ban,
                t.don_vi_goc,
                t.ti_le_quy_doi,
                COALESCE(latest_price.gia_ban, 0) AS gia_ban,
                inventory.tong_ton_kho,
                0 AS vat
            FROM thuoc t
            LEFT JOIN (
                SELECT g1.thuoc_id, g1.gia_ban
                FROM gia_thuoc g1
                INNER JOIN (
                    SELECT 
                        thuoc_id, 
                        MAX(id) AS latest_id
                    FROM gia_thuoc
                    WHERE ngay_bat_dau <= CURDATE()
                    AND (ngay_ket_thuc IS NULL OR ngay_ket_thuc >= CURDATE())
                    GROUP BY thuoc_id
                ) g2 ON g1.id = g2.latest_id
            ) latest_price ON latest_price.thuoc_id = t.thuoc_id
            INNER JOIN (
                SELECT thuoc_id, SUM(ton_kho_hien_tai) AS tong_ton_kho
                FROM lo_thuoc
                WHERE han_su_dung >= CURDATE()
                AND ton_kho_hien_tai > 0
                GROUP BY thuoc_id
            ) inventory ON inventory.thuoc_id = t.thuoc_id
            WHERE (t.ten_thuoc LIKE ? OR t.ma_thuoc LIKE ?)
            AND t.trang_thai = 1
            ORDER BY t.ten_thuoc ASC
            LIMIT 20
        ", ["%{$keyword}%", "%{$keyword}%"]);

        if (empty($thuocs)) {
            return response()->json([
                'success' => false,
                'message' => 'Không tìm thấy thuốc phù hợp'
            ]);
        }

        // Lấy thêm thông tin về các lô của từng thuốc
        $results = collect($thuocs)->map(function($thuoc) {
            // Lấy thông tin chi tiết của các lô thuốc 
            $loThuocs = DB::table('lo_thuoc')
                ->where('thuoc_id', $thuoc->thuoc_id)
                ->where('han_su_dung', '>=', now())
                ->where('ton_kho_hien_tai', '>', 0)
                ->select('lo_id', 'ma_lo', 'han_su_dung', 'ton_kho_hien_tai')
                ->orderBy('han_su_dung', 'asc') // Lô gần hết hạn đầu tiên (FIFO)
                ->get();
                
            return [
                'thuoc_id'     => $thuoc->thuoc_id,
                'ten_thuoc'    => $thuoc->ten_thuoc,
                'ma_thuoc'     => $thuoc->ma_thuoc,
                'don_vi_ban'   => $thuoc->don_vi_ban,
                'don_vi_goc'   => $thuoc->don_vi_goc,
                'ti_le_quy_doi'=> $thuoc->ti_le_quy_doi,
                'gia_ban'      => $thuoc->gia_ban,
                'tong_ton_kho' => $thuoc->tong_ton_kho,
                'co_hang'      => $thuoc->tong_ton_kho > 0,
                'vat'          => 0,
                'search_text'  => $thuoc->ten_thuoc . ' ' . $thuoc->ma_thuoc,
                'lo_thuocs'    => $loThuocs
            ];
        })->values();

        return response()->json([
            'success' => true,
            'thuocs'  => $results
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
        
        // Lấy lô thuốc cũ nhất để tự động chọn
        $loCuNhat = $thuoc->getLoThuocCuNhat();
        
        // Lấy tổng số lượng còn trong tất cả các lô
        $tongTonKho = $loThuocs->sum('ton_kho_hien_tai');
        
        // Lấy giá bán hiện tại thông qua method (không sử dụng relation)
        $giaThuoc = $thuoc->giaBanHienTai();
        $giaBan = $giaThuoc ? $giaThuoc->gia_ban : 0;
        $ngayHienTai = date('Y-m-d');
        
        // Kiểm tra nếu không có giá hiện tại
        $needPriceUpdate = false;
        if (!$giaThuoc) {
            $needPriceUpdate = true;
        } elseif ($giaThuoc->ngay_bat_dau > $ngayHienTai || 
                 ($giaThuoc->ngay_ket_thuc && $giaThuoc->ngay_ket_thuc < $ngayHienTai)) {
            $needPriceUpdate = true;
        }
        
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
                'tong_ton_kho' => $tongTonKho,
                'need_price_update' => $needPriceUpdate
            ],
            'lo_thuocs' => $loThuocs->map(function($lo) use ($loCuNhat) {
                return [
                    'lo_id' => $lo->lo_id,
                    'ma_lo' => $lo->ma_lo,
                    'han_su_dung' => $lo->han_su_dung,
                    'ton_kho_hien_tai' => $lo->ton_kho_hien_tai,
                    'is_oldest' => ($loCuNhat && $lo->lo_id === $loCuNhat->lo_id)
                ];
            }),
            'lo_cu_nhat' => $loCuNhat ? [
                'lo_id' => $loCuNhat->lo_id,
                'ma_lo' => $loCuNhat->ma_lo,
                'han_su_dung' => $loCuNhat->han_su_dung,
                'ton_kho_hien_tai' => $loCuNhat->ton_kho_hien_tai
            ] : null
        ]);
    }
    
    /**
     * Tạo Hóa đơnmới
     */
    public function store(Request $request)
    {
        // Validate đầu vào cơ bản
        $request->validate([
            'khach_hang_id' => 'nullable|exists:khach_hang,khach_hang_id',
            'khach_hang_moi.ho_ten' => 'required_without:khach_hang_id|string|max:100',
            'khach_hang_moi.sdt' => 'required_without:khach_hang_id|string|max:20',
            'items' => 'required|array|min:1',
            'items.*.thuoc_id' => 'required|exists:thuoc,thuoc_id',
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
                $khachHang = KhachHang::create([
                    'ho_ten' => $request->khach_hang_moi['ho_ten'],
                    'sdt' => $request->khach_hang_moi['sdt'],
                ]);
                $khachHangId = $khachHang->khach_hang_id;
            }

            // Tạo mã hóa đơn
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
            $tongTienHang = 0;
            $tongVat = 0;

            foreach ($request->items as $item) {
                $thanhTien = $item['so_luong'] * $item['gia_ban'];
                $tongTienHang += $thanhTien;
                $tongVat += $thanhTien * ($item['thue_suat'] ?? 0) / 100;
            }

            // Tạo Hóa đơnvới trạng thái CHỜ XỬ LÝ
            $donBanLe = DonBanLe::create([
                'ma_don' => $maDon,
                'nguoi_dung_id' => Auth::id(),
                'khach_hang_id' => $khachHangId,
                'ngay_ban' => Carbon::now(),
                'trang_thai' => 'cho_xu_ly', // Thay đổi từ 'hoan_tat' thành 'cho_xu_ly'
                'tong_tien' => $tongTienHang,
                'vat' => $tongVat,
                'tong_cong' => $tongTienHang + $tongVat,
            ]);

            // Chỉ thêm chi tiết hóa đơn, KHÔNG cập nhật tồn kho và lịch sử
            foreach ($request->items as $item) {
                $thuocId = $item['thuoc_id'];
                $soLuongCanBan = $item['so_luong'];
                $donViItem = $item['don_vi'];

                // Xác định lô thuốc để lưu vào chi tiết đơn
                $loId = null;
                
                if (isset($item['lo_id']) && $item['lo_id'] !== 'temporary' && is_numeric($item['lo_id'])) {
                    // Nếu có chỉ định lô cụ thể
                    $loId = $item['lo_id'];
                } else {
                    // Nếu là 'temporary' hoặc không có lô, lấy lô cũ nhất có tồn kho
                    $thuoc = Thuoc::find($thuocId);
                    $loCuNhat = $thuoc->getLoThuocConHang()->sortBy('han_su_dung')->first();
                    if ($loCuNhat) {
                        $loId = $loCuNhat->lo_id;
                    }
                }

                // Tạo chi tiết đơn (chưa trừ tồn kho)
                ChiTietDonBanLe::create([
                    'don_id' => $donBanLe->don_id,
                    'lo_id' => $loId,
                    'don_vi' => $donViItem === 'don_vi_goc' ? 0 : 1,
                    'so_luong' => $soLuongCanBan,
                    'gia_ban' => $item['gia_ban'],
                    'thue_suat' => $item['thue_suat'] ?? 0,
                    'tien_thue' => $item['tien_thue'] ?? 0,
                    'thanh_tien' => $item['thanh_tien'],
                ]);
            }

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'Đơn hàng đã được tạo thành công với trạng thái chờ xử lý',
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

    public function completeOrder($orderId)
    {
        DB::beginTransaction();

        try {
            $donBanLe = DonBanLe::with('chiTietDonBanLe')->find($orderId);
            
            if (!$donBanLe) {
                throw new Exception('Không tìm thấy đơn hàng');
            }

            if ($donBanLe->trang_thai === 'hoan_tat') {
                throw new Exception('Đơn hàng đã được hoàn tất trước đó');
            }

            if ($donBanLe->trang_thai !== 'cho_xu_ly') {
                throw new Exception('Chỉ có thể hoàn tất đơn hàng có trạng thái chờ xử lý');
            }

            // Lấy tất cả chi tiết đơn
            $chiTietDons = $donBanLe->chiTietDonBanLe;

            foreach($chiTietDons as $chiTiet) {
                $thuoc = $chiTiet->loThuoc->thuoc;
                $soLuongCanBan = $chiTiet->so_luong;
                $isDonViBan = ($chiTiet->don_vi == 1);

                // Tính hệ số quy đổi
                $heSoQuyDoi = 1;
                if ($isDonViBan && $thuoc->don_vi_ban != $thuoc->don_vi_goc && $thuoc->ti_le_quy_doi > 0) {
                    $heSoQuyDoi = $thuoc->ti_le_quy_doi;
                }

                // Quy đổi về đơn vị gốc
                $soLuongQuyDoiDonViGoc = $soLuongCanBan;
                if ($isDonViBan && $thuoc->don_vi_ban != $thuoc->don_vi_goc && $heSoQuyDoi > 0) {
                    $soLuongQuyDoiDonViGoc = $soLuongCanBan / $heSoQuyDoi;
                }

                // Kiểm tra và xử lý trừ tồn kho
                $this->processInventoryDeduction($chiTiet, $thuoc, $soLuongQuyDoiDonViGoc, $donBanLe);
            }

            // Cập nhật trạng thái đơn hàng
            $donBanLe->update([
                'trang_thai' => 'hoan_tat',
                'ngay_hoan_tat' => Carbon::now()
            ]);

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'Đơn hàng đã được hoàn tất và trừ tồn kho thành công'
            ]);

        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'success' => false,
                'message' => 'Lỗi: ' . $e->getMessage()
            ], 422);
        }
    }

    private function processInventoryDeduction($chiTiet, $thuoc, $soLuongQuyDoiDonViGoc, $donBanLe)
    {
        $loThuocGoc = $chiTiet->loThuoc;

        // Kiểm tra nếu lô gốc không đủ
        if ($loThuocGoc->ton_kho_hien_tai < $soLuongQuyDoiDonViGoc) {
            // Lấy tất cả lô khả dụng
            $cacLoThuoc = $thuoc->getLoThuocConHang()->sortBy('han_su_dung');
            
            $tongTonKho = $cacLoThuoc->sum('ton_kho_hien_tai');
            if ($tongTonKho < $soLuongQuyDoiDonViGoc) {
                throw new Exception('Tổng số lượng tồn kho không đủ cho sản phẩm ' . $thuoc->ten_thuoc);
            }

            $soLuongConLai = $soLuongQuyDoiDonViGoc;

            // Ưu tiên trừ từ lô gốc trước
            if ($loThuocGoc->ton_kho_hien_tai > 0) {
                $soLuongTru = min($loThuocGoc->ton_kho_hien_tai, $soLuongConLai);
                $this->deductInventoryFromLot($loThuocGoc, $soLuongTru, $donBanLe, $chiTiet);
                $soLuongConLai -= $soLuongTru;
            }

            // Trừ từ các lô khác nếu cần
            foreach ($cacLoThuoc as $lo) {
                if ($lo->lo_id === $loThuocGoc->lo_id || $soLuongConLai <= 0) continue;

                $soLuongTru = min($lo->ton_kho_hien_tai, $soLuongConLai);
                
                // Tạo chi tiết đơn bổ sung cho lô này
                $chiTietMoi = ChiTietDonBanLe::create([
                    'don_id' => $donBanLe->don_id,
                    'lo_id' => $lo->lo_id,
                    'don_vi' => $chiTiet->don_vi,
                    'so_luong' => $soLuongTru,
                    'gia_ban' => $chiTiet->gia_ban,
                    'thue_suat' => $chiTiet->thue_suat,
                    'tien_thue' => 0, // Phân bổ lại nếu cần
                    'thanh_tien' => 0, // Phân bổ lại nếu cần
                ]);

                $this->deductInventoryFromLot($lo, $soLuongTru, $donBanLe, $chiTietMoi);
                $soLuongConLai -= $soLuongTru;
            }
        } else {
            // Lô đủ số lượng
            $this->deductInventoryFromLot($loThuocGoc, $soLuongQuyDoiDonViGoc, $donBanLe, $chiTiet);
        }
    }

    private function deductInventoryFromLot($loThuoc, $soLuong, $donBanLe, $chiTiet)
    {
        // Trừ tồn kho
        $loThuoc->ton_kho_hien_tai -= $soLuong;
        $loThuoc->save();

        // Tạo lịch sử tồn kho
        LichSuTonKho::create([
            'lo_id' => $loThuoc->lo_id,
            'thuoc_id' => $loThuoc->thuoc_id,
            'don_ban_le_id' => $donBanLe->don_id,
            'chi_tiet_don_id' => $chiTiet->chi_tiet_id,
            'so_luong_thay_doi' => -$soLuong,
            'ton_kho_moi' => $loThuoc->ton_kho_hien_tai,
            'nguoi_dung_id' => Auth::id(),
            'loai_thay_doi' => 'ban',
            'mo_ta' => 'Bán thuốc: ' . $loThuoc->thuoc->ten_thuoc . ' - Lô: ' . $loThuoc->ma_lo . ' - Đơn: ' . $donBanLe->ma_don
        ]);
    }
    
    /**
     * Hiển thị chi tiết đơn hàng
     */
    public function show(DonBanLe $donBanLe)
    {
        // Load relationships with additional fields
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
     * Hủy hóa đơn
     */
    public function cancel($donBanLe)
    {
        // Tìm bản ghi DonBanLe
        $donBanLeS = DonBanLe::with('chiTietDonBanLe.loThuoc.thuoc')->findOrFail($donBanLe);

        // Kiểm tra trạng thái đơn
        if (!in_array($donBanLeS->trang_thai, ['hoan_thanh', 'cho_xu_ly'])) {
            return response()->json([
                'success' => false,
                'message' => 'Chỉ có thể hủy đơn hàng ở trạng thái hoàn thành hoặc chờ xử lý.'
            ], 422);
        }

        DB::beginTransaction();

        try {
            if ($donBanLeS->trang_thai === 'cho_xu_ly') {
                $donBanLeS->trang_thai = 'huy';
                $donBanLeS->save();

                DB::commit();

                return response()->json([
                    'success' => true,
                    'message' => 'Đơn hàng đã được xóa thành công.'
                ]);
            } else {
                // Cập nhật trạng thái đơn
                $donBanLeS->trang_thai = 'huy';
                $donBanLeS->save();

                // Hoàn lại tồn kho và ghi lịch sử tồn kho
                foreach ($donBanLeS->chiTietDonBanLe as $chiTiet) {
                    $loThuoc = $chiTiet->loThuoc;
                    $thuoc = $loThuoc->thuoc;

                    // Tính số lượng cần hoàn trả theo đơn vị gốc
                    $soLuongHoanTra = $chiTiet->so_luong;

                    // Quy đổi số lượng về đơn vị gốc nếu cần
                    if ($chiTiet->don_vi == 1 && $thuoc->don_vi_ban != $thuoc->don_vi_goc && $thuoc->ti_le_quy_doi > 0) {
                        $soLuongHoanTra = $chiTiet->so_luong / $thuoc->ti_le_quy_doi;
                    }

                    // Cập nhật tồn kho
                    $loThuoc->ton_kho_hien_tai += $soLuongHoanTra;
                    $loThuoc->save();

                    // Ghi lịch sử tồn kho
                    LichSuTonKho::create([
                        'lo_id' => $loThuoc->lo_id,
                        'thuoc_id' => $thuoc->thuoc_id,
                        'don_ban_le_id' => $donBanLeS->don_id,
                        'chi_tiet_don_id' => $chiTiet->chi_tiet_id,
                        'so_luong_thay_doi' => $soLuongHoanTra,
                        'ton_kho_moi' => $loThuoc->ton_kho_hien_tai,
                        'nguoi_dung_id' => auth()->id(),
                        'loai_thay_doi' => 'hoan_tra',
                        'mo_ta' => 'Hoàn trả thuốc do hủy đơn: ' . $thuoc->ten_thuoc . ' - Lô: ' . $loThuoc->ma_lo . ' - Đơn: ' . $donBanLeS->ma_don
                    ]);
                }

                DB::commit();

                return response()->json([
                    'success' => true,
                    'message' => 'Đơn hàng đã được hủy thành công và hàng đã được hoàn trả vào kho.'
                ]);
            }
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'success' => false,
                'message' => 'Lỗi khi hủy đơn hàng: ' . $e->getMessage()
            ], 500);
        }
    }
    
    /**
     * In hóa đơn
     */
    public function print($donBanLe)
    {

        $donBanLe = DonBanLe::with(['nguoiDung', 'khachHang', 'chiTietDonBanLe.loThuoc.thuoc'])
            ->find($donBanLe);
        
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
