@extends('layouts.app')

@section('title', 'Quản Lý Nhân Sự - Hệ Thống Quản Lý Hiệu Thuốc')

@section('page-title', 'Quản Lý Nhân Sự')

@section('styles')
<style>
    .user-avatar {
        width: 40px;
        height: 40px;
        border-radius: 50%;
        background-color: #4e73df;
        color: white;
        display: flex;
        align-items: center;
        justify-content: center;
        font-weight: bold;
        font-size: 16px;
    }
    
    .badge-inactive {
        background-color: #858796;
    }
    
    .card-header-custom {
        background-color: #f8f9fc;
        border-bottom: 1px solid #e3e6f0;
    }
    
    .table-container {
        position: relative;
        min-height: 100px;
    }
    
    .loading-overlay {
        position: absolute;
        top: 0;
        left: 0;
        width: 100%;
        height: 100%;
        background: rgba(255,255,255,0.8);
        display: flex;
        align-items: center;
        justify-content: center;
        z-index: 10;
    }
</style>
@endsection

@section('content')
<div class="row mb-4">
    <div class="col-12 text-end">
        <button type="button" class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#addUserModal">
            <i class="bi bi-plus-circle me-1"></i> Thêm Nhân Sự
        </button>
    </div>
</div>

<!-- Danh sách Admin -->
<div class="row mb-4">
    <div class="col-12">
        <div class="card shadow">
            <div class="card-header py-3 card-header-custom">
                <div class="d-flex justify-content-between align-items-center">
                    <h6 class="m-0 font-weight-bold text-primary">
                        <i class="bi bi-shield-lock me-1"></i> Quản Trị Viên
                    </h6>
                    <span class="badge bg-primary">{{ count($admins) }} người</span>
                </div>
            </div>
            <div class="card-body">
                <div class="table-container">
                    <div class="table-responsive">
                        <table class="table table-striped" id="admin-table">
                            <thead>
                                <tr>
                                    <th style="width: 50px;"></th>
                                    <th>Tên Đăng Nhập</th>
                                    <th>Email</th>
                                    <th>Số Điện Thoại</th>
                                    <th>Trạng Thái</th>
                                    <th style="width: 300px;">Thao Tác</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse ($admins as $admin)
                                <tr>
                                    <td>
                                        <div class="user-avatar" style="background-color: #4e73df;">
                                            {{ substr($admin->ho_ten, 0, 1) }}
                                        </div>
                                    </td>
                                    <td>{{ $admin->ten_dang_nhap }}</td>
                                    <td>{{ $admin->email }}</td>
                                    <td>{{ $admin->sdt ?? 'Chưa cập nhật' }}</td>
                                    <td>
                                        @if($admin->trang_thai)
                                            <span class="badge bg-success">Hoạt động</span>
                                        @else
                                            <span class="badge badge-inactive text-white">Khóa</span>
                                        @endif
                                    </td>
                                    <td>
                                        <div class="btn-group" role="group" style="
    display: flex;
    gap: 10px; ">
                                            <button type="button" class="btn btn-sm btn-warning edit-btn" data-id="{{ $admin->nguoi_dung_id }}">
                                                <i class="bi bi-pencil"></i> Sửa
                                            </button>
                                            <button type="button" class="btn btn-sm btn-info change-password-btn" data-id="{{ $admin->nguoi_dung_id }}" data-name="{{ $admin->ho_ten }}">
                                                <i class="bi bi-key"></i> 
                                            </button>
                                            <button type="button" class="btn btn-sm btn-danger delete-btn" data-id="{{ $admin->nguoi_dung_id }}" data-name="{{ $admin->ho_ten }}">
                                                <i class="bi bi-ban"></i> {{ $admin->trang_thai == 1 ? 'Đình chỉ' : 'Bỏ đình chỉ' }}
                                            </button>
                                        </div>
                                    </td>
                                </tr>
                                @empty
                                <tr>
                                    <td colspan="7" class="text-center">Không có dữ liệu</td>
                                </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Danh sách Dược Sĩ -->
