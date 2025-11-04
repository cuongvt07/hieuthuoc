<?php

namespace App\Http\Controllers;

use App\Models\NguoiDung;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rule;

class NguoiDungController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        // Lấy danh sách người dùng và phân theo vai trò
        $admins = NguoiDung::where('vai_tro', 'admin')
                ->when($request->has('search'), function($query) use ($request) {
                    $search = $request->search;
                    $query->where(function ($q) use ($search) {
                        $q->where('ho_ten', 'like', "%{$search}%")
                          ->orWhere('ten_dang_nhap', 'like', "%{$search}%")
                          ->orWhere('email', 'like', "%{$search}%")
                          ->orWhere('sdt', 'like', "%{$search}%");
                    });
                })
                ->orderBy('ho_ten')
                ->get();
        
        $duocSis = NguoiDung::where('vai_tro', 'duoc_si')
                ->when($request->has('search'), function($query) use ($request) {
                    $search = $request->search;
                    $query->where(function ($q) use ($search) {
                        $q->where('ho_ten', 'like', "%{$search}%")
                          ->orWhere('ten_dang_nhap', 'like', "%{$search}%")
                          ->orWhere('email', 'like', "%{$search}%")
                          ->orWhere('sdt', 'like', "%{$search}%");
                    });
                })
                ->orderBy('ho_ten')
                ->get();

        if ($request->ajax()) {
            return response()->json([
                'admins' => $admins,
                'duocSis' => $duocSis,
            ]);
        }

        return view('nguoi-dung.index', compact('admins', 'duocSis'));
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $validatedData = $request->validate([
            'ten_dang_nhap' => 'required|string|max:50|unique:nguoi_dung',
            'ho_ten' => 'required|string|max:100',
            'email' => 'required|email|max:100',
            'sdt' => 'nullable|string|max:20',
            'vai_tro' => ['required', Rule::in(['admin', 'duoc_si'])],
            // trang_thai: 1 = active, 0 = suspended
            'trang_thai' => ['required', Rule::in([0,1,'0','1'])],
            'mat_khau' => 'required|string|min:6',
        ], [
            'ten_dang_nhap.required' => 'Tên đăng nhập không được để trống',
            'ten_dang_nhap.unique' => 'Tên đăng nhập đã tồn tại',
            'ho_ten.required' => 'Họ tên không được để trống',
            'email.required' => 'Email không được để trống',
            'email.email' => 'Email không hợp lệ',
            'vai_tro.required' => 'Vai trò không được để trống',
            'vai_tro.in' => 'Vai trò không hợp lệ',
            'mat_khau.required' => 'Mật khẩu không được để trống',
            'mat_khau.min' => 'Mật khẩu phải có ít nhất 6 ký tự',
        ]);

        // Hash password
        $validatedData['mat_khau_hash'] = Hash::make($validatedData['mat_khau']);
        unset($validatedData['mat_khau']);

        $nguoiDung = NguoiDung::create($validatedData);

        if ($request->ajax()) {
            return response()->json([
                'success' => true,
                'message' => 'Người dùng đã được thêm thành công.',
                'nguoiDung' => $nguoiDung
            ]);
        }

        return redirect()->route('nguoi-dung.index')
            ->with('success', 'Người dùng đã được thêm thành công.');
    }

    /**
     * Display the specified resource.
     */
    public function show(NguoiDung $nguoiDung)
    {
        return response()->json([
            'nguoiDung' => $nguoiDung,
        ]);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, NguoiDung $nguoiDung)
    {
        $validatedData = $request->validate([
            'ten_dang_nhap' => [
                'string', 
                'max:50', 
                Rule::unique('nguoi_dung')->ignore($nguoiDung->nguoi_dung_id, 'nguoi_dung_id')
            ],
            'ho_ten' => 'string|max:100',
            'email' => 'email|max:100',
            'sdt' => 'nullable|string|max:20',
            'vai_tro' => ['required', Rule::in(['admin', 'duoc_si'])],
            'trang_thai' => ['required', Rule::in([0,1,'0','1'])],
        ]);

        $nguoiDung->update($validatedData);

        return response()->json([
            'success' => true,
            'message' => 'Người dùng đã được cập nhật thành công.',
            'nguoiDung' => $nguoiDung
        ]);
    }


    /**
     * Change password for a user.
     */
    public function changePassword(Request $request, NguoiDung $nguoiDung)
    {
        $validatedData = $request->validate([
            'mat_khau' => 'required|string|min:6',
        ], [
            'mat_khau.required' => 'Mật khẩu không được để trống',
            'mat_khau.min' => 'Mật khẩu phải có ít nhất 6 ký tự',
        ]);

        $nguoiDung->update([
            'mat_khau_hash' => Hash::make($validatedData['mat_khau'])
        ]);

        if ($request->ajax()) {
            return response()->json([
                'success' => true,
                'message' => 'Mật khẩu đã được cập nhật thành công.'
            ]);
        }

        return redirect()->route('nguoi-dung.index')
            ->with('success', 'Mật khẩu đã được cập nhật thành công.');
    }

    /**
     * Đình chỉ hoặc bỏ đình chỉ người dùng
     */
    public function suspend(Request $request, NguoiDung $nguoiDung)
    {
        // Toggle numeric trạng_thái: 1 => 0, 0 => 1
        $nguoiDung->trang_thai = $nguoiDung->trang_thai == 1 ? 0 : 1;
        $nguoiDung->save();

        $message = $nguoiDung->trang_thai == 0 ? 'Đã đình chỉ người dùng.' : 'Đã bỏ đình chỉ người dùng.';

        return response()->json([
            'success' => true,
            'trang_thai' => $nguoiDung->trang_thai,
            'message' => $message,
        ]);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(NguoiDung $nguoiDung)
    {
        try {
            $nguoiDung->delete();
            
            return response()->json([
                'success' => true,
                'message' => 'Người dùng đã được xóa thành công.'
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Không thể xóa người dùng này vì có liên kết với dữ liệu khác.'
            ], 422);
        }
    }
}
