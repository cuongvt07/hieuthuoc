<?php

namespace App\Http\Controllers;

use App\Http\Requests\GiaThuocRequest;
use App\Models\GiaThuoc;
use App\Models\Thuoc;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class GiaThuocController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $query = GiaThuoc::with('thuoc');
        
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
                         ->orderBy('created_at', 'desc')
                         ->paginate(10);
        
        if ($request->ajax()) {
            return response()->json([
                'giaThuoc' => $giaThuoc,
                'links' => $giaThuoc->links()->toHtml(),
            ]);
        }
        
        // Lấy danh sách thuốc cho dropdown
        $thuoc = Thuoc::orderBy('ten_thuoc')->get();
        
        return view('gia-thuoc.index', compact('giaThuoc', 'thuoc'));
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(GiaThuocRequest $request)
    {
        // Kiểm tra xem thuốc đã có giá chưa
        $existingPrice = GiaThuoc::where('thuoc_id', $request->thuoc_id)
            ->whereNull('ngay_ket_thuc')
            ->latest('ngay_bat_dau')
            ->first();

        $now = now();

        // Nếu đã có giá thì cập nhật ngày kết thúc của bản ghi cũ
        if ($existingPrice) {
            $existingPrice->ngay_ket_thuc = $now;
            $existingPrice->save();
        }

        // Tạo giá mới với ngày bắt đầu là thời điểm hiện tại
        $giaThuoc = new GiaThuoc();
        $giaThuoc->fill($request->validated());
        $giaThuoc->ngay_bat_dau = $now;
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
     * Display the specified resource.
     */
    public function show(GiaThuoc $giaThuoc)
    {
        $giaThuoc->load('thuoc');
        return response()->json([
            'giaThuoc' => $giaThuoc
        ]);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(GiaThuocRequest $request, GiaThuoc $giaThuoc)
    {
        // Đảm bảo ngày bắt đầu là thời điểm tạo bản ghi mới
        $now = now();

        // Cập nhật ngày kết thúc của bản ghi hiện tại
        $giaThuoc->ngay_ket_thuc = $now;
        $giaThuoc->save();

        // Tạo bản ghi giá mới
        $newGiaThuoc = new GiaThuoc();
        $newGiaThuoc->thuoc_id = $giaThuoc->thuoc_id;
        $newGiaThuoc->fill($request->validated());
        $newGiaThuoc->ngay_bat_dau = $now;
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
     * Remove the specified resource from storage.
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
