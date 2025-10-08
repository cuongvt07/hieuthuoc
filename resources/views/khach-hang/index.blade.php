@extends('layouts.app')

@section('title', 'Quản Lý Khách Hàng - Hệ Thống Quản Lý Hiệu Thuốc')

@section('page-title', 'Quản Lý Khách Hàng')

@section('styles')
<style>
    .order-item {
        border-left: 3px solid #4e73df;
        padding-left: 10px;
        margin-bottom: 10px;
    }
    
    .order-date {
        color: #4e73df;
        font-weight: bold;
    }
    
    .order-details {
        margin-top: 10px;
    }
    
    .order-products {
        margin-top: 5px;
        padding-left: 15px;
    }
    
    .modal-body {
        max-height: 70vh;
        overflow-y: auto;
    }
</style>
@endsection

@section('content')
<div class="row">
    <div class="col-md-12">
        <div class="card shadow mb-4">
            <div class="card-header py-3 d-flex justify-content-between align-items-center">
                <h6 class="m-0 font-weight-bold">Danh Sách Khách Hàng</h6>
                <button type="button" class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#addKhachHangModal">
                    <i class="bi bi-plus-circle me-1"></i> Thêm Khách Hàng
                </button>
            </div>
            <div class="card-body">
                <div class="row mb-3">
                    <div class="col-md-6">
                        <div class="input-group">
                            <input type="text" id="search-input" class="form-control" placeholder="Tìm theo số điện thoại hoặc họ tên...">
                            <button class="btn btn-primary" type="button" id="searchBtn">
                                <i class="bi bi-search"></i> Tìm kiếm
                            </button>
                            <button class="btn btn-outline-danger" type="button" id="resetSearchBtn" title="Xóa tìm kiếm">
                                <i class="bi bi-x-circle"></i>
                            </button>
                        </div>
                    </div>
                </div>
                
                <div class="table-responsive">
                    <table class="table table-bordered table-striped" id="khach-hang-table" width="100%" cellspacing="0">
                        <thead>
                            <tr>
                                <th>Số Điện Thoại</th>
                                <th>Họ Tên</th>
                                <th>Số Đơn Hàng</th>
                                <th>Thao Tác</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse ($khachHang as $item)
                            <tr>
                                <td>{{ $item->sdt }}</td>
                                <td>{{ $item->ho_ten }}</td>
                                <td class="text-center">{{ $item->donBanLe->count() }}</td>
                                <td class="text-center">
                                    <button type="button" class="btn btn-sm btn-info view-btn" data-id="{{ $item->khach_hang_id }}">
                                        <i class="bi bi-eye"></i> Xem
                                    </button>
                                    <button type="button" class="btn btn-sm btn-warning edit-btn" data-id="{{ $item->khach_hang_id }}">
                                        <i class="bi bi-pencil"></i> Sửa
                                    </button>
                                    <button type="button" class="btn btn-sm btn-warning suspend-btn" 
                                        data-id="{{ $item->khach_hang_id }}" 
                                        data-ten="{{ $item->ho_ten }}" data-status="{{ $item->trang_thai }}">
                                        <i class="bi bi-ban"></i> {{ $item->trang_thai == 1 ? 'Đình chỉ' : 'Bỏ đình chỉ' }}
                                    </button>
                                </td>
                            </tr>
                            @empty
                            <tr>
                                <td colspan="4" class="text-center">Không có dữ liệu</td>
                            </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
                
                <div class="d-flex justify-content-center mt-3" id="pagination">
                    {{ $khachHang->links() }}
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Modal thêm khách hàng -->
<div class="modal fade" id="addKhachHangModal" tabindex="-1" aria-labelledby="addKhachHangModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <form id="addKhachHangForm">
                <div class="modal-header">
                    <h5 class="modal-title" id="addKhachHangModalLabel">Thêm Khách Hàng</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <div class="mb-3">
                        <label for="sdt" class="form-label">Số Điện Thoại <span class="text-danger">*</span></label>
                        <input type="text" class="form-control" id="sdt" name="sdt" requiredmsg="Trường này yêu cầu bắt buộc">
                        <div class="invalid-feedback" id="sdt_error"></div>
                    </div>
                    <div class="mb-3">
                        <label for="ho_ten" class="form-label">Họ Tên <span class="text-danger">*</span></label>
                        <input type="text" class="form-control" id="ho_ten" name="ho_ten" requiredmsg="Trường này yêu cầu bắt buộc">
                        <div class="invalid-feedback" id="ho_ten_error"></div>
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

