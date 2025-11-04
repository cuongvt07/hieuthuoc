<?php

namespace App\Http\Controllers;

use App\Http\Requests\ThuocRequest;
use App\Models\Kho;
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
     * Get list of warehouses that have lots of this medicine,
     * or if no lots exist, get the warehouse set by kho_id in Thuoc
     */
    public function getKhoList($id): JsonResponse
    {
        // Lấy danh sách kho đã có lô của thuốc này
        $existingKho = \DB::table('lo_thuoc')
            ->where('thuoc_id', $id)
            ->join('kho', 'lo_thuoc.kho_id', '=', 'kho.kho_id')
            ->select('kho.kho_id', 'kho.ten_kho')
            ->distinct()
            ->get();

        if ($existingKho->isEmpty()) {
            // Nếu chưa có lô, lấy kho theo kho_id của thuốc
            $thuoc = Thuoc::findOrFail($id);
            $kho = Kho::where('kho_id', $thuoc->kho_id)
                ->select('kho_id', 'ten_kho')
                ->get();
            $allKho = $kho;
        } else {
            // Nếu đã có lô, lấy danh sách tất cả các kho có liên kết với thuốc qua lô
            $allKho = Kho::with(['thuoc' => function($q) use ($id) {
                    $q->where('thuoc.thuoc_id', $id);
                }])
                ->select('kho_id', 'ten_kho')
                ->get();
        }

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
        $query = Thuoc::with(['nhomThuoc', 'kho']);

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

        if ($request->has('kho_id') && $request->kho_id != '') {
            $query->where('kho_id', $request->kho_id);
        }

        $thuoc = $query->paginate(10)->withQueryString();
        
        $nhomQuery = NhomThuoc::query();
        $kho = Kho::all();

        $khoQuery = Kho::query();
        
        if ($request->has('search_nhom')) {
            $search = $request->search_nhom;
            if (!empty(trim($search))) {
                $nhomQuery->where(function ($q) use ($search) {
                    $q->where('ma_nhom', 'like', "%{$search}%")
                      ->orWhere('ten_nhom', 'like', "%{$search}%");
                });
                
            }
        }
        
        $nhomThuoc = $nhomQuery->paginate(10)->withQueryString();

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

        return view('thuoc.index', compact('thuoc', 'nhomThuoc', 'kho'));
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(ThuocRequest $request)
    {
        $validated = $request->validated();
        $validated['kho_id'] = $request->input('kho_id'); // Include kho_id
        $validated['trang_thai'] = 1; // Mặc định trạng thái là hoạt động

        $thuoc = Thuoc::create($validated);
        $thuoc->load('nhomThuoc', 'kho');

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
        $thuoc->load('nhomThuoc', 'giaThuoc', 'kho');
        return response()->json([
            'success' => true,
            'thuoc' => $thuoc
        ]);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(ThuocRequest $request, Thuoc $thuoc)
    {
        $validated = $request->validated();
        $validated['kho_id'] = $request->input('kho_id'); // Include kho_id

        $thuoc->update($validated);
        $thuoc->load('nhomThuoc', 'kho');

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
        $thuoc->trang_thai = $request->input('trang_thai', 1);
        $thuoc->save();
        return response()->json(['message' => $thuoc->trang_thai == 0 ? 'Thuốc đã bị đình chỉ.' : 'Đã bỏ đình chỉ thuốc.']);
    }

    /**
     * Get lot details for a specific medicine
     */
    public function getLots(Thuoc $thuoc): JsonResponse
    {
        $lots = $thuoc->loThuoc()
            ->with(['kho', 'lichSuTonKho'])
            ->get();

        return response()->json([
            'success' => true,
            'data' => $lots
        ]);
    }
}