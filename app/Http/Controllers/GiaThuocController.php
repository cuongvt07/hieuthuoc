<?php

namespace App\Http\Controllers;

use App\Http\Requests\GiaThuocRequest;
use App\Models\GiaThuoc;
use App\Models\Thuoc;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Carbon\Carbon;

class GiaThuocController extends Controller
{
    /**
     * Hiển thị danh sách giá thuốc.
     */
    public function index(Request $request)
    {
        $query = GiaThuoc::with(['thuoc' => function ($q) {
            $q->select('thuoc_id', 'ten_thuoc', 'ma_thuoc');
        }])
            ->select(['gia_id', 'thuoc_id', 'gia_ban', 'ngay_bat_dau', 'ngay_ket_thuc', 'ngay_tao']);

        if ($request->has('thuoc_id') && $request->thuoc_id) {
            $query->where('thuoc_id', $request->thuoc_id);
        }

        if ($request->has('ngay_bat_dau') && $request->ngay_bat_dau) {
            $query->where('ngay_bat_dau', '>=', $request->ngay_bat_dau);
        }

        if ($request->has('ngay_ket_thuc') && $request->ngay_ket_thuc) {
            $query->where('ngay_bat_dau', '<=', $request->ngay_ket_thuc);
        }

        $giaThuoc = $query->orderBy('thuoc_id')
            ->orderBy('ngay_bat_dau', 'desc')
            ->paginate(10);

        $now = now();

        // 🟢 Lấy bản ghi "đang hiệu lực" và mới nhất cho mỗi thuốc
        $activeGiaByThuoc = GiaThuoc::select('thuoc_id', 'gia_id', 'ngay_bat_dau', 'ngay_ket_thuc')
            ->where('ngay_bat_dau', '<=', $now)
            ->where(function ($q) use ($now) {
                $q->whereNull('ngay_ket_thuc')->orWhere('ngay_ket_thuc', '>=', $now);
            })
            ->orderBy('ngay_bat_dau', 'desc')
            ->get()
            ->groupBy('thuoc_id')
            ->map(function ($group) {
                // Lấy bản ghi có ngày bắt đầu mới nhất
                return $group->sortByDesc('ngay_bat_dau')->first();
            });

        // 🟡 Lấy các bản ghi "chưa hiệu lực" (ngày bắt đầu trong tương lai)
        $futureGiaByThuoc = GiaThuoc::select('thuoc_id', 'gia_id', 'ngay_bat_dau')
            ->where('ngay_bat_dau', '>', $now)
            ->orderBy('ngay_bat_dau', 'asc')
            ->get()
            ->groupBy('thuoc_id')
            ->map(function ($group) {
                return $group->sortBy('ngay_bat_dau')->first(); // gần nhất trong tương lai
            });

        $thuoc = \App\Models\Thuoc::orderBy('ten_thuoc')->get();

        return view('gia-thuoc.index', compact('giaThuoc', 'thuoc', 'activeGiaByThuoc', 'futureGiaByThuoc'));
    }

    /**
     * Lưu giá thuốc mới.
     */
    public function store(GiaThuocRequest $request)
    {
        $ngayBatDau = $request->ngay_bat_dau ?? now();

        // Nếu thuốc đã có giá đang hiệu lực => kết thúc giá cũ
        $existingPrice = GiaThuoc::where('thuoc_id', $request->thuoc_id)
            ->whereNull('ngay_ket_thuc')
            ->latest('ngay_bat_dau')
            ->first();

        if ($existingPrice) {
            $existingPrice->ngay_ket_thuc = $ngayBatDau;
            $existingPrice->save();
        }

        // Thêm giá mới
        $giaThuoc = new GiaThuoc();
        $giaThuoc->fill($request->validated());
        $giaThuoc->ngay_bat_dau = $ngayBatDau;
        $giaThuoc->save();

        if ($request->ajax()) {
            return response()->json([
                'success' => true,
                'giaThuoc' => $giaThuoc,
                'message' => 'Giá thuốc đã được cập nhật thành công.'
            ]);
        }

        return redirect()->route('gia-thuoc.index')
            ->with('success', 'Giá thuốc đã được cập nhật thành công.');
    }

    /**
     * Hiển thị chi tiết 1 giá thuốc.
     */
    public function show(GiaThuoc $giaThuoc)
    {
        $giaThuoc->load('thuoc');
        return response()->json(['giaThuoc' => $giaThuoc]);
    }

    /**
     * Cập nhật giá thuốc (thêm giá mới, kết thúc giá cũ).
     */
    public function update(GiaThuocRequest $request, GiaThuoc $giaThuoc)
    {
        $ngayBatDau = $request->ngay_bat_dau ?? now();

        // Kết thúc bản ghi hiện tại
        $giaThuoc->ngay_ket_thuc = $ngayBatDau;
        $giaThuoc->save();

        // Tạo bản ghi mới
        $newGiaThuoc = new GiaThuoc();
        $newGiaThuoc->thuoc_id = $giaThuoc->thuoc_id;
        $newGiaThuoc->fill($request->validated());
        $newGiaThuoc->ngay_bat_dau = $ngayBatDau;
        $newGiaThuoc->save();

        if ($request->ajax()) {
            return response()->json([
                'success' => true,
                'giaThuoc' => $newGiaThuoc,
                'message' => 'Đã thêm giá mới cho thuốc thành công.'
            ]);
        }

        return redirect()->route('gia-thuoc.index')
            ->with('success', 'Đã thêm giá mới cho thuốc thành công.');
    }

    /**
     * Xóa giá thuốc.
     */
    public function destroy(GiaThuoc $giaThuoc)
    {
        try {
            $giaThuoc->delete();
            return response()->json([
                'success' => true,
                'message' => 'Giá thuốc đã được xóa thành công.'
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Không thể xóa giá thuốc này.'
            ], 422);
        }
    }
}