<div class="row">
    <div class="col-12">
        <div class="card shadow">
            <div class="card-header py-3 card-header-custom">
                <div class="d-flex justify-content-between align-items-center">
                    <h6 class="m-0 font-weight-bold text-success">
                        <i class="bi bi-prescription2 me-1"></i> Dược Sĩ
                    </h6>
                    <span class="badge bg-success">{{ count($duocSis) }} người</span>
                </div>
            </div>
            <div class="card-body">
                <div class="table-container">
                    <div class="table-responsive">
                        <table class="table table-striped" id="duoc-si-table">
                            <thead>
                                <tr>
                                    <th style="width: 50px;"></th>
                                    <th>Tên Đăng Nhập</th>
                                    <th>Họ Tên</th>
                                    <th>Email</th>
                                    <th>Số Điện Thoại</th>
                                    <th>Trạng Thái</th>
                                    <th style="width: 250px;">Thao Tác</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse ($duocSis as $duocSi)
                                <tr>
                                    <td>
                                        <div class="user-avatar" style="background-color: #1cc88a;">
                                            {{ substr($duocSi->ho_ten, 0, 1) }}
                                        </div>
                                    </td>
                                    <td>{{ $duocSi->ten_dang_nhap }}</td>
                                    <td>{{ $duocSi->ho_ten }}</td>
                                    <td>{{ $duocSi->email }}</td>
                                    <td>{{ $duocSi->sdt ?? 'Chưa cập nhật' }}</td>
                                    <td>
                                        @if($duocSi->trang_thai)
                                            <span class="badge bg-success">Hoạt động</span>
                                        @else
                                            <span class="badge badge-inactive text-white">Khóa</span>
                                        @endif
                                    </td>
                                    <td>
                                        <div class="btn-group" role="group" style="
    display: flex;
    gap: 10px;">
                                            <button type="button" class="btn btn-sm btn-warning edit-btn" data-id="{{ $duocSi->nguoi_dung_id }}">
                                                <i class="bi bi-pencil"></i> Sửa
                                            </button>
                                            <button type="button" class="btn btn-sm btn-info change-password-btn" data-id="{{ $duocSi->nguoi_dung_id }}" data-name="{{ $duocSi->ho_ten }}">
                                                <i class="bi bi-key"></i>
                                            </button>
                                            <button type="button" class="btn btn-sm btn-danger delete-btn" data-id="{{ $duocSi->nguoi_dung_id }}" data-name="{{ $duocSi->ho_ten }}">
                                                <i class="bi bi-ban"></i> {{ $duocSi->trang_thai == 1 ? 'Đình chỉ' : 'Bỏ đình chỉ' }}
                                            </button>
                                        </div>
                                    </td>
                                </tr>
                                @empty
                                <tr>
                                    <td colspan="7" class="text-center">Không có dữ liệu</td>
                                </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Modal thêm người dùng -->
