# Hệ Thống Quản Lý Hiệu Thuốc - Hướng Dẫn Triển Khai

## Giới Thiệu

Dự án này là một hệ thống quản lý hiệu thuốc được phát triển bằng Laravel 11. Hệ thống cho phép quản lý thuốc, nhóm thuốc, giá thuốc, nhập kho, bán hàng, quản lý khách hàng, nhà cung cấp và người dùng.

## Cấu Trúc Dự Án

Dự án được tổ chức theo kiến trúc MVC chuẩn của Laravel:

### Models
- `NguoiDung`: Quản lý thông tin người dùng hệ thống (Admin, Dược sĩ).
- `Thuoc`: Quản lý thông tin thuốc.
- `NhomThuoc`: Quản lý thông tin nhóm thuốc.
- `GiaThuoc`: Quản lý giá thuốc theo thời gian.
- `LoThuoc`: Quản lý lô thuốc và tồn kho.
- `KhachHang`: Quản lý thông tin khách hàng.
- `NhaCungCap`: Quản lý thông tin nhà cung cấp.
- `Kho`: Quản lý thông tin kho.
- `PhieuNhap`: Quản lý thông tin phiếu nhập kho.
- `ChiTietLoNhap`: Quản lý chi tiết lô nhập kho.
- `DonBanLe`: Quản lý thông tin hóa đơn.
- `ChiTietDonBanLe`: Quản lý chi tiết hóa đơn.

### Controllers
- `AuthController`: Quản lý đăng nhập, đăng xuất.
- `ThuocController`: Quản lý thêm/sửa/xóa thuốc.
- `NhomThuocController`: Quản lý thêm/sửa/xóa nhóm thuốc.
- `GiaThuocController`: Quản lý thêm/sửa/xóa giá thuốc.
- `KhachHangController`: Quản lý thêm/sửa/xóa khách hàng.
- `NhaCungCapController`: Quản lý thêm/sửa/xóa nhà cung cấp.

### Views
- `layouts/app.blade.php`: Layout chung cho hệ thống.
- `auth/login.blade.php`: Trang đăng nhập.
- `dashboard.blade.php`: Trang tổng quan.
- `nhom-thuoc/index.blade.php`: Trang quản lý nhóm thuốc.

## Thiết Lập Dự Án

1. Cài đặt dependencies:
```bash
composer install
```

2. Cấu hình môi trường:
- Copy `.env.example` thành `.env`
- Cấu hình kết nối database trong file `.env`

3. Tạo key ứng dụng:
```bash
php artisan key:generate
```

4. Chạy project:
```bash
php artisan serve
```

## Các Tính Năng

### Đã Hoàn Thiện
- Trang đăng nhập
- Layout tổng thể
- Dashboard
- Quản lý nhóm thuốc

### Cần Hoàn Thiện
- Quản lý thuốc
- Quản lý giá thuốc
- Quản lý khách hàng
- Quản lý nhà cung cấp
- Quản lý kho và lô thuốc
- Quản lý nhập kho
- Quản lý bán lẻ
- Báo cáo

## Quy Tắc Phát Triển
- Sử dụng resource routes cho CRUD.
- Sử dụng FormRequest để validate dữ liệu.
- Sử dụng AJAX/Fetch API để tương tác không reload trang.
- Sử dụng Modal form cho thêm/sửa nhanh.
- Sử dụng Bootstrap 5 cho layout và UI.