<!-- Modal sửa khách hàng -->
<div class="modal fade" id="editKhachHangModal" tabindex="-1" aria-labelledby="editKhachHangModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <form id="editKhachHangForm">
                <div class="modal-header">
                    <h5 class="modal-title" id="editKhachHangModalLabel">Chỉnh Sửa Khách Hàng</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <input type="hidden" id="edit_khach_hang_id" name="khach_hang_id">
                    <div class="mb-3">
                        <label for="edit_sdt" class="form-label">Số Điện Thoại <span class="text-danger">*</span></label>
                        <input type="text" class="form-control" id="edit_sdt" name="sdt" requiredmsg="Trường này yêu cầu bắt buộc">
                        <div class="invalid-feedback" id="edit_sdt_error"></div>
                    </div>
                    <div class="mb-3">
                        <label for="edit_ho_ten" class="form-label">Họ Tên <span class="text-danger">*</span></label>
                        <input type="text" class="form-control" id="edit_ho_ten" name="ho_ten" requiredmsg="Trường này yêu cầu bắt buộc">
                        <div class="invalid-feedback" id="edit_ho_ten_error"></div>
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

<!-- Modal xem chi tiết khách hàng -->
<div class="modal fade" id="viewKhachHangModal" tabindex="-1" aria-labelledby="viewKhachHangModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="viewKhachHangModalLabel">Chi Tiết Khách Hàng</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <div class="card mb-4">
                    <div class="card-header py-2">
                        <h6 class="m-0 font-weight-bold">Thông Tin Khách Hàng</h6>
                    </div>
                    <div class="card-body">
                        <div class="row">
                            <div class="col-md-6">
                                <p><strong>Họ Tên:</strong> <span id="view_ho_ten"></span></p>
                            </div>
                            <div class="col-md-6">
                                <p><strong>Số Điện Thoại:</strong> <span id="view_sdt"></span></p>
                            </div>
                        </div>
                    </div>
                </div>
                
                <div class="card">
                    <div class="card-header py-2 d-flex justify-content-between align-items-center">
                        <h6 class="m-0 font-weight-bold">Lịch Sử Đơn Hàng</h6>
                        <span class="badge bg-primary" id="don_hang_count">0 đơn</span>
                    </div>
                    <div class="card-body">
                        <div id="don_hang_list" class="timeline">
                            <!-- Danh sách đơn hàng sẽ được render bởi JavaScript -->
                        </div>
                    </div>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Đóng</button>
            </div>
        </div>
    </div>
</div>

<!-- Modal xác nhận xóa khách hàng -->
<div class="modal fade" id="deleteKhachHangModal" tabindex="-1" aria-labelledby="deleteKhachHangModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="deleteKhachHangModalLabel">Xác nhận xóa</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <p>Bạn có chắc chắn muốn xóa khách hàng <span id="delete_ho_ten" class="fw-bold"></span>?</p>
                <p class="text-danger mb-0">Lưu ý: Nếu khách hàng đã có đơn hàng thì không thể xóa.</p>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Hủy</button>
                <button type="button" class="btn btn-danger" id="confirmDelete">Xóa</button>
            </div>
        </div>
    </div>
</div>
@endsection