<div class="modal fade" id="addUserModal" tabindex="-1" aria-labelledby="addUserModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <form id="addUserForm">
                <div class="modal-header">
                    <h5 class="modal-title" id="addUserModalLabel">Thêm Nhân Sự Mới</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label for="ten_dang_nhap" class="form-label">Tên Đăng Nhập <span class="text-danger">*</span></label>
                            <input type="text" class="form-control" id="ten_dang_nhap" name="ten_dang_nhap" required>
                            <div class="invalid-feedback" id="ten_dang_nhap_error"></div>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label for="ho_ten" class="form-label">Họ Tên <span class="text-danger">*</span></label>
                            <input type="text" class="form-control" id="ho_ten" name="ho_ten" required>
                            <div class="invalid-feedback" id="ho_ten_error"></div>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label for="email" class="form-label">Email <span class="text-danger">*</span></label>
                            <input type="email" class="form-control" id="email" name="email" required>
                            <div class="invalid-feedback" id="email_error"></div>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label for="sdt" class="form-label">Số Điện Thoại</label>
                            <input type="text" class="form-control" id="sdt" name="sdt">
                            <div class="invalid-feedback" id="sdt_error"></div>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label for="vai_tro" class="form-label">Vai Trò <span class="text-danger">*</span></label>
                            <select class="form-select" id="vai_tro" name="vai_tro" required>
                                <option value="">-- Chọn vai trò --</option>
                                <option value="admin">Quản Trị Viên</option>
                                <option value="duoc_si">Dược Sĩ</option>
                            </select>
                            <div class="invalid-feedback" id="vai_tro_error"></div>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Trạng Thái <span class="text-danger">*</span></label>
                            <div>
                                <div class="form-check form-check-inline">
                                    <input class="form-check-input" type="radio" name="trang_thai" id="trang_thai_1" value="1" checked>
                                    <label class="form-check-label" for="trang_thai_1">Hoạt động</label>
                                </div>
                                <div class="form-check form-check-inline">
                                    <input class="form-check-input" type="radio" name="trang_thai" id="trang_thai_0" value="0">
                                    <label class="form-check-label" for="trang_thai_0">Khóa</label>
                                </div>
                            </div>
                            <div class="invalid-feedback" id="trang_thai_error"></div>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label for="mat_khau" class="form-label">Mật Khẩu <span class="text-danger">*</span></label>
                            <div class="input-group">
                                <input type="password" class="form-control" id="mat_khau" name="mat_khau" required>
                                <button class="btn btn-outline-secondary toggle-password" type="button" data-target="mat_khau">
                                    <i class="bi bi-eye"></i>
                                </button>
                            </div>
                            <div class="invalid-feedback" id="mat_khau_error"></div>
                            <small class="form-text text-muted">Mật khẩu phải có ít nhất 6 ký tự.</small>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Đóng</button>
                    <button type="submit" class="btn btn-primary">Lưu</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Modal sửa người dùng -->
<div class="modal fade" id="editUserModal" tabindex="-1" aria-labelledby="editUserModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <form id="editUserForm">
                <div class="modal-header">
                    <h5 class="modal-title" id="editUserModalLabel">Sửa Thông Tin Nhân Sự</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <input type="hidden" id="edit_nguoi_dung_id" name="nguoi_dung_id">
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label for="edit_ten_dang_nhap" class="form-label">Tên Đăng Nhập <span class="text-danger">*</span></label>
                            <input type="text" class="form-control" id="edit_ten_dang_nhap" name="ten_dang_nhap" required>
                            <div class="invalid-feedback" id="edit_ten_dang_nhap_error"></div>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label for="edit_ho_ten" class="form-label">Họ Tên <span class="text-danger">*</span></label>
                            <input type="text" class="form-control" id="edit_ho_ten" name="ho_ten" required>
                            <div class="invalid-feedback" id="edit_ho_ten_error"></div>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label for="edit_email" class="form-label">Email <span class="text-danger">*</span></label>
                            <input type="email" class="form-control" id="edit_email" name="email" required>
                            <div class="invalid-feedback" id="edit_email_error"></div>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label for="edit_sdt" class="form-label">Số Điện Thoại</label>
                            <input type="text" class="form-control" id="edit_sdt" name="sdt">
                            <div class="invalid-feedback" id="edit_sdt_error"></div>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label for="edit_vai_tro" class="form-label">Vai Trò <span class="text-danger">*</span></label>
                            <select class="form-select" id="edit_vai_tro" name="vai_tro" required>
                                <option value="admin">Quản Trị Viên</option>
                                <option value="duoc_si">Dược Sĩ</option>
                            </select>
                            <div class="invalid-feedback" id="edit_vai_tro_error"></div>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Trạng Thái <span class="text-danger">*</span></label>
                            <div>
                                <div class="form-check form-check-inline">
                                    <input class="form-check-input" type="radio" name="trang_thai" id="edit_trang_thai_1" value="1">
                                    <label class="form-check-label" for="edit_trang_thai_1">Hoạt động</label>
                                </div>
                                <div class="form-check form-check-inline">
                                    <input class="form-check-input" type="radio" name="trang_thai" id="edit_trang_thai_0" value="0">
                                    <label class="form-check-label" for="edit_trang_thai_0">Khóa</label>
                                </div>
                            </div>
                            <div class="invalid-feedback" id="edit_trang_thai_error"></div>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Đóng</button>
                    <button type="submit" class="btn btn-primary">Lưu thay đổi</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Modal đổi mật khẩu -->
