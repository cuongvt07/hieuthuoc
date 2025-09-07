<?php

namespace App\Http\Controllers;

use App\Models\Kho;
use App\Models\LoThuoc;
use Illuminate\Http\Request;

class KhoController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $query = Kho::query();

        if ($request->has('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('ten_kho', 'like', "%{$search}%")
                  ->orWhere('dia_chi', 'like', "%{$search}%");
            });
        }

        $khos = $query->orderBy('ten_kho')->get();

        if ($request->ajax()) {
            return response()->json([
                'khos' => $khos,
            ]);
        }

        // Lấy tổng số thuốc trong kho
        foreach ($khos as $kho) {
            $kho->total_items = LoThuoc::where('kho_id', $kho->kho_id)->sum('ton_kho_hien_tai');
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
    public function show(Kho $kho)
    {
        $loThuoc = LoThuoc::with('thuoc')
                ->where('kho_id', $kho->kho_id)
                ->where('ton_kho_hien_tai', '>', 0)
                ->orderBy('han_su_dung')
                ->get();

        return response()->json([
            'kho' => $kho,
            'loThuoc' => $loThuoc
        ]);
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
