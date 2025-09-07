<?php

use App\Http\Controllers\Auth\AuthController;
use App\Http\Controllers\GiaThuocController;
use App\Http\Controllers\KhachHangController;
use App\Http\Controllers\KhoController;
use App\Http\Controllers\NguoiDungController;
use App\Http\Controllers\NhaCungCapController;
use App\Http\Controllers\NhomThuocController;
use App\Http\Controllers\PhieuNhapController;
use App\Http\Controllers\ThuocController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
*/

// Auth routes
Route::get('/login', [AuthController::class, 'showLoginForm'])->name('login');
Route::post('/login', [AuthController::class, 'login']);
Route::post('/logout', [AuthController::class, 'logout'])->name('logout');

// Protected routes
Route::middleware('auth')->group(function () {
    // Dashboard
    Route::get('/', function () {
        return view('dashboard');
    })->name('dashboard');
    
    // Thuốc routes (bao gồm cả quản lý nhóm thuốc)
    Route::resource('thuoc', ThuocController::class);
    
    // Nhóm thuốc routes - chuyển hướng về trang quản lý thuốc và giữ các tham số tìm kiếm
    Route::get('nhom-thuoc', function(Request $request) {
        // Chuyển tất cả query parameters từ request hiện tại sang route thuoc.index
        return redirect()->route('thuoc.index', $request->all());
    })->name('nhom-thuoc.index');
    
    // API routes cho nhóm thuốc
    Route::post('nhom-thuoc', [NhomThuocController::class, 'store'])->name('nhom-thuoc.store');
    Route::get('nhom-thuoc/{nhomThuoc}', [NhomThuocController::class, 'show'])->name('nhom-thuoc.show');
    Route::put('nhom-thuoc/{nhomThuoc}', [NhomThuocController::class, 'update'])->name('nhom-thuoc.update');
    Route::delete('nhom-thuoc/{nhomThuoc}', [NhomThuocController::class, 'destroy'])->name('nhom-thuoc.destroy');
    
    // Giá thuốc routes
    Route::resource('gia-thuoc', GiaThuocController::class);
    
    // Khách hàng routes
    Route::resource('khach-hang', KhachHangController::class);
    Route::get('khach-hang-tim-sdt', [KhachHangController::class, 'findByPhone'])->name('khach-hang.findByPhone');
    
    // Nhà cung cấp routes
    Route::resource('nha-cung-cap', NhaCungCapController::class);
    Route::get('nha-cung-cap-tim', [NhaCungCapController::class, 'findByPhoneOrTax'])->name('nha-cung-cap.findByPhoneOrTax');
    
    // Người dùng routes (quản lý nhân sự)
    Route::resource('nguoi-dung', NguoiDungController::class);
    Route::put('nguoi-dung/{nguoiDung}/doi-mat-khau', [NguoiDungController::class, 'changePassword'])->name('nguoi-dung.changePassword');
    
    // Kho routes
    Route::resource('kho', KhoController::class);
    
    // Phiếu nhập routes
    Route::resource('phieu-nhap', PhieuNhapController::class);
    Route::get('phieu-nhap-ton-kho', [PhieuNhapController::class, 'getTonKho'])->name('phieu-nhap.get-ton-kho');
    Route::get('phieu-nhap-lo-thuoc', [PhieuNhapController::class, 'getLoThuoc'])->name('phieu-nhap.get-lo-thuoc');
});