<div class="modal fade" id="changePasswordModal" tabindex="-1" aria-labelledby="changePasswordModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <form id="changePasswordForm">
                <div class="modal-header">
                    <h5 class="modal-title" id="changePasswordModalLabel">Đổi Mật Khẩu</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <input type="hidden" id="change_password_nguoi_dung_id">
                    <p class="mb-3">Đặt mật khẩu mới cho người dùng: <strong id="change_password_user_name"></strong></p>
                    <div class="mb-3">
                        <label for="new_password" class="form-label">Mật Khẩu Mới <span class="text-danger">*</span></label>
                        <div class="input-group">
                            <input type="password" class="form-control" id="new_password" name="mat_khau" required>
                            <button class="btn btn-outline-secondary toggle-password" type="button" data-target="new_password">
                                <i class="bi bi-eye"></i>
                            </button>
                        </div>
                        <div class="invalid-feedback" id="new_password_error"></div>
                        <small class="form-text text-muted">Mật khẩu phải có ít nhất 6 ký tự.</small>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Đóng</button>
                    <button type="submit" class="btn btn-primary">Đổi mật khẩu</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Modal xác nhận xóa người dùng -->
<div class="modal fade" id="deleteUserModal" tabindex="-1" aria-labelledby="deleteUserModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="deleteUserModalLabel">Xác nhận xóa</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <p>Bạn có chắc chắn muốn xóa người dùng <span id="delete_user_name" class="fw-bold"></span>?</p>
                <p class="text-danger mb-0">Lưu ý: Hành động này không thể hoàn tác. Nếu người dùng đã có dữ liệu liên quan trong hệ thống, bạn không thể xóa mà chỉ có thể khóa tài khoản.</p>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Hủy</button>
                <button type="button" class="btn btn-danger" id="confirmDelete">Xóa</button>
            </div>
        </div>
    </div>
</div>

<!-- Khối tìm kiếm -->
<div class="position-fixed bottom-0 end-0 p-3" style="z-index: 5">
    <div class="collapse" id="searchCollapse">
        <div class="card shadow-sm">
            <div class="card-body">
                <div class="input-group">
                    <input type="text" id="search-input" class="form-control" placeholder="Tìm kiếm nhân sự...">
                    <button class="btn btn-primary" type="button" id="searchBtn">
                        <i class="bi bi-search"></i>
                    </button>
                    <button class="btn btn-outline-danger" type="button" id="resetSearchBtn">
                        <i class="bi bi-x-lg"></i>
                    </button>
                </div>
            </div>
        </div>
    </div>
    <button class="btn btn-primary rounded-circle shadow" type="button" data-bs-toggle="collapse" data-bs-target="#searchCollapse" aria-expanded="false" aria-controls="searchCollapse" style="width: 50px; height: 50px;">
        <i class="bi bi-search"></i>
    </button>
</div>
@endsection

