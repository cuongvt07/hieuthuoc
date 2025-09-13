<?php

namespace App\Http\Controllers;

use App\Models\LoThuoc;
use App\Models\NhaCungCap;
use App\Models\PhieuNhap;
use App\Models\Thuoc;
use App\Models\ChiTietLoNhap;
use App\Models\Kho;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class PhieuNhapController extends Controller
{
    /**
     * Hiển thị danh sách phiếu nhập
     */
    public function index(Request $request)
    {
        // Get all receipts for the tree view
        $allPhieuNhaps = PhieuNhap::with(['nhaCungCap', 'nguoiDung'])
            ->orderBy('ngay_nhap', 'desc')
            ->get();
            
        $query = PhieuNhap::with(['nhaCungCap', 'nguoiDung'])
            ->orderBy('ngay_nhap', 'desc');

        // Lọc theo nhà cung cấp
        if ($request->has('ncc_id') && $request->ncc_id != '') {
            $query->where('ncc_id', $request->ncc_id);
        }

        // Lọc theo trạng thái
        if ($request->has('trang_thai') && $request->trang_thai != '') {
            $query->where('trang_thai', $request->trang_thai);
        }

        // Lọc theo khoảng thời gian
        if ($request->has('tu_ngay') && $request->tu_ngay != '') {
            $query->where('ngay_nhap', '>=', $request->tu_ngay);
        }

        if ($request->has('den_ngay') && $request->den_ngay != '') {
            $query->where('ngay_nhap', '<=', $request->den_ngay);
        }

        // Lọc theo từ khóa (mã phiếu, ghi chú)
        if ($request->has('keyword') && $request->keyword != '') {
            $keyword = $request->keyword;
            $query->where(function($q) use ($keyword) {
                $q->where('ma_phieu', 'like', "%{$keyword}%")
                  ->orWhere('ghi_chu', 'like', "%{$keyword}%");
            });
        }

        $phieuNhaps = $query->paginate(10);
        $nhaCungCaps = NhaCungCap::orderBy('ten_ncc')->get();

        return view('phieu-nhap.index', compact('phieuNhaps', 'nhaCungCaps', 'allPhieuNhaps'));
    }

    /**
     * Hiển thị form tạo phiếu nhập mới
     */
    public function create()
    {
        // Lấy dữ liệu cần thiết
        $nhaCungCaps = NhaCungCap::orderBy('ten_ncc')->get();
        $thuocs = Thuoc::orderBy('ten_thuoc')->where('trang_thai', 0)->get();
        $khos = Kho::orderBy('ten_kho')->get();

        // Tạo mã phiếu tự động
        $lastPhieu = PhieuNhap::latest('phieu_id')->first();
        $lastId = $lastPhieu ? $lastPhieu->phieu_id : 0;
        $nextId = $lastId + 1;
        $maPhieu = 'PN' . date('Ymd') . str_pad($nextId, 5, '0', STR_PAD_LEFT);

        return view('phieu-nhap.create', compact('nhaCungCaps', 'thuocs', 'khos', 'maPhieu'));
    }

    /**
     * Lưu phiếu nhập mới
     */
    public function store(Request $request)
    {
        $request->validate([
            'ma_phieu' => 'required|string|max:20|unique:phieu_nhap,ma_phieu',
            'ncc_id' => 'required|exists:nha_cung_cap,ncc_id',
            'ngay_nhap' => 'required|date',
            'ngay_chung_tu' => 'required|date',
            'tong_tien' => 'required|numeric|min:0',
            'vat' => 'required|numeric|min:0',
            'tong_cong' => 'required|numeric|min:0',
            'ghi_chu' => 'nullable|string',
            // Validate các trường thuốc
            'thuoc_id' => 'required|array',
            'thuoc_id.*' => 'required|exists:thuoc,thuoc_id',
            'kho_id' => 'required|array',
            'kho_id.*' => 'required|exists:kho,kho_id',
            'is_new_lot' => 'required|array',
            'lo_id' => 'required|array',
            'so_lo' => 'required|array',
            'so_lo.*' => 'nullable|string',
            'so_luong' => 'required|array',
            'so_luong.*' => 'required|numeric|min:0.01',
            'don_vi' => 'required|array',
            'gia_nhap' => 'required|array',
            'gia_nhap.*' => 'required|numeric|min:0',
            'thue_suat' => 'required|array',
            'thue_suat.*' => 'required|numeric|min:0',
            'tien_thue' => 'required|array',
            'tien_thue.*' => 'required|numeric|min:0',
            'thanh_tien' => 'required|array',
            'thanh_tien.*' => 'required|numeric|min:0',
            'han_su_dung' => 'required|array',
            'han_su_dung.*' => 'required|date',
            'ngay_san_xuat' => 'required|array',
            'ngay_san_xuat.*' => 'nullable|date|before:han_su_dung.*',
            'so_lo_nha_san_xuat' => 'nullable|array',
            'so_lo_nha_san_xuat.*' => 'nullable|string',
            'ghi_chu_lo' => 'nullable|array',
            'ghi_chu_lo.*' => 'nullable|string',
        ], [
            'ma_phieu.required' => 'Vui lòng nhập mã phiếu',
            'ma_phieu.unique' => 'Mã phiếu đã tồn tại',
            'ncc_id.required' => 'Vui lòng chọn nhà cung cấp',
            'ngay_nhap.required' => 'Vui lòng chọn ngày nhập',
            'ngay_chung_tu.required' => 'Vui lòng chọn ngày chứng từ',
            'thuoc_id.required' => 'Vui lòng chọn ít nhất một thuốc',
            'so_luong.*.required' => 'Vui lòng nhập số lượng',
            'gia_nhap.*.required' => 'Vui lòng nhập giá nhập',
            'han_su_dung.*.required' => 'Vui lòng nhập hạn sử dụng',
            'ngay_san_xuat.*.before' => 'Ngày sản xuất phải trước hạn sử dụng',
        ]);

        DB::beginTransaction();

        try {
            // Lưu thông tin phiếu nhập
            $phieuNhap = new PhieuNhap();
            $phieuNhap->ma_phieu = $request->ma_phieu;
            $phieuNhap->ncc_id = $request->ncc_id;
            $phieuNhap->ngay_nhap = $request->ngay_nhap;
            $phieuNhap->ngay_chung_tu = $request->ngay_chung_tu;
            $phieuNhap->nguoi_dung_id = Auth::id();
            $phieuNhap->tong_tien = $request->tong_tien;
            $phieuNhap->vat = $request->vat;
            $phieuNhap->tong_cong = $request->tong_cong;
            $phieuNhap->ghi_chu = $request->ghi_chu;
            $phieuNhap->trang_thai = 'cho_xu_ly'; // Mặc định là chờ xử lý
            $phieuNhap->save();

            // Lưu chi tiết lô nhập
            for ($i = 0; $i < count($request->thuoc_id); $i++) {
                $thuocId = $request->thuoc_id[$i];
                $khoId = $request->kho_id[$i];
                $isNewLot = $request->is_new_lot[$i] == '1';
                $loId = $request->lo_id[$i];
                $soLo = $request->so_lo[$i];
                $soLoNSX = $request->so_lo_nha_san_xuat[$i] ?? null;
                $hanSuDung = $request->han_su_dung[$i];
                $ngaySX = $request->ngay_san_xuat[$i] ?? null;
                $soLuong = $request->so_luong[$i];
                $donVi = $request->don_vi[$i];
                $giaNhap = $request->gia_nhap[$i];
                $thueSuat = $request->thue_suat[$i];
                $tienThue = $request->tien_thue[$i];
                $thanhTien = $request->thanh_tien[$i];
                $ghiChuLo = $request->ghi_chu_lo[$i] ?? null;

                // Xử lý lô thuốc dựa vào isNewLot
                if ($isNewLot) {
                    // Tạo lô mới
                    // Tạo mã lô tự động nếu không nhập
                    if (empty($soLo)) {
                        $soLo = 'LT' . date('Ymd') . rand(1000, 9999);
                    }

                    $loThuoc = new LoThuoc();
                    $loThuoc->ma_lo = $soLo;
                    $loThuoc->thuoc_id = $thuocId;
                    $loThuoc->kho_id = $khoId;
                    $loThuoc->han_su_dung = $hanSuDung;
                    $loThuoc->ngay_san_xuat = $ngaySX;
                    $loThuoc->tong_so_luong = $soLuong;
                    $loThuoc->ton_kho_hien_tai = $soLuong;
                    $loThuoc->gia_nhap_tb = $giaNhap;
                    $loThuoc->so_lo_nha_san_xuat = $soLoNSX;
                    $loThuoc->ghi_chu = $ghiChuLo;
                    $loThuoc->save();
                } else {
                    // Cập nhật lô hiện có
                    $loThuoc = LoThuoc::findOrFail($loId);
                    
                    // Tính giá nhập trung bình mới
                    $tongSoLuongCu = $loThuoc->tong_so_luong;
                    $giaNhapTBCu = $loThuoc->gia_nhap_tb;
                    
                    // Tính giá nhập trung bình mới
                    $tongGiaTriCu = $tongSoLuongCu * $giaNhapTBCu;
                    $tongGiaTriMoi = $soLuong * $giaNhap;
                    $tongSoLuongMoi = $tongSoLuongCu + $soLuong;
                    $giaNhapTBMoi = ($tongGiaTriCu + $tongGiaTriMoi) / $tongSoLuongMoi;
                    
                    $loThuoc->tong_so_luong = $tongSoLuongMoi;
                    $loThuoc->ton_kho_hien_tai += $soLuong;
                    $loThuoc->gia_nhap_tb = $giaNhapTBMoi;
                    $loThuoc->save();
                }

                // Lưu chi tiết lô nhập
                $chiTietLoNhap = new ChiTietLoNhap();
                $chiTietLoNhap->phieu_id = $phieuNhap->phieu_id;
                $chiTietLoNhap->lo_id = $loThuoc->lo_id;
                $chiTietLoNhap->don_vi = $donVi;
                $chiTietLoNhap->so_luong = $soLuong;
                $chiTietLoNhap->gia_nhap = $giaNhap;
                $chiTietLoNhap->thue_suat = $thueSuat;
                $chiTietLoNhap->tien_thue = $tienThue;
                $chiTietLoNhap->thanh_tien = $thanhTien;
                $chiTietLoNhap->han_su_dung = $hanSuDung;
                $chiTietLoNhap->save();
            }

            DB::commit();

            return redirect()->route('phieu-nhap.show', $phieuNhap->phieu_id)
                ->with('success', 'Phiếu nhập đã được tạo thành công.');
        } catch (\Exception $e) {
            DB::rollBack();
            return redirect()->back()
                ->withInput()
                ->with('error', 'Đã xảy ra lỗi khi lưu phiếu nhập: ' . $e->getMessage());
        }
    }

    /**
     * Hiển thị chi tiết phiếu nhập
     */
    public function show(PhieuNhap $phieuNhap)
    {
        $phieuNhap->load(['nhaCungCap', 'nguoiDung', 'chiTietLoNhaps.loThuoc.thuoc']);
        return view('phieu-nhap.show', compact('phieuNhap'));
    }

    /**
     * Chỉnh sửa phiếu nhập
     * Chỉ cho phép chỉnh sửa các thông tin cơ bản, không cho phép sửa chi tiết lô
     */
    public function edit(PhieuNhap $phieuNhap)
    {
        // Chỉ cho phép sửa phiếu nhập trong trạng thái nháp
        if ($phieuNhap->trang_thai !== 'nháp') {
            return redirect()->route('phieu-nhap.show', $phieuNhap->phieu_id)
                ->with('error', 'Không thể chỉnh sửa phiếu nhập đã hoàn thành.');
        }

        $nhaCungCaps = NhaCungCap::orderBy('ten_ncc')->get();
        return view('phieu-nhap.edit', compact('phieuNhap', 'nhaCungCaps'));
    }

    /**
     * Cập nhật phiếu nhập
     */
    public function update(Request $request, PhieuNhap $phieuNhap)
    {
        // Chỉ cho phép sửa phiếu nhập trong trạng thái nháp
        if ($phieuNhap->trang_thai !== 'nháp') {
            return redirect()->route('phieu-nhap.show', $phieuNhap->phieu_id)
                ->with('error', 'Không thể chỉnh sửa phiếu nhập đã hoàn thành.');
        }

        $request->validate([
            'ma_phieu' => 'required|string|max:20|unique:phieu_nhap,ma_phieu,' . $phieuNhap->phieu_id . ',phieu_id',
            'ncc_id' => 'required|exists:nha_cung_cap,ncc_id',
            'ngay_nhap' => 'required|date',
            'ngay_chung_tu' => 'required|date',
            'ghi_chu' => 'nullable|string',
        ]);

        $phieuNhap->ma_phieu = $request->ma_phieu;
        $phieuNhap->ncc_id = $request->ncc_id;
        $phieuNhap->ngay_nhap = $request->ngay_nhap;
        $phieuNhap->ngay_chung_tu = $request->ngay_chung_tu;
        $phieuNhap->ghi_chu = $request->ghi_chu;
        $phieuNhap->save();

        return redirect()->route('phieu-nhap.show', $phieuNhap->phieu_id)
            ->with('success', 'Phiếu nhập đã được cập nhật thành công.');
    }

    /**
     * Hủy phiếu nhập
     */
    public function destroy(PhieuNhap $phieuNhap)
    {
        if ($phieuNhap->trang_thai === 'hoan_tat') {
            return redirect()->route('phieu-nhap.index')
                ->with('error', 'Không thể hủy phiếu nhập đã hoàn thành.');
        }

        $phieuNhap->delete();
        return redirect()->route('phieu-nhap.index')
            ->with('success', 'Phiếu nhập đã được hủy thành công.');
    }

    /**
     * API để lấy thông tin tồn kho của thuốc
     */
    public function getTonKho(Request $request)
    {
        $getAllLots = $request->has('all_lots');
        
        if ($getAllLots) {
            // Lấy tất cả các lô có tồn kho > 0
            $tonKho = LoThuoc::with(['thuoc', 'kho'])
                ->where('han_su_dung', '>', now()) // Chỉ lấy lô còn hạn sử dụng
                ->orderBy('han_su_dung', 'asc')
                ->get();
                
            return response()->json([
                'tonKho' => $tonKho
            ]);
        } else {
            // Lấy lô theo thuốc và kho cụ thể
            $thuocId = $request->thuoc_id;
            $khoId = $request->kho_id;
            
            if (!$thuocId || !$khoId) {
                return response()->json([
                    'error' => 'Thiếu thông tin thuốc hoặc kho'
                ], 400);
            }
            
            $thuoc = Thuoc::find($thuocId);
            
            if (!$thuoc) {
                return response()->json([
                    'error' => 'Không tìm thấy thuốc'
                ], 404);
            }

            // Lấy tất cả các lô có tồn kho > 0 của thuốc trong kho này
            $tonKho = LoThuoc::where('thuoc_id', $thuocId)
                ->where('kho_id', $khoId)
                ->where('ton_kho_hien_tai', '>', 0)
                ->orderBy('han_su_dung', 'asc')
                ->get();

            // Tính tổng tồn kho của tất cả các lô
            $tongTonKho = $tonKho->sum('ton_kho_hien_tai');
            
            return response()->json([
                'tonKho' => $tonKho,
                'thuoc' => $thuoc,
                'tongTonKho' => $tongTonKho
            ]);
        }
    }

    /**
     * API để lấy lịch sử nhập của một lô
     */
    public function getLotHistory(Request $request)
    {
        $loId = $request->lo_id;
        
        if (!$loId) {
            return response()->json([
                'error' => 'Thiếu thông tin lô'
            ], 400);
        }
        
        // Lấy lịch sử nhập của lô
        $history = ChiTietLoNhap::with(['phieuNhap' => function($query) {
            $query->select('phieu_id', 'ma_phieu', 'ngay_nhap');
        }])->where('lo_id', $loId)
           ->orderBy('phieu_id', 'desc')
           ->get();
        
        return response()->json([
            'history' => $history
        ]);
    }
    
    /**
     * API để lấy thông tin lô thuốc
     */
    public function getLoThuoc(Request $request)
    {
        $loId = $request->lo_id;
        $loThuoc = LoThuoc::with('thuoc')->find($loId);
        
        if (!$loThuoc) {
            return response()->json(['error' => 'Không tìm thấy lô thuốc'], 404);
        }
        
        return response()->json([
            'loThuoc' => $loThuoc
        ]);
    }
    
    /**
     * API để lấy thông tin phiếu nhập cho AJAX
     */
    public function getPhieuNhapInfo($id)
    {
        try {
            $phieuNhap = PhieuNhap::with([
                'nhaCungCap',
                'nguoiDung',
                'chiTietLoNhaps.loThuoc.thuoc',
                'chiTietLoNhaps.loThuoc.kho'
            ])->findOrFail($id);

            return response()->json([
                'success' => true,
                'phieuNhap' => $phieuNhap
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Không tìm thấy phiếu nhập hoặc có lỗi xảy ra: ' . $e->getMessage()
            ], 404);
        }
    }

    /**
     * Xác nhận hoàn thành phiếu nhập
     */
    public function complete($id)
    {
        try {
            $phieuNhap = PhieuNhap::findOrFail($id);

            // Kiểm tra trạng thái hiện tại
            if ($phieuNhap->trang_thai === 'hoan_tat') {
                return response()->json([
                    'success' => false,
                    'message' => 'Phiếu nhập này đã được hoàn thành trước đó.'
                ], 400);
            }

            if ($phieuNhap->trang_thai === 'huy') {
                return response()->json([
                    'success' => false,
                    'message' => 'Không thể hoàn thành phiếu nhập đã bị hủy.'
                ], 400);
            }

            // Cập nhật trạng thái phiếu nhập
            $phieuNhap->trang_thai = 'hoan_tat';
            $phieuNhap->save();

            return response()->json([
                'success' => true,
                'message' => 'Phiếu nhập đã được xác nhận hoàn thành thành công.'
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Có lỗi xảy ra khi xác nhận hoàn thành phiếu nhập: ' . $e->getMessage()
            ], 500);
        }
    }
}
