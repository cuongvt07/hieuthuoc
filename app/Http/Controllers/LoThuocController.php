<?php

namespace App\Http\Controllers;

use App\Models\ChiTietLoNhap;
use App\Models\LoThuoc;
use App\Models\Kho;
use App\Models\Thuoc;
use App\Models\PhieuNhap;
use App\Models\NhaCungCap;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class LoThuocController extends Controller
{
    /**
     * Hiển thị danh sách lô thuốc
     */
    public function index(Request $request)
    {
        // Eager load related thuoc, kho and any 'dieu_chinh' history (hủy tồn) ordered by newest
        $query = LoThuoc::with([
            'thuoc', 
            'kho', 
            'lichSuTonKho' => function($q) {
                $q->where('loai_thay_doi', 'dieu_chinh')->orderBy('created_at', 'desc');
            }
        ]);

        // Lọc theo thuốc
        if ($request->has('thuoc_id') && $request->thuoc_id != '') {
            $query->where('thuoc_id', $request->thuoc_id);
        }

        // Lọc theo kho
        if ($request->has('kho_id') && $request->kho_id != '') {
            $query->where('kho_id', $request->kho_id);
        }

        // Lọc theo tồn kho
        if ($request->has('con_ton_kho') && $request->con_ton_kho == '1') {
            $query->where('ton_kho_hien_tai', '>', 0);
        }


        // Lọc theo sắp hết hạn (<= 30 ngày)
        if ($request->has('sap_het_han') && $request->sap_het_han == '1') {
            $today = date('Y-m-d');
            $thirtyDaysLater = date('Y-m-d', strtotime('+30 days'));
            $query->whereBetween('han_su_dung', [$today, $thirtyDaysLater]);
        }

        // Lọc hết hạn (chưa hủy):
        // - Lô hết hạn (han_su_dung < hôm nay)
        // - Còn tồn kho (ton_kho_hien_tai > 0)
        // - Trong lịch sử tồn kho CHƯA TỪNG có bất kỳ bản ghi nào của lô đó có loai_thay_doi = 'dieu_chinh'
        if ($request->has('het_han_chua_huy') && $request->het_han_chua_huy == '1') {
            $today = date('Y-m-d');
            $query->where('han_su_dung', '<', $today)
                  ->where('ton_kho_hien_tai', '>', 0)
                  ->whereDoesntHave('lichSuTonKho', function($q) {
                      $q->where('loai_thay_doi', 'dieu_chinh');
                  });
        }

        // Lọc hết hạn (đã hủy): hết hạn và tồn kho = 0
        if ($request->has('het_han_da_huy') && $request->het_han_da_huy == '1') {
            $today = date('Y-m-d');
            $query->where('han_su_dung', '<', $today)
                  ->where('ton_kho_hien_tai', '<=', 0);
        }

        // Lọc hết hạn (tổng hợp, nếu vẫn còn dùng filter cũ)
        if ($request->has('het_han') && $request->het_han == '1') {
            $today = date('Y-m-d');
            $query->where('han_su_dung', '<', $today);
        }

        // Lọc theo từ khóa (mã lô, số lô nhà sản xuất, ghi chú)
        if ($request->has('keyword') && $request->keyword != '') {
            $keyword = $request->keyword;
            $query->where(function($q) use ($keyword) {
                $q->where('ma_lo', 'like', "%{$keyword}%")
                  ->orWhere('so_lo_nha_san_xuat', 'like', "%{$keyword}%")
                  ->orWhere('ghi_chu', 'like', "%{$keyword}%");
            });
        }

        // Sắp xếp kết quả
        $sortField = $request->input('sort_by', 'han_su_dung');
        $sortDirection = $request->input('sort_direction', 'asc');
        
        $query->orderBy($sortField, $sortDirection);

        $loThuocs = $query->paginate(10);
        $thuocs = Thuoc::orderBy('ten_thuoc')->get();
        $khos = Kho::orderBy('ten_kho')->get();

        return view('lo-thuoc.index', compact('loThuocs', 'thuocs', 'khos'));
    }

    /**
     * Hiển thị chi tiết lô thuốc
     */
    public function show(LoThuoc $loThuoc)
    {
        // Lấy lô thuốc với các mối quan hệ
        $loThuoc->load(['thuoc', 'kho', 'chiTietLoNhap.phieuNhap.nhaCungCap']);

        // Lấy thông tin phiếu nhập của lô này
        $phieuNhaps = $loThuoc->chiTietLoNhap->map(function ($chiTiet) {
            return $chiTiet->phieuNhap;
        })->unique('phieu_id')->values();
        
        // Lấy tất cả kho cho trang chuyển kho
        $khos = Kho::where('kho_id', '!=', $loThuoc->kho_id)->orderBy('ten_kho')->get();

        return view('lo-thuoc.show', compact('loThuoc', 'phieuNhaps', 'khos'));
    }

    /**
     * Hiển thị form tạo lô thuốc mới
     */
    public function create()
    {
        $thuocs = Thuoc::orderBy('ten_thuoc')->get();
        $khos = Kho::orderBy('ten_kho')->get();
        return view('lo-thuoc.create', compact('thuocs', 'khos'));
    }

    /**
     * Lưu lô thuốc mới
     */
    public function store(Request $request)
    {
        $request->validate([
            'ma_lo' => 'nullable|string|max:50',
            'so_lo_nha_san_xuat' => 'nullable|string|max:50',
            'ngay_san_xuat' => 'nullable|date|before:han_su_dung',
            'han_su_dung' => 'required|date',
            'so_luong' => 'required|numeric|min:0.01',
            'gia_nhap' => 'required|numeric|min:0',
            'ghi_chu' => 'nullable|string',
            'thuoc_id' => 'required|exists:thuoc,thuoc_id',
            'kho_id' => 'required|exists:kho,kho_id',
        ]);

        // Tạo mã lô nếu không nhập
        $maLo = $request->ma_lo;
        if (empty($maLo)) {
            $thuoc = Thuoc::find($request->thuoc_id);
            $today = date('ymd');
            $count = LoThuoc::whereDate('created_at', today())->count() + 1;
            $maLo = 'LT' . $today . str_pad($count, 3, '0', STR_PAD_LEFT);
        }

        DB::beginTransaction();

        try {
            // Tạo lô thuốc mới
            $loThuoc = new LoThuoc();
            $loThuoc->ma_lo = $maLo;
            $loThuoc->so_lo_nha_san_xuat = $request->so_lo_nha_san_xuat;
            $loThuoc->ngay_san_xuat = $request->ngay_san_xuat;
            $loThuoc->han_su_dung = $request->han_su_dung;
            $loThuoc->ghi_chu = $request->ghi_chu;
            $loThuoc->thuoc_id = $request->thuoc_id;
            $loThuoc->kho_id = $request->kho_id;
            $loThuoc->tong_so_luong = $request->so_luong;
            $loThuoc->ton_kho_hien_tai = $request->so_luong;
            $loThuoc->gia_nhap_tb = $request->gia_nhap;

            $loThuoc->save();

            DB::commit();

            return redirect()->route('lo-thuoc.show', $loThuoc->lo_id)
                ->with('success', 'Tạo lô thuốc mới thành công.');
        } catch (\Exception $e) {
            DB::rollBack();
            return redirect()->back()
                ->withInput()
                ->with('error', 'Đã xảy ra lỗi khi tạo lô thuốc: ' . $e->getMessage());
        }
    }

    /**
     * Hiển thị form hủy tồn hết hạn
     */
    public function dispose(LoThuoc $loThuoc)
    {
        // Kiểm tra xem lô có tồn kho không
        if ($loThuoc->ton_kho_hien_tai <= 0) {
            return redirect()->route('lo-thuoc.show', $loThuoc->lo_id)
                ->with('error', 'Lô thuốc này không còn tồn kho để hủy.');
        }

        return view('lo-thuoc.edit', compact('loThuoc'));
    }

    /**
     * Xử lý hủy tồn hết hạn
     */
    public function processDispose(Request $request, LoThuoc $loThuoc)
    {
        $request->validate([
            'ngay_huy' => 'required|date|before_or_equal:today',
            'ly_do_huy' => 'required|string|max:500',
        ], [
            'ngay_huy.required' => 'Vui lòng nhập ngày hủy',
            'ngay_huy.before_or_equal' => 'Ngày hủy không thể là ngày trong tương lai',
            'ly_do_huy.required' => 'Vui lòng nhập lý do hủy',
        ]);

        // Kiểm tra xem lô có tồn kho không
        if ($loThuoc->ton_kho_hien_tai <= 0) {
            return redirect()->route('lo-thuoc.show', $loThuoc->lo_id)
                ->with('error', 'Lô thuốc này không còn tồn kho để hủy.');
        }

        DB::beginTransaction();

        try {
            $soLuongHuy = $loThuoc->ton_kho_hien_tai;
            
            // Tạo lịch sử tồn kho với loại_thay_doi = 'dieu_chinh'
            \App\Models\LichSuTonKho::create([
                'lo_id' => $loThuoc->lo_id,
                'thuoc_id' => $loThuoc->thuoc_id,
                'so_luong_thay_doi' => -$soLuongHuy,
                'ton_kho_moi' => 0,
                'nguoi_dung_id' => auth()->id(),
                'loai_thay_doi' => 'dieu_chinh',
                'mo_ta' => 'Hủy tồn hết hạn - ' . $request->ly_do_huy . ' (Ngày hủy: ' . \Carbon\Carbon::parse($request->ngay_huy)->format('d/m/Y') . ')',
            ]);

            // Cập nhật tồn kho về 0
            $loThuoc->ton_kho_hien_tai = 0;
            $loThuoc->ghi_chu = ($loThuoc->ghi_chu ? $loThuoc->ghi_chu . "\n" : '') . 
                                '[' . now()->format('d/m/Y H:i') . '] Đã hủy tồn: ' . $request->ly_do_huy;
            $loThuoc->save();

            DB::commit();

            return redirect()->route('lo-thuoc.show', $loThuoc->lo_id)
                ->with('success', 'Đã hủy tồn thành công ' . number_format($soLuongHuy, 2) . ' ' . $loThuoc->thuoc->don_vi_goc . ' của lô ' . $loThuoc->ma_lo);

        } catch (\Exception $e) {
            DB::rollBack();
            return redirect()->back()
                ->with('error', 'Có lỗi xảy ra: ' . $e->getMessage())
                ->withInput();
        }
    }

    /**
     * Điều chỉnh số lượng tồn kho
     */
    public function adjustStock(Request $request, LoThuoc $loThuoc)
    {
        $request->validate([
            'adjustment_type' => 'required|in:increase,decrease',
            'adjustment_amount' => 'required|numeric|min:0.01',
            'reason' => 'required|string|max:255',
        ]);

        $oldStock = $loThuoc->ton_kho_hien_tai;
        $adjustmentAmount = $request->adjustment_amount;
        $adjustmentType = $request->adjustment_type;
        
        if ($adjustmentType == 'increase') {
            $loThuoc->ton_kho_hien_tai += $adjustmentAmount;
            $loThuoc->tong_so_luong += $adjustmentAmount;
        } else {
            if ($loThuoc->ton_kho_hien_tai < $adjustmentAmount) {
                return redirect()->back()
                    ->withInput()
                    ->with('error', 'Số lượng giảm không thể lớn hơn tồn kho hiện tại.');
            }
            $loThuoc->ton_kho_hien_tai -= $adjustmentAmount;
        }

        // Lưu lịch sử điều chỉnh (nếu có bảng lịch sử riêng)
        // StockAdjustmentHistory::create([...])

        $loThuoc->save();

        $message = $adjustmentType == 'increase' 
            ? "Đã tăng số lượng tồn kho thêm $adjustmentAmount đơn vị." 
            : "Đã giảm số lượng tồn kho $adjustmentAmount đơn vị.";

        return redirect()->route('lo-thuoc.show', $loThuoc->lo_id)
            ->with('success', $message);
    }

    /**
     * Chuyển lô thuốc sang kho khác
     */
    public function transfer(Request $request, LoThuoc $loThuoc)
    {
        $request->validate([
            'target_kho_id' => 'required|exists:kho,kho_id|different:source_kho_id',
            'transfer_amount' => 'required|numeric|min:0.01|lte:' . $loThuoc->ton_kho_hien_tai,
            'source_kho_id' => 'required',
        ], [
            'target_kho_id.different' => 'Kho đích phải khác kho nguồn',
            'transfer_amount.lte' => 'Số lượng chuyển không thể lớn hơn tồn kho hiện tại',
        ]);

        $sourceKhoId = $loThuoc->kho_id;
        $targetKhoId = $request->target_kho_id;
        $transferAmount = $request->transfer_amount;

        DB::beginTransaction();

        try {
            // Trường hợp chuyển toàn bộ lô
            if ($transferAmount == $loThuoc->ton_kho_hien_tai) {
                $loThuoc->kho_id = $targetKhoId;
                $loThuoc->save();
            } 
            // Trường hợp chỉ chuyển một phần
            else {
                // Giảm số lượng ở lô hiện tại
                $loThuoc->ton_kho_hien_tai -= $transferAmount;
                $loThuoc->save();

                // Tìm hoặc tạo lô mới ở kho đích
                $targetLot = LoThuoc::firstOrNew([
                    'thuoc_id' => $loThuoc->thuoc_id,
                    'kho_id' => $targetKhoId,
                    'ma_lo' => $loThuoc->ma_lo,
                    'so_lo_nha_san_xuat' => $loThuoc->so_lo_nha_san_xuat,
                    'han_su_dung' => $loThuoc->han_su_dung,
                ]);

                if (!$targetLot->exists) {
                    $targetLot->ngay_san_xuat = $loThuoc->ngay_san_xuat;
                    $targetLot->gia_nhap_tb = $loThuoc->gia_nhap_tb;
                    $targetLot->ghi_chu = $loThuoc->ghi_chu;
                    $targetLot->tong_so_luong = $transferAmount;
                    $targetLot->ton_kho_hien_tai = $transferAmount;
                } else {
                    $targetLot->ton_kho_hien_tai += $transferAmount;
                    $targetLot->tong_so_luong += $transferAmount;
                }

                $targetLot->save();
            }

            DB::commit();

            return redirect()->route('lo-thuoc.index')
                ->with('success', 'Chuyển lô thuốc thành công.');
        } catch (\Exception $e) {
            DB::rollBack();
            return redirect()->back()
                ->withInput()
                ->with('error', 'Đã xảy ra lỗi khi chuyển lô thuốc: ' . $e->getMessage());
        }
    }
}