@section('scripts')
<script>
    $(document).ready(function() {
        // Hiển thị/ẩn mật khẩu
        $('.toggle-password').click(function() {
            const targetId = $(this).data('target');
            const passwordInput = $(`#${targetId}`);
            const type = passwordInput.attr('type') === 'password' ? 'text' : 'password';
            passwordInput.attr('type', type);
            
            // Thay đổi icon
            const icon = $(this).find('i');
            icon.toggleClass('bi-eye bi-eye-slash');
        });
        
        // Hiển thị loading spinner
        function showLoading() {
            return `
                <div class="text-center py-4">
                    <div class="spinner-border text-primary" role="status">
                        <span class="visually-hidden">Đang tải...</span>
                    </div>
                    <p class="mt-2">Đang tải dữ liệu...</p>
                </div>
            `;
        }
        
        // Lấy vai trò và ID của người dùng hiện tại
        const userRole = '{{ Auth::user()->vai_tro }}'; // Lấy vai trò từ PHP
        const currentUserId = '{{ Auth::id() }}'; // Lấy ID người dùng hiện tại

        // Hàm kiểm tra quyền chỉnh sửa
        function hasEditPermission() {
            return userRole === 'admin';
        }

        // Vô hiệu hóa các nút thao tác nếu không phải admin
        if (!hasEditPermission()) {
            // Ẩn hoặc vô hiệu hóa nút "Thêm Nhân Sự"
            $('#addUserModal').parent().find('.btn-primary').prop('disabled', true).addClass('disabled');

            // Vô hiệu hóa các nút chỉnh sửa, xóa, đình chỉ trong bảng Admin
            $('#admin-table .edit-btn, #admin-table .delete-btn').prop('disabled', true).addClass('disabled');
        }

        // Tìm kiếm người dùng
        $('#searchBtn').click(function() {
            const searchValue = $('#search-input').val();
            if (searchValue.trim() !== '') {
                loadUsers(searchValue);
            }
        });
        
        // Nhấn Enter để tìm kiếm
        $('#search-input').keypress(function(e) {
            if (e.which === 13) {
                $('#searchBtn').click();
            }
        });
        
        // Reset tìm kiếm
        $('#resetSearchBtn').click(function() {
            $('#search-input').val('');
            loadUsers('');
            $('#searchCollapse').collapse('hide');
        });
        
        // Hàm load danh sách người dùng
        function loadUsers(search = '') {
            const adminTable = $('#admin-table tbody');
            const duocSiTable = $('#duoc-si-table tbody');
            
            adminTable.html(showLoading());
            duocSiTable.html(showLoading());
            
            $.ajax({
                url: "{{ route('nguoi-dung.index') }}",
                type: "GET",
                data: {
                    search: search
                },
                dataType: "json",
                success: function(response) {
                    // Render admins
                    let adminHtml = '';
                    if (response.admins.length > 0) {
                        $.each(response.admins, function(index, item) {
                            const isCurrentUser = item.nguoi_dung_id == currentUserId;
                            adminHtml += `
                                <tr>
                                    <td>
                                        <div class="user-avatar" style="background-color: #4e73df;">
                                            ${item.ho_ten.charAt(0)}
                                        </div>
                                    </td>
                                    <td>${item.ten_dang_nhap}</td>
                                    <td>${item.ho_ten}</td>
                                    <td>${item.email}</td>
                                    <td>${item.sdt || 'Chưa cập nhật'}</td>
                                    <td>
                                        ${item.trang_thai 
                                            ? '<span class="badge bg-success">Hoạt động</span>' 
                                            : '<span class="badge badge-inactive text-white">Khóa</span>'}
                                    </td>
                                    <td>
                                        <div class="btn-group" role="group">
                                            <button type="button" class="btn btn-sm btn-warning edit-btn" data-id="${item.nguoi_dung_id}" ${!hasEditPermission() ? 'disabled' : ''}>
                                                <i class="bi bi-pencil"></i> Sửa
                                            </button>
                                            <button type="button" class="btn btn-sm btn-info change-password-btn" data-id="${item.nguoi_dung_id}" data-name="${item.ho_ten}" ${!hasEditPermission() ? 'disabled' : ''}>
                                                <i class="bi bi-key"></i> Đổi Mật Khẩu
                                            </button>
                                            <button type="button" class="btn btn-sm btn-danger delete-btn" data-id="${item.nguoi_dung_id}" data-name="${item.ho_ten}" ${!hasEditPermission() ? 'disabled' : ''}>
                                                <i class="bi bi-ban"></i> ${item.trang_thai == 'hoat_dong' ? 'Đình chỉ' : 'Bỏ đình chỉ'}
                                            </button>
         
                                            </div>
                                    </td>
                                </tr>
                            `;
                        });
                    } else {
                        adminHtml = '<tr><td colspan="7" class="text-center">Không có dữ liệu</td></tr>';
                    }
                    adminTable.html(adminHtml);
                    
                    // Render dược sĩ (chỉ hiển thị tài khoản của người dùng hiện tại nếu không phải admin)
                    let duocSiHtml = '';
                    if (response.duocSis.length > 0) {
                        $.each(response.duocSis, function(index, item) {
                            const isCurrentUser = item.nguoi_dung_id == currentUserId;
                            if (hasEditPermission() || isCurrentUser) {
                                duocSiHtml += `
                                    <tr>
                                        <td>
                                            <div class="user-avatar" style="background-color: #1cc88a;">
                                                ${item.ho_ten.charAt(0)}
                                            </div>
                                        </td>
                                        <td>${item.ten_dang_nhap}</td>
                                        <td>${item.ho_ten}</td>
                                        <td>${item.email}</td>
                                        <td>${item.sdt || 'Chưa cập nhật'}</td>
                                        <td>
                                            ${item.trang_thai 
                                                ? '<span class="badge bg-success">Hoạt động</span>' 
                                                : '<span class="badge badge-inactive text-white">Khóa</span>'}
                                        </td>
                                        <td>
                                            <div class="btn-group" role="group">
                                                <button type="button" class="btn btn-sm btn-warning edit-btn" data-id="${item.nguoi_dung_id}" ${!hasEditPermission() && !isCurrentUser ? 'disabled' : ''}>
                                                    <i class="bi bi-pencil"></i> Sửa
                                                </button>
                                                <button type="button" class="btn btn-sm btn-info change-password-btn" data-id="${item.nguoi_dung_id}" data-name="${item.ho_ten}" ${!hasEditPermission() && !isCurrentUser ? 'disabled' : ''}>
                                                    <i class="bi bi-key"></i> Đổi Mật Khẩu
                                                </button>
                                                <button type="button" class="btn btn-sm btn-danger delete-btn" data-id="${item.nguoi_dung_id}" data-name="${item.ho_ten}" ${!hasEditPermission() ? 'disabled' : ''}>
                                                    <i class="bi bi-ban"></i> ${item.trang_thai == 'hoat_dong' ? 'Đình chỉ' : 'Bỏ đình chỉ'}
                                                </button>
                                            </div>
                                        </td>
                                    </tr>
                                `;
                            }
                        });
                    } else {
                        duocSiHtml = '<tr><td colspan="7" class="text-center">Không có dữ liệu</td></tr>';
                    }
                    duocSiTable.html(duocSiHtml);
                    
                    // Rebind buttons
                    bindButtons();
                },
                error: function() {
                    adminTable.html('<tr><td colspan="7" class="text-center text-danger">Đã xảy ra lỗi khi tải dữ liệu</td></tr>');
                    duocSiTable.html('<tr><td colspan="7" class="text-center text-danger">Đã xảy ra lỗi khi tải dữ liệu</td></tr>');
                    showToast('Đã xảy ra lỗi khi tải dữ liệu người dùng', 'danger');
                }
            });
        }
        
        // Thêm người dùng
        $('#addUserForm').submit(function(e) {
            e.preventDefault();
            if (!hasEditPermission()) {
                showToast('Bạn không có quyền thêm nhân sự', 'warning');
                return;
            }
            
            const formData = new FormData(this);
            $.ajax({
                url: "{{ route('nguoi-dung.store') }}",
                type: "POST",
                data: formData,
                dataType: "json",
                processData: false,
                contentType: false,
                headers: {
                    'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                },
                success: function(response) {
                    $('#addUserModal').modal('hide');
                    $('#addUserForm')[0].reset();
                    showToast(response.message);
                    loadUsers();
                },
                error: function(xhr) {
                    const errors = xhr.responseJSON.errors;
                    $('#addUserForm .is-invalid').removeClass('is-invalid');
                    if (errors) {
                        Object.keys(errors).forEach(function(key) {
                            $(`#${key}`).addClass('is-invalid');
                            $(`#${key}_error`).text(errors[key][0]);
                        });
                    }
                }
            });
        });

        // Lấy thông tin người dùng để sửa
        function getUser(id) {
            if (!hasEditPermission() && id != currentUserId) {
                showToast('Bạn không có quyền sửa thông tin này', 'warning');
                return;
            }
            $.ajax({
                url: `/nguoi-dung/${id}`,
                type: "GET",
                dataType: "json",
                success: function(response) {
                    const user = response.nguoiDung;
                    $('#edit_nguoi_dung_id').val(user.nguoi_dung_id);
                    $('#edit_ten_dang_nhap').val(user.ten_dang_nhap);
                    $('#edit_ho_ten').val(user.ho_ten);
                    $('#edit_email').val(user.email);
                    $('#edit_sdt').val(user.sdt || '');
                    $('#edit_vai_tro').val(user.vai_tro);
                    
                    if (user.trang_thai == 1) {
                        $('#edit_trang_thai_1').prop('checked', true);
                    } else {
                        $('#edit_trang_thai_0').prop('checked', true);
                    }
                    
                    $('#editUserModal').modal('show');
                },
                error: function(xhr) {
                    showToast('Có lỗi xảy ra khi lấy thông tin người dùng', 'danger');
                }
            });
        }

        // Cập nhật người dùng
        $('#editUserForm').submit(function(e) {
            e.preventDefault();
            const id = $('#edit_nguoi_dung_id').val();
            if (!hasEditPermission() && id != currentUserId) {
                showToast('Bạn không có quyền sửa thông tin này', 'warning');
                return;
            }
            
            const formData = new FormData(this);
            $.ajax({
                url: `/nguoi-dung/${id}`,
                type: "PUT",
                data: formData,
                dataType: "json",
                processData: false,
                contentType: false,
                headers: {
                    'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                },
                success: function(response) {
                    $('#editUserModal').modal('hide');
                    showToast(response.message);
                    loadUsers();
                },
                error: function(xhr) {
                    const errors = xhr.responseJSON.errors;
                    $('#editUserForm .is-invalid').removeClass('is-invalid');
                    if (errors) {
                        Object.keys(errors).forEach(function(key) {
                            $(`#edit_${key}`).addClass('is-invalid');
                            $(`#edit_${key}_error`).text(errors[key][0]);
                        });
                    }
                }
            });
        });

        // Đổi mật khẩu
        let passwordUserId = null;
        
        // Mở modal đổi mật khẩu
        $(document).on('click', '.change-password-btn', function() {
            const id = $(this).data('id');
            if (!hasEditPermission() && id != currentUserId) {
                showToast('Bạn không có quyền đổi mật khẩu cho người dùng này', 'warning');
                return;
            }
            passwordUserId = id;
            const userName = $(this).data('name');
            $('#change_password_nguoi_dung_id').val(passwordUserId);
            $('#change_password_user_name').text(userName);
            $('#changePasswordModal').modal('show');
        });
        
        // Submit đổi mật khẩu
        $('#changePasswordForm').submit(function(e) {
            e.preventDefault();
            const id = $('#change_password_nguoi_dung_id').val();
            if (!hasEditPermission() && id != currentUserId) {
                showToast('Bạn không có quyền đổi mật khẩu cho người dùng này', 'warning');
                return;
            }
            
            const newPassword = $('#new_password').val();
            $.ajax({
                url: `/nguoi-dung/${id}/doi-mat-khau`,
                type: "PUT",
                data: {
                    mat_khau: newPassword
                },
                dataType: "json",
                headers: {
                    'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                },
                success: function(response) {
                    $('#changePasswordModal').modal('hide');
                    $('#changePasswordForm')[0].reset();
                    showToast(response.message);
                },
                error: function(xhr) {
                    const errors = xhr.responseJSON.errors;
                    $('#new_password').removeClass('is-invalid');
                    if (errors && errors.mat_khau) {
                        $('#new_password').addClass('is-invalid');
                        $('#new_password_error').text(errors.mat_khau[0]);
                    }
                }
            });
        });

        // Xóa người dùng
        let deleteId = null;
        
        function bindButtons() {
            // Nút sửa người dùng
            $('.edit-btn').click(function() {
                const id = $(this).data('id');
                getUser(id);
            });
            
            // Nút xóa người dùng
            $('.delete-btn').click(function() {
                const id = $(this).data('id');
                if (!hasEditPermission() || id != currentUserId) {
                    showToast('Bạn không có quyền xóa người dùng này', 'warning');
                    return;
                }
                deleteId = id;
                const userName = $(this).data('name');
                $('#delete_user_name').text(userName);
                $('#deleteUserModal').modal('show');
            });
        }
        
        // Xác nhận xóa người dùng
        $('#confirmDelete').click(function() {
            if (!deleteId || !hasEditPermission()) {
                showToast('Bạn không có quyền xóa người dùng này', 'warning');
                $('#deleteUserModal').modal('hide');
                return;
            }
            
            $.ajax({
                url: `/nguoi-dung/${deleteId}`,
                type: "DELETE",
                dataType: "json",
                headers: {
                    'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                },
                success: function(response) {
                    $('#deleteUserModal').modal('hide');
                    showToast(response.message);
                    loadUsers();
                },
                error: function(xhr) {
                    $('#deleteUserModal').modal('hide');
                    showToast(xhr.responseJSON.message, 'danger');
                }
            });
        });

        // Khởi tạo
        bindButtons();
        loadUsers();
        
        // Clear form khi đóng modal
        $('#addUserModal').on('hidden.bs.modal', function() {
            $('#addUserForm')[0].reset();
            $('#addUserForm .is-invalid').removeClass('is-invalid');
        });
        
        $('#editUserModal').on('hidden.bs.modal', function() {
            $('#editUserForm .is-invalid').removeClass('is-invalid');
        });
        
        $('#changePasswordModal').on('hidden.bs.modal', function() {
            $('#changePasswordForm')[0].reset();
            $('#new_password').removeClass('is-invalid');
        });
        
        // Hiển thị thông báo toast
        function showToast(message, type = 'success') {
            const toast = `
                <div class="toast align-items-center text-white bg-${type} border-0 show" role="alert" aria-live="assertive" aria-atomic="true">
                    <div class="d-flex">
                        <div class="toast-body">
                            ${message}
                        </div>
                        <button type="button" class="btn-close btn-close-white me-2 m-auto" data-bs-dismiss="toast" aria-label="Close"></button>
                    </div>
                </div>
            `;
            
            const toastContainer = $('#toast-container');
            if (toastContainer.length === 0) {
                $('body').append('<div id="toast-container" class="toast-container position-fixed top-0 end-0 p-3"></div>');
            }
            
            const toastElement = $(toast);
            $('#toast-container').append(toastElement);
            
            setTimeout(function() {
                toastElement.remove();
            }, 3000);
        }
    });
</script>
@endsection
