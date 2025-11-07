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
     * Trang chính: render giao diện quản lý thuốc & nhóm thuốc
     */
    public function index()
    {
        // ✅ Trang đầu tiên hiển thị dữ liệu trang 1 của cả hai bảng
        $thuoc = Thuoc::with(['nhomThuoc', 'kho'])->paginate(5);
        $nhomThuoc = NhomThuoc::paginate(2); // dùng cho danh sách nhóm thuốc (bên trái)
        $nhomThuocData = NhomThuoc::all();    // dùng cho dropdown filter nhóm ở khối thuốc
        $kho = Kho::all();                    // danh sách kho cho dropdown

        return view('thuoc.index', [
            'thuoc' => $thuoc,
            'nhomThuoc' => $nhomThuoc,
            'nhomThuocData' => $nhomThuocData,
            'kho' => $kho
        ]);
    }

    /**
     * API: danh sách nhóm thuốc (AJAX cho khối bên trái)
     */
    public function getNhomThuocList(Request $request)
    {
        $query = NhomThuoc::query();

        if ($search = trim($request->get('search', ''))) {
            $query->where(function ($q) use ($search) {
                $q->where('ma_nhom', 'like', "%{$search}%")
                ->orWhere('ten_nhom', 'like', "%{$search}%");
            });
        }

        // ⚙️ Giữ query string khi phân trang
        $nhomThuoc = $query->paginate(2)
            ->appends($request->query());

        return response()->json([
            'nhomThuoc' => $nhomThuoc,
            'links' => $nhomThuoc->onEachSide(1)->links('vendor.pagination.custom')->render(),
        ]);
    }


    /**
     * API: danh sách thuốc (AJAX cho khối bên phải)
     */
    public function getThuocList(Request $request)
    {
        $query = Thuoc::with(['nhomThuoc', 'kho']);

        if ($search = trim($request->get('search', ''))) {
            $query->where(function ($q) use ($search) {
                $q->where('ma_thuoc', 'like', "%{$search}%")
                ->orWhere('ten_thuoc', 'like', "%{$search}%");
            });
        }

        if ($request->filled('nhom_id')) {
            $query->where('nhom_id', $request->nhom_id);
        }

        if ($request->filled('kho_id')) {
            $query->where('kho_id', $request->kho_id);
        }

        // ⚙️ Giữ filter khi chuyển trang
        $thuoc = $query->orderByDesc('created_at')
            ->paginate(5)
            ->appends($request->query());

        return response()->json([
            'thuoc' => $thuoc,
            'links' => $thuoc->onEachSide(1)->links('vendor.pagination.custom')->render(),
        ]);
    }

    /**
     * Lấy thông tin chi tiết 1 thuốc
     */
    public function getInfo(Request $request): JsonResponse
    {
        $thuoc = Thuoc::with(['nhomThuoc', 'giaThuoc' => function ($query) {
            $query->orderByDesc('created_at')->first();
        }])->findOrFail($request->id);

        return response()->json([
            'success' => true,
            'data' => $thuoc
        ]);
    }

    /**
     * Lấy danh sách kho liên quan đến thuốc
     */
    public function getKhoList($id): JsonResponse
    {
        $existingKho = \DB::table('lo_thuoc')
            ->where('thuoc_id', $id)
            ->join('kho', 'lo_thuoc.kho_id', '=', 'kho.kho_id')
            ->select('kho.kho_id', 'kho.ten_kho')
            ->distinct()
            ->get();

        if ($existingKho->isEmpty()) {
            $thuoc = Thuoc::findOrFail($id);
            $allKho = Kho::where('kho_id', $thuoc->kho_id)
                ->select('kho_id', 'ten_kho')
                ->get();
        } else {
            $allKho = Kho::with(['thuoc' => fn($q) => $q->where('thuoc.thuoc_id', $id)])
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

    /** ---------- CRUD thuốc giữ nguyên ---------- */

    public function store(ThuocRequest $request)
    {
        $validated = $request->validated();
        $validated['kho_id'] = $request->input('kho_id');
        $validated['trang_thai'] = 1;

        $thuoc = Thuoc::create($validated);
        $thuoc->load('nhomThuoc', 'kho');

        return $request->ajax()
            ? response()->json(['success' => true, 'thuoc' => $thuoc, 'message' => 'Thuốc đã được thêm thành công.'])
            : redirect()->route('thuoc.index')->with('success', 'Thuốc đã được thêm thành công.');
    }

    public function show(Thuoc $thuoc)
    {
        $thuoc->load('nhomThuoc', 'giaThuoc', 'kho');
        return response()->json(['success' => true, 'thuoc' => $thuoc]);
    }

    public function update(ThuocRequest $request, Thuoc $thuoc)
    {
        $validated = $request->validated();
        $validated['kho_id'] = $request->input('kho_id');
        $thuoc->update($validated);
        $thuoc->load('nhomThuoc', 'kho');

        return $request->ajax()
            ? response()->json(['success' => true, 'thuoc' => $thuoc, 'message' => 'Thuốc đã được cập nhật thành công.'])
            : redirect()->route('thuoc.index')->with('success', 'Thuốc đã được cập nhật thành công.');
    }

    public function destroy(Thuoc $thuoc)
    {
        try {
            $thuoc->delete();
            return response()->json(['success' => true, 'message' => 'Thuốc đã được xóa thành công.']);
        } catch (\Exception $e) {
            return response()->json(['success' => false, 'message' => 'Không thể xóa thuốc này vì đã có dữ liệu liên quan.'], 422);
        }
    }

    public function suspend($id, Request $request)
    {
        $thuoc = Thuoc::findOrFail($id);
        $thuoc->trang_thai = $request->input('trang_thai', 1);
        $thuoc->save();

        return response()->json([
            'message' => $thuoc->trang_thai == 0 ? 'Thuốc đã bị đình chỉ.' : 'Đã bỏ đình chỉ thuốc.'
        ]);
    }

    public function getLots(Thuoc $thuoc): JsonResponse
    {
        $lots = $thuoc->loThuoc()->with(['kho', 'lichSuTonKho'])->get();
        return response()->json(['success' => true, 'data' => $lots]);
    }
}
