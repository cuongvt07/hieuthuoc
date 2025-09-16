<?php

use App\Http\Controllers\Auth\AuthController;
use App\Http\Controllers\BaoCaoTonKhoController;
use App\Http\Controllers\BaoCaoKhachHangController;
use App\Http\Controllers\BaoCaoLoThuocController;
use App\Http\Controllers\BaoCaoThuocController;
use App\Http\Controllers\BaoCaoKhoController;
use App\Http\Controllers\DonBanLeController;
use App\Http\Controllers\GiaThuocController;
use App\Http\Controllers\KhachHangController;
use App\Http\Controllers\KhoController;
use App\Http\Controllers\LoThuocController;
use App\Http\Controllers\NguoiDungController;
use App\Http\Controllers\NhaCungCapController;
use App\Http\Controllers\NhomThuocController;
use App\Http\Controllers\PhieuNhapController;
use App\Http\Controllers\ThuocController;
use App\Http\Controllers\ThongBaoController;
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
    // Notifications API
    Route::get('/test-expired', [ThongBaoController::class, 'checkExpiredMedicines']);
    Route::get('/api/notifications/unread', [ThongBaoController::class, 'getUnreadNotifications']);
    Route::post('/api/notifications/{id}/read', [ThongBaoController::class, 'markAsRead']);

    // Đình chỉ/Bỏ đình chỉ nhóm thuốc
    Route::post('/nhom-thuoc/{id}/suspend', [App\Http\Controllers\NhomThuocController::class, 'suspend'])->name('nhom-thuoc.suspend');
    // Đình chỉ/Bỏ đình chỉ thuốc
    Route::post('/thuoc/{id}/suspend', [App\Http\Controllers\ThuocController::class, 'suspend'])->name('thuoc.suspend');
    // Dashboard
    Route::get('/', function () {
        return view('dashboard');
    })->name('dashboard');

    // Báo cáo - trang chính
    Route::get('/lich-su-ton-kho', [App\Http\Controllers\LichSuTonKhoController::class, 'index'])->name('lich-su-ton-kho.index');
    Route::get('bao-cao', function () {
        return view('bao-cao.index');
    })->name('bao-cao.index');
    
    // Thuốc routes (bao gồm cả quản lý nhóm thuốc)
    Route::resource('thuoc', ThuocController::class);
    Route::get('thuoc/{thuoc}/lots', [ThuocController::class, 'getLots'])->name('thuoc.lots');
    Route::get('api/thuoc/info', [ThuocController::class, 'getInfo'])->name('api.thuoc.info');
    Route::get('api/thuoc/{id}/kho', [ThuocController::class, 'getKhoList'])->name('api.thuoc.kho');
    
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
    Route::get('phieu-nhap/{id}/ajax', [PhieuNhapController::class, 'getPhieuNhapInfo'])->name('phieu-nhap.ajax');
    Route::post('phieu-nhap/{id}/complete', [PhieuNhapController::class, 'complete'])->name('phieu-nhap.complete');
    Route::get('phieu-nhap-ton-kho', [PhieuNhapController::class, 'getTonKho'])->name('phieu-nhap.get-ton-kho');
    Route::get('phieu-nhap-lo-thuoc', [PhieuNhapController::class, 'getLoThuoc'])->name('phieu-nhap.get-lo-thuoc');
    Route::get('phieu-nhap/lot-additions/{loId}', [PhieuNhapController::class, 'getLotAdditions'])->name('phieu-nhap.lot-additions');
    Route::get('phieu-nhap/lot-history/{loId}', [PhieuNhapController::class, 'getLotHistory'])->name('phieu-nhap.lot-history');
    
    // Lô thuốc routes
    Route::resource('lo-thuoc', LoThuocController::class);
    Route::post('lo-thuoc/{loThuoc}/adjust-stock', [LoThuocController::class, 'adjustStock'])->name('lo-thuoc.adjust-stock');
    Route::post('lo-thuoc/{loThuoc}/transfer', [LoThuocController::class, 'transfer'])->name('lo-thuoc.transfer');
    
    // Đơn bán lẻ routes
    Route::resource('don-ban-le', DonBanLeController::class);
    
    Route::get('don-ban-le-thuoc-info', [DonBanLeController::class, 'getThuocInfo'])->name('don-ban-le.thuoc-info');
    Route::get('don-ban-le-search-thuoc', [DonBanLeController::class, 'searchThuoc'])->name('don-ban-le.search-thuoc');
    Route::post('don-ban-le/{donBanLe}/cancel', [DonBanLeController::class, 'cancel'])->name('don-ban-le.cancel');
    Route::get('don-ban-le/{donBanLe}/print', [DonBanLeController::class, 'print'])->name('don-ban-le.print');
    Route::get('don-ban-le-report', [DonBanLeController::class, 'report'])->name('don-ban-le.report');
    
    // Báo cáo tồn kho
    Route::get('bao-cao/ton-kho', [BaoCaoTonKhoController::class, 'index'])->name('bao-cao.ton-kho.index');
    
    // Báo cáo khách hàng
    // Báo cáo routes
    Route::get('bao-cao/khach-hang', [BaoCaoKhachHangController::class, 'index'])->name('bao-cao.khach-hang.index');
    Route::get('bao-cao/lo-thuoc', [BaoCaoLoThuocController::class, 'index'])->name('bao-cao.lo-thuoc.index');
    Route::get('bao-cao/thuoc', [BaoCaoThuocController::class, 'index'])->name('bao-cao.thuoc.index');
    Route::get('bao-cao/kho', [BaoCaoKhoController::class, 'index'])->name('bao-cao.kho.index');
});
