<?php

namespace App\Http\Controllers;

use App\Http\Requests\NhomThuocRequest;
use App\Models\NhomThuoc;
use Illuminate\Http\Request;

class NhomThuocController extends Controller
{
    /**
     * Get all drug groups for dropdowns (used by JS)
     */
    public function all()
    {
        $nhomThuoc = NhomThuoc::orderBy('ten_nhom')->get();
        return response()->json(['nhomThuoc' => $nhomThuoc]);
    }
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $query = NhomThuoc::query();

        if ($request->has('search')) {
            $search = $request->search;
            if (!empty(trim($search))) {
                $query->where(function ($q) use ($search) {
                    $q->where('ma_nhom', 'like', "%{$search}%")
                      ->orWhere('ten_nhom', 'like', "%{$search}%");
                });
            }
        }

        $nhomThuoc = $query->paginate(10);

        if ($request->ajax()) {
            return response()->json([
                'nhomThuoc' => $nhomThuoc,
                'links' => $nhomThuoc->links()->toHtml(),
            ]);
        }

        return view('nhom-thuoc.index', compact('nhomThuoc'));
    }

    /**
     * Get all active drug groups (for dropdowns)
     */
    public function getAllActive()
    {
        $nhomThuoc = NhomThuoc::orderBy('ten_nhom')->get();
        
        return response()->json([
            'nhomThuoc' => $nhomThuoc
        ]);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(NhomThuocRequest $request)
    {
        $nhomThuoc = NhomThuoc::create($request->validated());

        if ($request->ajax()) {
            return response()->json([
                'success' => true,
                'nhomThuoc' => $nhomThuoc,
                'message' => 'Nhóm thuốc đã được thêm thành công.'
            ]);
        }

        return redirect()->route('nhom-thuoc.index')
            ->with('success', 'Nhóm thuốc đã được thêm thành công.');
    }

    /**
     * Display the specified resource.
     */
    public function show(NhomThuoc $nhomThuoc)
    {
        return response()->json([
            'nhomThuoc' => $nhomThuoc
        ]);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(NhomThuocRequest $request, NhomThuoc $nhomThuoc)
    {
        $nhomThuoc->update($request->validated());

        if ($request->ajax()) {
            return response()->json([
                'success' => true,
                'nhomThuoc' => $nhomThuoc,
                'message' => 'Nhóm thuốc đã được cập nhật thành công.'
            ]);
        }

        return redirect()->route('nhom-thuoc.index')
            ->with('success', 'Nhóm thuốc đã được cập nhật thành công.');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(NhomThuoc $nhomThuoc)
    {
        try {
            // Kiểm tra xem có thuốc nào đang sử dụng nhóm này không
            if ($nhomThuoc->thuoc()->exists()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Không thể xóa nhóm thuốc này vì còn có thuốc đang sử dụng.'
                ], 422);
            }

            $nhomThuoc->delete();
            
            return response()->json([
                'success' => true,
                'message' => 'Nhóm thuốc đã được xóa thành công.'
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Có lỗi xảy ra khi xóa nhóm thuốc.'
            ], 500);
        }
    }

    /**
     * Suspend or unsuspend the specified resource.
     */
    public function suspend($id, Request $request)
    {
        $nhomThuoc = NhomThuoc::findOrFail($id);
        $nhomThuoc->trang_thai = $request->input('trang_thai', 1);
        $nhomThuoc->save();
        
        return response()->json([
            'message' => $nhomThuoc->trang_thai == 0 ? 'Nhóm thuốc đã bị đình chỉ.' : 'Đã bỏ đình chỉ nhóm thuốc.'
        ]);
    }
}