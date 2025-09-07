<?php

namespace App\Http\Controllers;

use App\Http\Requests\NhomThuocRequest;
use App\Models\NhomThuoc;
use Illuminate\Http\Request;

class NhomThuocController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $query = NhomThuoc::query();

        if ($request->has('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('ma_nhom', 'like', "%{$search}%")
                  ->orWhere('ten_nhom', 'like', "%{$search}%");
            });
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
            $nhomThuoc->delete();
            return response()->json([
                'success' => true,
                'message' => 'Nhóm thuốc đã được xóa thành công.'
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Không thể xóa nhóm thuốc này vì đã có thuốc thuộc nhóm.'
            ], 422);
        }
    }
}
