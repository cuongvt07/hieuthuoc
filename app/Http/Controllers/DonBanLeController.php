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

        // Truy vấn cơ bản để lấy thông tin thuốc
        $thuocs = DB::select("
            SELECT 
                t.thuoc_id,
                t.ten_thuoc,
                t.ma_thuoc,
                t.don_vi_ban,
                t.don_vi_goc,
                t.ti_le_quy_doi,
                COALESCE(g.gia_ban, 0) AS gia_ban,
                COALESCE(SUM(l.ton_kho_hien_tai), 0) AS tong_ton_kho,
                0 AS vat
            FROM thuoc t
            LEFT JOIN gia_thuoc g 
                ON g.thuoc_id = t.thuoc_id 
            AND g.ngay_bat_dau <= CURDATE()
            AND (g.ngay_ket_thuc IS NULL OR g.ngay_ket_thuc >= CURDATE())
            LEFT JOIN lo_thuoc l 
                ON l.thuoc_id = t.thuoc_id 
            AND l.han_su_dung >= CURDATE()
            AND l.ton_kho_hien_tai > 0
            WHERE t.ten_thuoc LIKE ? OR t.ma_thuoc LIKE ?
            GROUP BY 
                t.thuoc_id, t.ten_thuoc, t.ma_thuoc, 
                t.don_vi_ban, t.don_vi_goc, t.ti_le_quy_doi, g.gia_ban
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
                'vat'          => 0, // Gán giá trị VAT mặc định là 0
                'search_text'  => $thuoc->ten_thuoc . ' ' . $thuoc->ma_thuoc,
                'lo_thuocs'    => $loThuocs // Thêm thông tin chi tiết các lô
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
     * Tạo đơn bán lẻ mới
     */
    public function store(Request $request)
    {
        // Validate đầu vào cơ bản (trừ lo_id sẽ xử lý đặc biệt)
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
                'trang_thai' => 'hoan_tat',
                'tong_tien' => $tongTien - $tongVat,
                'vat' => $tongVat,
                'tong_cong' => $tongTien,
            ]);

            // Thêm chi tiết đơn bán lẻ và cập nhật tồn kho
            foreach ($request->items as $item) {
                $thuocId = $item['thuoc_id'];
                $soLuongCanBan = $item['so_luong'];
                $donViItem = $item['don_vi'];

                // Tính hệ số quy đổi nếu cần
                $thuoc = Thuoc::find($thuocId);

                // Kiểm tra nếu đơn vị là đơn vị bán (dựa trên don_vi từ JavaScript)
                $heSoQuyDoi = 1; // Mặc định là 1 (không cần quy đổi)
                $isDonViBan = ($donViItem === 'don_vi_ban');

                if ($isDonViBan && $thuoc->don_vi_ban != $thuoc->don_vi_goc && $thuoc->ti_le_quy_doi > 0) {
                    $heSoQuyDoi = $thuoc->ti_le_quy_doi;
                }

                // Quy đổi số lượng cần bán về đơn vị gốc (chia thay vì nhân)
                $soLuongQuyDoiDonViGoc = $soLuongCanBan;
                if ($isDonViBan && $thuoc->don_vi_ban != $thuoc->don_vi_goc && $heSoQuyDoi > 0) {
                    $soLuongQuyDoiDonViGoc = $soLuongCanBan / $heSoQuyDoi;
                }

                // Kiểm tra nếu lo_id là "temporary", xử lý đặc biệt bằng cách tự động chọn lô cũ nhất
                if (isset($item['lo_id']) && $item['lo_id'] === 'temporary') {
                    // Lấy tất cả các lô của thuốc còn hạn sử dụng và có tồn kho, ưu tiên lô cũ nhất (HSD gần hết hạn nhất)
                    $cacLoThuoc = $thuoc->getLoThuocConHang()->sortBy('han_su_dung');

                    if ($cacLoThuoc->isEmpty()) {
                        throw new Exception('Không có lô thuốc nào khả dụng cho sản phẩm ' . $thuoc->ten_thuoc);
                    }

                    $tongTonKho = $cacLoThuoc->sum('ton_kho_hien_tai');
                    if ($tongTonKho < $soLuongQuyDoiDonViGoc) {
                        throw new Exception('Tổng số lượng tồn kho không đủ cho sản phẩm ' . $thuoc->ten_thuoc . ' (Yêu cầu: ' . $soLuongQuyDoiDonViGoc . ' ' . $thuoc->don_vi_goc . ', Tồn kho: ' . $tongTonKho . ' ' . $thuoc->don_vi_goc . ')');
                    }

                    // Biến để theo dõi số lượng còn cần lấy
                    $soLuongConLai = $soLuongQuyDoiDonViGoc;

                    // Duyệt qua các lô theo thứ tự cũ nhất đến mới nhất (HSD tăng dần)
                    foreach ($cacLoThuoc as $loThuoc) {
                        if ($soLuongConLai <= 0) break;

                        // Số lượng có thể lấy từ lô này
                        $soLuongLayTuLo = min($loThuoc->ton_kho_hien_tai, $soLuongConLai);

                        // Quy đổi số lượng hiển thị nếu là đơn vị bán
                        $soLuongHienThi = $soLuongLayTuLo;
                        if ($isDonViBan && $thuoc->don_vi_ban != $thuoc->don_vi_goc && $heSoQuyDoi > 0) {
                            $soLuongHienThi = $soLuongLayTuLo / $heSoQuyDoi;
                        }

                        // Sử dụng thanh_tien từ request thay vì tính lại
                        $thanhTien = $item['thanh_tien'] * ($soLuongHienThi / $soLuongCanBan); // Phân bổ thanh_tien theo số lượng
                        $tienThue = ($item['tien_thue'] ?? 0) * ($soLuongHienThi / $soLuongCanBan);

                        $chiTiet = ChiTietDonBanLe::create([
                            'don_id' => $donBanLe->don_id,
                            'lo_id' => $loThuoc->lo_id,
                            'don_vi' => $donViItem === 'don_vi_goc' ? 0 : 1, // Chuyển thành 0 hoặc 1
                            'so_luong' => $soLuongHienThi,
                            'gia_ban' => $item['gia_ban'],
                            'thue_suat' => $item['thue_suat'] ?? 0,
                            'tien_thue' => $tienThue,
                            'thanh_tien' => $thanhTien,
                        ]);

                        // Cập nhật tồn kho
                        $loThuoc->ton_kho_hien_tai -= $soLuongLayTuLo;
                        $loThuoc->save();

                        // Thêm vào lịch sử tồn kho
                        LichSuTonKho::create([
                            'lo_id' => $loThuoc->lo_id,
                            'thuoc_id' => $thuocId,
                            'kho_id' => $loThuoc->kho_id,
                            'don_ban_le_id' => $donBanLe->don_id,
                            'chi_tiet_don_id' => $chiTiet->chi_tiet_id,
                            'so_luong_thay_doi' => -$soLuongLayTuLo,
                            'ton_kho_moi' => $loThuoc->ton_kho_hien_tai,
                            'nguoi_dung_id' => Auth::id(),
                            'loai_thay_doi' => 'ban',
                            'mo_ta' => 'Bán thuốc: ' . $thuoc->ten_thuoc . ' - Lô: ' . $loThuoc->ma_lo . ' - Đơn: ' . $donBanLe->ma_don
                        ]);

                        // Cập nhật số lượng còn lại cần lấy
                        $soLuongConLai -= $soLuongLayTuLo;
                    }
                } else {
                    // Nếu lo_id là một giá trị thực, kiểm tra xem nó có tồn tại không
                    if (!isset($item['lo_id']) || !is_numeric($item['lo_id'])) {
                        throw new Exception('Không có thông tin lô thuốc hợp lệ cho sản phẩm ' . $thuoc->ten_thuoc);
                    }

                    $loThuoc = LoThuoc::find($item['lo_id']);
                    if (!$loThuoc) {
                        throw new Exception('Không tìm thấy lô thuốc cho sản phẩm ' . $thuoc->ten_thuoc);
                    }

                    // Kiểm tra nếu lô được chỉ định không đủ, sẽ lấy thêm từ lô khác
                    if ($loThuoc->ton_kho_hien_tai < $soLuongQuyDoiDonViGoc) {
                        // Lấy tất cả các lô còn hạn sử dụng và có tồn kho, sắp xếp theo ngày hết hạn
                        $cacLoThuoc = $thuoc->getLoThuocConHang()->sortBy('han_su_dung');

                        $tongTonKho = $cacLoThuoc->sum('ton_kho_hien_tai');

                        if ($tongTonKho < $soLuongQuyDoiDonViGoc) {
                            throw new Exception('Tổng số lượng tồn kho không đủ cho sản phẩm ' . $thuoc->ten_thuoc . ' (Yêu cầu: ' . $soLuongQuyDoiDonViGoc . ', Tồn kho: ' . $tongTonKho . ')');
                        }

                        $soLuongConLai = $soLuongQuyDoiDonViGoc;

                        // Ưu tiên lấy từ lô được chỉ định trước
                        $soLuongLayTuLo = min($loThuoc->ton_kho_hien_tai, $soLuongConLai);

                        $soLuongHienThi = $soLuongLayTuLo;
                        if ($isDonViBan && $thuoc->don_vi_ban != $thuoc->don_vi_goc && $heSoQuyDoi > 0) {
                            $soLuongHienThi = $soLuongLayTuLo / $heSoQuyDoi;
                        }

                        $thanhTien = $item['thanh_tien'] * ($soLuongHienThi / $soLuongCanBan);
                        $tienThue = ($item['tien_thue'] ?? 0) * ($soLuongHienThi / $soLuongCanBan);

                        $chiTiet = ChiTietDonBanLe::create([
                            'don_id' => $donBanLe->don_id,
                            'lo_id' => $loThuoc->lo_id,
                            'don_vi' => $donViItem === 'don_vi_goc' ? 0 : 1, // Chuyển thành 0 hoặc 1
                            'so_luong' => $soLuongHienThi,
                            'gia_ban' => $item['gia_ban'],
                            'thue_suat' => $item['thue_suat'] ?? 0,
                            'tien_thue' => $tienThue,
                            'thanh_tien' => $thanhTien,
                        ]);

                        // Cập nhật tồn kho
                        $loThuoc->ton_kho_hien_tai -= $soLuongLayTuLo;
                        $loThuoc->save();

                        // Thêm vào lịch sử tồn kho
                        LichSuTonKho::create([
                            'lo_id' => $loThuoc->lo_id,
                            'thuoc_id' => $thuocId,
                            'kho_id' => $loThuoc->kho_id,
                            'don_ban_le_id' => $donBanLe->don_id,
                            'chi_tiet_don_id' => $chiTiet->chi_tiet_id,
                            'so_luong_thay_doi' => -$soLuongLayTuLo,
                            'ton_kho_moi' => $loThuoc->ton_kho_hien_tai,
                            'nguoi_dung_id' => Auth::id(),
                            'loai_thay_doi' => 'ban',
                            'mo_ta' => 'Bán thuốc: ' . $thuoc->ten_thuoc . ' - Lô: ' . $loThuoc->ma_lo . ' - Đơn: ' . $donBanLe->ma_don
                        ]);

                        // Cập nhật số lượng còn lại
                        $soLuongConLai -= $soLuongLayTuLo;

                        // Nếu vẫn cần lấy thêm từ các lô khác
                        if ($soLuongConLai > 0) {
                            // Duyệt qua các lô khác theo thứ tự cũ nhất đến mới nhất (ngoại trừ lô đã lấy)
                            foreach ($cacLoThuoc as $lo) {
                                if ($lo->lo_id === $loThuoc->lo_id) continue;
                                if ($soLuongConLai <= 0) break;

                                $soLuongLayTuLo = min($lo->ton_kho_hien_tai, $soLuongConLai);

                                $soLuongHienThi = $soLuongLayTuLo;
                                if ($isDonViBan && $thuoc->don_vi_ban != $thuoc->don_vi_goc && $heSoQuyDoi > 0) {
                                    $soLuongHienThi = $soLuongLayTuLo / $heSoQuyDoi;
                                }

                                $thanhTien = $item['thanh_tien'] * ($soLuongHienThi / $soLuongCanBan);
                                $tienThue = ($item['tien_thue'] ?? 0) * ($soLuongHienThi / $soLuongCanBan);

                                $chiTiet = ChiTietDonBanLe::create([
                                    'don_id' => $donBanLe->don_id,
                                    'lo_id' => $lo->lo_id,
                                    'don_vi' => $donViItem === 'don_vi_goc' ? 0 : 1, // Chuyển thành 0 hoặc 1
                                    'so_luong' => $soLuongHienThi,
                                    'gia_ban' => $item['gia_ban'],
                                    'thue_suat' => $item['thue_suat'] ?? 0,
                                    'tien_thue' => $tienThue,
                                    'thanh_tien' => $thanhTien,
                                ]);

                                // Cập nhật tồn kho
                                $lo->ton_kho_hien_tai -= $soLuongLayTuLo;
                                $lo->save();

                                // Thêm vào lịch sử tồn kho
                                LichSuTonKho::create([
                                    'lo_id' => $lo->lo_id,
                                    'thuoc_id' => $thuocId,
                                    'kho_id' => $lo->kho_id,
                                    'don_ban_le_id' => $donBanLe->don_id,
                                    'chi_tiet_don_id' => $chiTiet->chi_tiet_id,
                                    'so_luong_thay_doi' => -$soLuongLayTuLo,
                                    'ton_kho_moi' => $lo->ton_kho_hien_tai,
                                    'nguoi_dung_id' => Auth::id(),
                                    'loai_thay_doi' => 'ban',
                                    'mo_ta' => 'Bán thuốc: ' . $thuoc->ten_thuoc . ' - Lô: ' . $lo->ma_lo . ' - Đơn: ' . $donBanLe->ma_don
                                ]);

                                $soLuongConLai -= $soLuongLayTuLo;
                            }
                        }
                    } else {
                        // Lô đủ số lượng
                        $soLuongHienThi = $soLuongCanBan;
                        $thanhTien = $item['thanh_tien']; // Sử dụng giá trị từ request
                        $tienThue = $item['tien_thue'] ?? 0;

                        $chiTiet = ChiTietDonBanLe::create([
                            'don_id' => $donBanLe->don_id,
                            'lo_id' => $loThuoc->lo_id,
                            'don_vi' => $donViItem === 'don_vi_goc' ? 0 : 1, // Chuyển thành 0 hoặc 1
                            'so_luong' => $soLuongHienThi,
                            'gia_ban' => $item['gia_ban'],
                            'thue_suat' => $item['thue_suat'] ?? 0,
                            'tien_thue' => $tienThue,
                            'thanh_tien' => $thanhTien,
                        ]);

                        // Cập nhật tồn kho
                        $loThuoc->ton_kho_hien_tai -= $soLuongQuyDoiDonViGoc;
                        $loThuoc->save();

                        // Thêm vào lịch sử tồn kho
                        LichSuTonKho::create([
                            'lo_id' => $loThuoc->lo_id,
                            'thuoc_id' => $thuocId,
                            'kho_id' => $loThuoc->kho_id,
                            'don_ban_le_id' => $donBanLe->don_id,
                            'chi_tiet_don_id' => $chiTiet->chi_tiet_id,
                            'so_luong_thay_doi' => -$soLuongQuyDoiDonViGoc,
                            'ton_kho_moi' => $loThuoc->ton_kho_hien_tai,
                            'nguoi_dung_id' => Auth::id(),
                            'loai_thay_doi' => 'ban',
                            'mo_ta' => 'Bán thuốc: ' . $thuoc->ten_thuoc . ' - Lô: ' . $loThuoc->ma_lo . ' - Đơn: ' . $donBanLe->ma_don
                        ]);
                    }
                }
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
