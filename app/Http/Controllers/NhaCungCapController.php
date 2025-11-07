<?php

namespace App\Http\Controllers;

use App\Http\Requests\NhaCungCapRequest;
use App\Models\NhaCungCap;
use Illuminate\Http\Request;

class NhaCungCapController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {

        $query = NhaCungCap::withCount('phieuNhap')->with(['phieuNhap']);

        if ($request->has('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('ten_ncc', 'like', "%{$search}%")
                  ->orWhere('sdt', 'like', "%{$search}%")
                  ->orWhere('email', 'like', "%{$search}%")
                  ->orWhere('ma_so_thue', 'like', "%{$search}%");
            });
        }

        // Allow sorting by purchase order count
        $sort = $request->get('sort');
        $direction = $request->get('direction', 'desc');
        if ($sort === 'so_phieu_nhap') {
            $query->orderBy('phieu_nhap_count', $direction);
        } else {
            $query->orderBy('ten_ncc');
        }

        $nhaCungCap = $query->paginate(10)->appends($request->query());

if ($request->ajax()) {
    return response()->json([
        'nhaCungCap' => $nhaCungCap,
        'links' => $nhaCungCap
            ->onEachSide(1)
            ->appends($request->all())
            ->links('vendor.pagination.custom')
            ->render(), // ✅ Trả HTML đúng
    ]);
}


        return view('nha-cung-cap.index', compact('nhaCungCap'));
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(NhaCungCapRequest $request)
    {
    $nhaCungCap = NhaCungCap::create($request->all());
        
        if ($request->ajax()) {
            return response()->json([
                'success' => true,
                'nhaCungCap' => $nhaCungCap,
                'message' => 'Nhà cung cấp đã được thêm thành công.'
            ]);
        }
        
        return redirect()->route('nha-cung-cap.index')
            ->with('success', 'Nhà cung cấp đã được thêm thành công.');
    }

    /**
     * Display the specified resource.
     */
    public function show(NhaCungCap $nhaCungCap)
    {
        // Lấy danh sách phiếu nhập của nhà cung cấp
        $phieuNhap = $nhaCungCap->phieuNhap()
                  ->with(['chiTietLoNhaps.loThuoc.thuoc', 'nguoiDung']) // Eager load chi tiết phiếu và thông tin thuốc
                  ->orderBy('ngay_nhap', 'desc')
                  ->get();
        
        return response()->json([
            'nhaCungCap' => $nhaCungCap,
            'phieuNhap' => $phieuNhap
        ]);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(NhaCungCapRequest $request, NhaCungCap $nhaCungCap)
    {
        $nhaCungCap->update($request->all());
        
        if ($request->ajax()) {
            return response()->json([
                'success' => true,
                'nhaCungCap' => $nhaCungCap,
                'message' => 'Nhà cung cấp đã được cập nhật thành công.'
            ]);
        }
        
        return redirect()->route('nha-cung-cap.index')
            ->with('success', 'Nhà cung cấp đã được cập nhật thành công.');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(NhaCungCap $nhaCungCap)
    {
        try {
            $nhaCungCap->delete();
            return response()->json([
                'success' => true,
                'message' => 'Nhà cung cấp đã được xóa thành công.'
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Không thể xóa nhà cung cấp này vì đã có phiếu nhập liên quan.'
            ], 422);
        }
    }
    
    /**
     * Tìm nhà cung cấp theo số điện thoại hoặc mã số thuế
     */
    public function findByPhoneOrTax(Request $request)
    {
        $query = NhaCungCap::query();
        
        if ($request->has('sdt')) {
            $query->where('sdt', 'like', "%{$request->sdt}%");
        }
        
        if ($request->has('mst')) {
            $query->orWhere('ma_so_thue', 'like', "%{$request->mst}%");
        }
        
        $nhaCungCap = $query->get();
        
        return response()->json([
            'success' => true,
            'nhaCungCap' => $nhaCungCap
        ]);
    }

    /**
     * Đình chỉ hoặc bỏ đình chỉ nhà cung cấp
     */
    public function suspend(Request $request, NhaCungCap $nhaCungCap)
    {
        $nhaCungCap->trang_thai = $nhaCungCap->trang_thai == 1 ? 0 : 1;
        $nhaCungCap->save();
        return response()->json([
            'success' => true,
            'trang_thai' => $nhaCungCap->trang_thai,
            'message' => $nhaCungCap->trang_thai == 1 ? 'Đã đình chỉ nhà cung cấp.' : 'Đã bỏ đình chỉ nhà cung cấp.'
        ]);
    }
}
