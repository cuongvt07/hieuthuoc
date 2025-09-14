<?php

namespace App\Http\Controllers;

use App\Models\Kho;
use App\Models\LoThuoc;
use App\Models\Thuoc;
use Illuminate\Http\Request;

class KhoController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $query = Kho::withSum(['loThuoc as total_items' => function($query) {
            $query->where('ton_kho_hien_tai', '>', 0);
        }], 'ton_kho_hien_tai')
        ->withCount(['thuoc as total_medicines' => function($query) {
            $query->whereHas('loThuoc', function($q) {
                $q->where('ton_kho_hien_tai', '>', 0);
            })->distinct();
        }]);

        if ($request->has('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('ten_kho', 'like', "%{$search}%")
                  ->orWhere('dia_chi', 'like', "%{$search}%");
            });
        }

        $khos = $query->orderBy('ten_kho')->paginate(10);

        if ($request->ajax()) {
            return response()->json([
                'khos' => $khos,
                'links' => $khos->links()->toHtml()
            ]);
        }

        return view('kho.index', compact('khos'));
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $validatedData = $request->validate([
            'ten_kho' => 'required|string|max:100',
            'dia_chi' => 'nullable|string|max:255',
            'ghi_chu' => 'nullable|string',
        ], [
            'ten_kho.required' => 'Vui lòng nhập tên kho',
        ]);

        $kho = Kho::create($validatedData);
        
        if ($request->ajax()) {
            return response()->json([
                'success' => true,
                'kho' => $kho,
                'message' => 'Kho đã được thêm thành công.'
            ]);
        }
        
        return redirect()->route('kho.index')
            ->with('success', 'Kho đã được thêm thành công.');
    }

    /**
     * Display the specified resource.
     */
    public function show(Request $request, Kho $kho)
    {
        if ($request->has('thuoc_id')) {
            // Xem tồn kho của một thuốc trong tất cả các kho
            $thuoc = Thuoc::with(['loThuoc' => function($query) {
                $query->where('ton_kho_hien_tai', '>', 0)
                      ->with('kho');
            }])->findOrFail($request->thuoc_id);

            // Tính tổng tồn kho theo từng kho
            $tonKhoTheoKho = LoThuoc::where('thuoc_id', $request->thuoc_id)
                ->where('ton_kho_hien_tai', '>', 0)
                ->select('kho_id')
                ->selectRaw('SUM(ton_kho_hien_tai) as tong_ton_kho')
                ->with('kho:kho_id,ten_kho')
                ->groupBy('kho_id')
                ->get();
            
            return response()->json([
                'thuoc' => $thuoc,
                'tonKhoTheoKho' => $tonKhoTheoKho,
                'loThuoc' => $thuoc->loThuoc
            ]);
        } else {
            // Xem danh sách thuốc trong kho
            $thuocs = Thuoc::with(['loThuoc' => function($query) use ($kho) {
                $query->where('kho_id', $kho->kho_id)
                      ->where('ton_kho_hien_tai', '>', 0)
                      ->select('lo_id', 'thuoc_id', 'kho_id', 'ton_kho_hien_tai', 'ngay_san_xuat', 'han_su_dung');
            }])
            ->whereHas('loThuoc', function($query) use ($kho) {
                $query->where('kho_id', $kho->kho_id)
                      ->where('ton_kho_hien_tai', '>', 0);
            })
            ->select('thuoc_id', 'ma_thuoc', 'ten_thuoc', 'don_vi_goc', 'nhom_id')
            ->with('nhomThuoc:nhom_id,ten_nhom')
            ->withSum(['loThuoc' => function($query) use ($kho) {
                $query->where('kho_id', $kho->kho_id)
                      ->where('ton_kho_hien_tai', '>', 0);
            }], 'ton_kho_hien_tai')
            ->paginate(10);
            
            if ($request->ajax()) {
                return response()->json([
                    'kho' => $kho,
                    'thuocs' => $thuocs,
                    'links' => $thuocs->links()->toHtml()
                ]);
            }
            
            return response()->json([
                'kho' => $kho,
                'thuocs' => $thuocs,
                'links' => $thuocs->links()->toHtml()
            ]);
        }
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Kho $kho)
    {
        $validatedData = $request->validate([
            'ten_kho' => 'required|string|max:100',
            'dia_chi' => 'nullable|string|max:255',
            'ghi_chu' => 'nullable|string',
        ], [
            'ten_kho.required' => 'Vui lòng nhập tên kho',
        ]);

        $kho->update($validatedData);
        
        if ($request->ajax()) {
            return response()->json([
                'success' => true,
                'kho' => $kho,
                'message' => 'Kho đã được cập nhật thành công.'
            ]);
        }
        
        return redirect()->route('kho.index')
            ->with('success', 'Kho đã được cập nhật thành công.');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Kho $kho)
    {
        // Kiểm tra nếu kho còn thuốc
        $hasItems = LoThuoc::where('kho_id', $kho->kho_id)->where('ton_kho_hien_tai', '>', 0)->exists();
        
        if ($hasItems) {
            return response()->json([
                'success' => false,
                'message' => 'Không thể xóa kho này vì còn thuốc tồn kho.'
            ], 422);
        }
        
        try {
            $kho->delete();
            return response()->json([
                'success' => true,
                'message' => 'Kho đã được xóa thành công.'
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Không thể xóa kho này vì có dữ liệu liên quan.'
            ], 422);
        }
    }
}
