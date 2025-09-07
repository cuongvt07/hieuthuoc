<?php

namespace App\Http\Controllers;

use App\Http\Requests\GiaThuocRequest;
use App\Models\GiaThuoc;
use App\Models\Thuoc;
use Illuminate\Http\Request;

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
            $query->where(function($q) use ($request) {
                $q->where('ngay_ket_thuc', '<=', $request->ngay_ket_thuc)
                  ->orWhereNull('ngay_ket_thuc');
            });
        }
        
        $giaThuoc = $query->orderBy('thuoc_id', 'asc')
                           ->orderBy('ngay_bat_dau', 'desc')
                           ->paginate(10);
        
        if ($request->ajax()) {
            return response()->json([
                'giaThuoc' => $giaThuoc,
                'links' => $giaThuoc->links()->toHtml(),
            ]);
        }
        
        // Lấy danh sách các thuốc chưa có giá để hiển thị trong dropdown thêm mới
        $existingThuocIds = GiaThuoc::pluck('thuoc_id')->toArray();
        $thuoc = Thuoc::all();
        $availableThuoc = Thuoc::whereNotIn('thuoc_id', $existingThuocIds)->get();
        
        return view('gia-thuoc.index', compact('giaThuoc', 'thuoc', 'availableThuoc'));
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(GiaThuocRequest $request)
    {
        // Kiểm tra xem thuốc đã có giá chưa
        $existingPrice = GiaThuoc::where('thuoc_id', $request->thuoc_id)->first();
        
        if ($existingPrice) {
            if ($request->ajax()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Thuốc này đã có giá. Vui lòng cập nhật giá hiện có thay vì thêm mới.'
                ], 422);
            }
            
            return redirect()->route('gia-thuoc.index')
                ->with('error', 'Thuốc này đã có giá. Vui lòng cập nhật giá hiện có thay vì thêm mới.');
        }
        
        $giaThuoc = GiaThuoc::create($request->validated());
        
        if ($request->ajax()) {
            return response()->json([
                'success' => true,
                'giaThuoc' => $giaThuoc,
                'message' => 'Giá thuốc đã được thêm thành công.'
            ]);
        }
        
        return redirect()->route('gia-thuoc.index')
            ->with('success', 'Giá thuốc đã được thêm thành công.');
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
        $giaThuoc->update($request->validated());
        
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
