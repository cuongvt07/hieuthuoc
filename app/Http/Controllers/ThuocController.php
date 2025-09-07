<?php

namespace App\Http\Controllers;

use App\Http\Requests\ThuocRequest;
use App\Models\NhomThuoc;
use App\Models\Thuoc;
use Illuminate\Http\Request;

class ThuocController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        // Query cho Thuốc
        $query = Thuoc::with('nhomThuoc');

        if ($request->has('search')) {
            $search = $request->search;
            // Chỉ áp dụng tìm kiếm khi từ khóa không rỗng
            if (!empty(trim($search))) {
                $query->where(function ($q) use ($search) {
                    $q->where('ma_thuoc', 'like', "%{$search}%")
                      ->orWhere('ten_thuoc', 'like', "%{$search}%");
                });
            }
        }

        if ($request->has('nhom_id') && $request->nhom_id != '') {
            $query->where('nhom_id', $request->nhom_id);
        }

        $thuoc = $query->paginate(10);
        
        // Query cho Nhóm Thuốc
        $nhomQuery = NhomThuoc::query();
        
        if ($request->has('search_nhom')) {
            $search = $request->search_nhom;
            // Chỉ áp dụng tìm kiếm khi từ khóa không rỗng
            if (!empty(trim($search))) {
                $nhomQuery->where(function ($q) use ($search) {
                    $q->where('ma_nhom', 'like', "%{$search}%")
                      ->orWhere('ten_nhom', 'like', "%{$search}%");
                });
            }
        }
        
        $nhomThuoc = $nhomQuery->paginate(10);

        if ($request->ajax()) {
            // Kiểm tra xem request đến từ phần tìm kiếm thuốc hay nhóm thuốc
            if ($request->has('search_nhom')) {
                return response()->json([
                    'nhomThuoc' => $nhomThuoc,
                    'links' => $nhomThuoc->links()->toHtml(),
                ]);
            } else {
                return response()->json([
                    'thuoc' => $thuoc,
                    'links' => $thuoc->links()->toHtml(),
                ]);
            }
        }

        return view('thuoc.index', compact('thuoc', 'nhomThuoc'));
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(ThuocRequest $request)
    {
        $thuoc = Thuoc::create($request->validated());
        
        if ($request->ajax()) {
            return response()->json([
                'success' => true,
                'thuoc' => $thuoc,
                'message' => 'Thuốc đã được thêm thành công.'
            ]);
        }
        
        return redirect()->route('thuoc.index')
            ->with('success', 'Thuốc đã được thêm thành công.');
    }

    /**
     * Display the specified resource.
     */
    public function show(Thuoc $thuoc)
    {
        $thuoc->load('nhomThuoc', 'giaThuoc');
        return response()->json([
            'thuoc' => $thuoc
        ]);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(ThuocRequest $request, Thuoc $thuoc)
    {
        $thuoc->update($request->validated());
        
        if ($request->ajax()) {
            return response()->json([
                'success' => true,
                'thuoc' => $thuoc,
                'message' => 'Thuốc đã được cập nhật thành công.'
            ]);
        }
        
        return redirect()->route('thuoc.index')
            ->with('success', 'Thuốc đã được cập nhật thành công.');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Thuoc $thuoc)
    {
        try {
            $thuoc->delete();
            return response()->json([
                'success' => true,
                'message' => 'Thuốc đã được xóa thành công.'
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Không thể xóa thuốc này vì đã có dữ liệu liên quan.'
            ], 422);
        }
    }
}