@section('scripts')
<script>
    $(document).ready(function() {
        // Hiển thị loading spinner
        function showLoading(element) {
            const loadingHtml = `
                <div class="text-center py-4">
                    <div class="spinner-border text-primary" role="status">
                        <span class="visually-hidden">Đang tải...</span>
                    </div>
                    <p class="mt-2">Đang tải dữ liệu...</p>
                </div>
            `;
            element.html(loadingHtml);
        }
        
        // Format date từ yyyy-mm-dd thành dd/mm/yyyy
        function formatDate(dateString) {
            if (!dateString) return '';
            const date = new Date(dateString);
            return date.toLocaleDateString('vi-VN');
        }
        
        // Format giá tiền
        function formatMoney(amount) {
            return parseInt(amount).toLocaleString('vi-VN') + ' đ';
        }
        
        // Tìm kiếm khách hàng
        $('#searchBtn').click(function() {
            const searchValue = $('#search-input').val();
            loadKhachHang(1, searchValue);
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
            loadKhachHang();
        });
        
        // Hàm load danh sách khách hàng
        function loadKhachHang(page = 1, search = '') {
            const tableBody = $('#khach-hang-table tbody');
            showLoading(tableBody);
            
            $.ajax({
                url: "{{ route('khach-hang.index') }}",
                type: "GET",
                data: {
                    page: page,
                    search: search
                },
                dataType: "json",
                success: function(response) {
                    let html = '';
                    
                    if (response.khachHang.data.length > 0) {
                        $.each(response.khachHang.data, function(index, item) {
                            html += `
                                <tr>
                                    <td>${item.sdt}</td>
                                    <td>${item.ho_ten}</td>
                                    <td class="text-center">${item.don_ban_le_count || 0}</td>
                                    <td class="text-center">
                                        <button type="button" class="btn btn-sm btn-info view-btn" data-id="${item.khach_hang_id}">
                                            <i class="bi bi-eye"></i> Xem
                                        </button>
                                        <button type="button" class="btn btn-sm btn-warning edit-btn" data-id="${item.khach_hang_id}">
                                            <i class="bi bi-pencil"></i> Sửa
                                        </button>
                                        <button type="button" class="btn btn-sm btn-danger delete-btn" 
                                            data-id="${item.khach_hang_id}" 
                                            data-ten="${item.ho_ten}">
                                            <i class="bi bi-trash"></i> Xóa
                                        </button>
                                    </td>
                                </tr>
                            `;
                        });
                    } else {
                        html = '<tr><td colspan="4" class="text-center">Không có dữ liệu</td></tr>';
                    }
                    
                    tableBody.html(html);
                    $('#pagination').html(response.links);

                    // Rebind pagination links
                    $('#pagination').on('click', '.pagination a', function(e) {
                        e.preventDefault();
                        const page = $(this).attr('href').split('page=')[1];
                        loadKhachHang(page, search);
                    });

                    // Rebind buttons
                    bindButtons();
                },
                error: function() {
                    tableBody.html('<tr><td colspan="4" class="text-center text-danger">Đã xảy ra lỗi khi tải dữ liệu</td></tr>');
                    showToast('Đã xảy ra lỗi khi tải dữ liệu khách hàng', 'danger');
                }
            });
        }
        
        // Thêm khách hàng
        $('#addKhachHangForm').submit(function(e) {
            e.preventDefault();
            
            // Lấy dữ liệu từ form
            const formData = new FormData(this);
            
            // Chuyển FormData thành đối tượng
            const data = {};
            formData.forEach((value, key) => {
                data[key] = value;
            });
            
            $.ajax({
                url: "{{ route('khach-hang.store') }}",
                type: "POST",
                data: data,
                dataType: "json",
                headers: {
                    'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                },
                success: function(response) {
                    $('#addKhachHangModal').modal('hide');
                    $('#addKhachHangForm')[0].reset();
                    showToast(response.message);
                    loadKhachHang();
                },
                error: function(xhr) {
                    const errors = xhr.responseJSON.errors;
                    
                    // Xóa tất cả invalid feedback trước
                    $('#addKhachHangForm .is-invalid').removeClass('is-invalid');
                    
                    // Hiển thị lỗi validation
                    if (errors) {
                        Object.keys(errors).forEach(function(key) {
                            $(`#${key}`).addClass('is-invalid');
                            $(`#${key}_error`).text(errors[key][0]);
                        });
                    }
                }
            });
        });

        // Lấy thông tin khách hàng để sửa
        function getKhachHang(id) {
            $.ajax({
                url: `/khach-hang/${id}`,
                type: "GET",
                dataType: "json",
                success: function(response) {
                    const khachHang = response.khachHang;
                    $('#edit_khach_hang_id').val(khachHang.khach_hang_id);
                    $('#edit_sdt').val(khachHang.sdt);
                    $('#edit_ho_ten').val(khachHang.ho_ten);
                    
                    $('#editKhachHangModal').modal('show');
                },
                error: function(xhr) {
                    showToast('Có lỗi xảy ra khi lấy thông tin khách hàng', 'danger');
                }
            });
        }

        // Xem chi tiết khách hàng
        function viewKhachHang(id) {
            $.ajax({
                url: `/khach-hang/${id}`,
                type: "GET",
                dataType: "json",
                success: function(response) {
                    const khachHang = response.khachHang;
                    const donHang = response.donHang;
                    
                    // Hiển thị thông tin khách hàng
                    $('#view_ho_ten').text(khachHang.ho_ten);
                    $('#view_sdt').text(khachHang.sdt);
                    
                    // Hiển thị số lượng đơn hàng
                    $('#don_hang_count').text(donHang.length + ' đơn');
                    
                    // Render danh sách đơn hàng
                    let donHangHtml = '';
                    if (donHang.length > 0) {
                        $.each(donHang, function(index, item) {
                            const ngayBan = formatDate(item.ngay_ban);
                            
                            donHangHtml += `
                                <div class="order-item">
                                    <div class="d-flex justify-content-between align-items-center">
                                        <div class="order-date">${ngayBan}</div>
                                        <div class="order-total fw-bold">${formatMoney(item.tong_cong)}</div>
                                    </div>
                                    <div class="order-code">Mã đơn: <strong>${item.ma_don}</strong></div>
                                    <div class="order-details">
                                        <div class="fw-bold">Danh sách thuốc:</div>
                                        <ul class="order-products">
                            `;
                            
                            // Render chi tiết đơn
                            if (item.chi_tiet_don_ban_le && item.chi_tiet_don_ban_le.length > 0) {
                                $.each(item.chi_tiet_don_ban_le, function(i, chiTiet) {
                                    donHangHtml += `
                                        <li>
                                            ${chiTiet.lo_thuoc.thuoc.ten_thuoc} - 
                                            ${chiTiet.so_luong} ${chiTiet.don_vi_tinh} x 
                                            ${formatMoney(chiTiet.don_gia)} = 
                                            ${formatMoney(chiTiet.thanh_tien)}
                                        </li>
                                    `;
                                });
                            } else {
                                donHangHtml += '<li>Không có dữ liệu chi tiết</li>';
                            }
                            
                            donHangHtml += `
                                        </ul>
                                    </div>
                                </div>
                            `;
                        });
                    } else {
                        donHangHtml = '<div class="alert alert-info">Khách hàng chưa có đơn hàng nào</div>';
                    }
                    
                    $('#don_hang_list').html(donHangHtml);
                    $('#viewKhachHangModal').modal('show');
                },
                error: function(xhr) {
                    showToast('Có lỗi xảy ra khi lấy thông tin chi tiết khách hàng', 'danger');
                }
            });
        }

        // Cập nhật khách hàng
        $('#editKhachHangForm').submit(function(e) {
            e.preventDefault();
            
            const id = $('#edit_khach_hang_id').val();
            
            // Lấy dữ liệu từ form
            const formData = new FormData(this);
            
            // Chuyển FormData thành đối tượng
            const data = {};
            formData.forEach((value, key) => {
                data[key] = value;
            });
            
            $.ajax({
                url: `/khach-hang/${id}`,
                type: "PUT",
                data: data,
                dataType: "json",
                headers: {
                    'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                },
                success: function(response) {
                    $('#editKhachHangModal').modal('hide');
                    showToast(response.message);
                    loadKhachHang();
                },
                error: function(xhr) {
                    const errors = xhr.responseJSON.errors;
                    
                    // Xóa tất cả invalid feedback trước
                    $('#editKhachHangForm .is-invalid').removeClass('is-invalid');
                    
                    // Hiển thị lỗi validation
                    if (errors) {
                        Object.keys(errors).forEach(function(key) {
                            $(`#edit_${key}`).addClass('is-invalid');
                            $(`#edit_${key}_error`).text(errors[key][0]);
                        });
                    }
                }
            });
        });

        // Xóa khách hàng
        let deleteId = null;
        
        function bindButtons() {
            // Nút xem chi tiết khách hàng
            $('.view-btn').click(function() {
                const id = $(this).data('id');
                viewKhachHang(id);
            });
            
            // Nút sửa khách hàng
            $('.edit-btn').click(function() {
                const id = $(this).data('id');
                getKhachHang(id);
            });
            
            // Nút xóa khách hàng
            $('.delete-btn').click(function() {
                deleteId = $(this).data('id');
                const hoTen = $(this).data('ten');
                
                $('#delete_ho_ten').text(hoTen);
                $('#deleteKhachHangModal').modal('show');
            });
            
            // Đình chỉ/bỏ đình chỉ khách hàng
            $('.suspend-btn').click(function() {
                var id = $(this).data('id');
                var status = $(this).data('status');
                var btn = $(this);
                $.ajax({
                    url: '/khach-hang/' + id + '/suspend',
                    type: 'POST',
                    data: {_token: $('meta[name="csrf-token"]').attr('content')},
                    success: function(res) {
                        if(res.success) {
                            btn.data('status', res.trang_thai);
                            btn.html('<i class="bi bi-ban"></i> ' + (res.trang_thai == 1 ? 'Bỏ đình chỉ' : 'Đình chỉ'));
                            showToast(res.message, 'info');
                        }
                    }
                });
            });
        }
        
        // Xác nhận xóa khách hàng
        $('#confirmDelete').click(function() {
            if (!deleteId) return;
            
            $.ajax({
                url: `/khach-hang/${deleteId}`,
                type: "DELETE",
                dataType: "json",
                headers: {
                    'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                },
                success: function(response) {
                    $('#deleteKhachHangModal').modal('hide');
                    showToast(response.message);
                    loadKhachHang();
                },
                error: function(xhr) {
                    $('#deleteKhachHangModal').modal('hide');
                    showToast(xhr.responseJSON.message, 'danger');
                }
            });
        });

        // Tìm khách hàng theo số điện thoại
        function findCustomerByPhone(phone) {
            return $.ajax({
                url: "{{ route('khach-hang.findByPhone') }}",
                type: "GET",
                data: {
                    sdt: phone
                },
                dataType: "json"
            });
        }

        // Khởi tạo
        bindButtons();
        
        // Clear form khi đóng modal
        $('#addKhachHangModal').on('hidden.bs.modal', function() {
            $('#addKhachHangForm')[0].reset();
            $('#addKhachHangForm .is-invalid').removeClass('is-invalid');
        });
        
        $('#editKhachHangModal').on('hidden.bs.modal', function() {
            $('#editKhachHangForm .is-invalid').removeClass('is-invalid');
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
            
            // Thêm toast vào container và tự động xóa sau 3 giây
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
        
        // Expose function để có thể gọi từ bên ngoài
        window.khachHangModule = {
            findByPhone: findCustomerByPhone
        };
    });
</script>
@endsection
