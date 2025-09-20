<?php

namespace App\Http\Controllers;

use App\Http\Requests\KhachHangRequest;
use App\Models\KhachHang;
use Illuminate\Http\Request;

class KhachHangController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $query = KhachHang::query();

        if ($request->has('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('sdt', 'like', "%{$search}%")
                  ->orWhere('ho_ten', 'like', "%{$search}%");
            });
        }

        $khachHang = $query->orderBy('ho_ten')->paginate(10);

        if ($request->ajax()) {
            return response()->json([
                'khachHang' => $khachHang,
                'links' => $khachHang->links()->toHtml(),
            ]);
        }

        return view('khach-hang.index', compact('khachHang'));
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(KhachHangRequest $request)
    {
        $khachHang = KhachHang::create($request->validated());
        
        if ($request->ajax()) {
            return response()->json([
                'success' => true,
                'khachHang' => $khachHang,
                'message' => 'Khách hàng đã được thêm thành công.'
            ]);
        }
        
        return redirect()->route('khach-hang.index')
            ->with('success', 'Khách hàng đã được thêm thành công.');
    }

    /**
     * Display the specified resource.
     */
    public function show(KhachHang $khachHang)
    {
        // Lấy danh sách đơn hàng của khách hàng
        $donHang = $khachHang->donBanLe()
                  ->with('chiTietDonBanLe.loThuoc.thuoc') // Eager load chi tiết đơn và thông tin thuốc
                  ->orderBy('ngay_ban', 'desc')
                  ->get();
        
        return response()->json([
            'khachHang' => $khachHang,
            'donHang' => $donHang
        ]);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(KhachHangRequest $request, KhachHang $khachHang)
    {
        $khachHang->update($request->validated());
        
        if ($request->ajax()) {
            return response()->json([
                'success' => true,
                'khachHang' => $khachHang,
                'message' => 'Khách hàng đã được cập nhật thành công.'
            ]);
        }
        
        return redirect()->route('khach-hang.index')
            ->with('success', 'Khách hàng đã được cập nhật thành công.');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(KhachHang $khachHang)
    {
        try {
            $khachHang->delete();
            return response()->json([
                'success' => true,
                'message' => 'Khách hàng đã được xóa thành công.'
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Không thể xóa khách hàng này vì đã có đơn hàng liên quan.'
            ], 422);
        }
    }
    
    /**
     * Tìm khách hàng theo số điện thoại
     */
    public function findByPhone(Request $request)
    {
        $sdt = $request->get('sdt');
        $khachHang = KhachHang::where('sdt', 'like', "%{$sdt}%")->where('trang_thai', 1)->get();
        
        return response()->json([
            'success' => true,
            'khachHang' => $khachHang
        ]);
    }

    /**
     * Đình chỉ hoặc bỏ đình chỉ khách hàng
     */
    public function suspend(Request $request, KhachHang $khachHang)
    {
        $khachHang->trang_thai = $khachHang->trang_thai == 1 ? 0 : 1;
        $khachHang->save();
        return response()->json([
            'success' => true,
            'trang_thai' => $khachHang->trang_thai,
            'message' => $khachHang->trang_thai == 1 ? 'Đã đình chỉ khách hàng.' : 'Đã bỏ đình chỉ khách hàng.'
        ]);
    }
}
