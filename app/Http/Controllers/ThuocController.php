<?php

namespace App\Http\Controllers;

use App\Http\Requests\ThuocRequest;
use App\Models\NhomThuoc;
use App\Models\Thuoc;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;

class ThuocController extends Controller
{
    /**
     * Get medicine information
     */
    public function getInfo(Request $request): JsonResponse
    {
        $thuoc = Thuoc::with(['nhomThuoc', 'giaThuoc' => function($query) {
            $query->orderBy('created_at', 'desc')->first();
        }])->findOrFail($request->id);
        
        return response()->json([
            'success' => true,
            'data' => $thuoc
        ]);
    }

    /**
     * Get list of warehouses that have lots of this medicine
     */
    public function getKhoList($id): JsonResponse
    {
        
        // Lấy danh sách tất cả các kho
        $allKho = \DB::table('kho')
            ->select('kho_id', 'ten_kho')
            ->get();

        // Lấy danh sách kho đã có lô của thuốc này
        $existingKho = \DB::table('lo_thuoc')
            ->join('kho', 'lo_thuoc.kho_id', '=', 'kho.kho_id')
            ->where('lo_thuoc.thuoc_id', $id)
            ->select('kho.kho_id', 'kho.ten_kho')
            ->distinct()
            ->get();

        return response()->json([
            'success' => true,
            'data' => [
                'all_kho' => $allKho,
                'existing_kho' => $existingKho
            ]
        ]);
    }

    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $query = Thuoc::with('nhomThuoc');

        if ($request->has('search')) {
            $search = $request->search;
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
        
        $nhomQuery = NhomThuoc::query();
        
        if ($request->has('search_nhom')) {
            $search = $request->search_nhom;
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
        $thuoc->load('nhomThuoc');

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
        $thuoc->load('nhomThuoc'); // Load relationship để trả về đầy đủ thông tin
        
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

    /**
     * Suspend or unsuspend the specified resource.
     */
    public function suspend($id, Request $request)
    {
        $thuoc = Thuoc::findOrFail($id);
        $thuoc->trang_thai = $request->input('trang_thai', 0);
        $thuoc->save();
        return response()->json(['message' => $thuoc->trang_thai == 1 ? 'Thuốc đã bị đình chỉ.' : 'Đã bỏ đình chỉ thuốc.']);
    }
}