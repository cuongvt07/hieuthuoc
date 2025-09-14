@extends('layouts.app')

@section('title', 'Quản Lý Kho - Hệ Thống Quản Lý Hiệu Thuốc')

@section('page-title', 'Quản Lý Kho')

@section('styles')
<style>
    .warehouse-card {
        transition: all 0.3s;
    }
    
    .warehouse-card:hover {
        transform: translateY(-5px);
        box-shadow: 0 0.5rem 1rem rgba(0, 0, 0, 0.15);
    }
    
    .warehouse-stats {
        background-color: rgba(0, 0, 0, 0.03);
        border-top: 1px solid rgba(0, 0, 0, 0.125);
        padding: 0.75rem;
        display: flex;
        justify-content: space-between;
    }
    
    .warehouse-icon {
        font-size: 2.5rem;
        color: #4e73df;
        opacity: 0.5;
    }
    
    .inventory-item {
        border-left: 3px solid #36b9cc;
        padding-left: 10px;
        margin-bottom: 10px;
    }
    
    .expiry-warning {
        border-left-color: #f6c23e;
        background: #ffc1075c;
    }
    
    .expiry-danger {
        border-left-color: #e74a3b;
        background: #dc354545;
    }
    
    .expiry-date {
        font-weight: bold;
    }
    
    .warning-text {
        color: #f6c23e;
    }
    
    .danger-text {
        color: #e74a3b;
    }
</style>
@endsection

@section('content')
<div class="card shadow">
    <div class="card-header py-3 d-flex justify-content-between align-items-center">
        <h6 class="m-0 font-weight-bold">Danh Sách Kho</h6>
        <button type="button" class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#addKhoModal">
            <i class="bi bi-plus-circle me-1"></i> Thêm Kho
        </button>
    </div>
    <div class="card-body">

        <div class="table-responsive">
            <table class="table table-hover">
                <thead>
                    <tr>
                        <th>Tên Kho</th>
                        <th>Địa Chỉ</th>
                        <th class="text-center">Số Loại Thuốc</th>
                        <th class="text-center">Tổng SL Tồn</th>
                        <th class="text-end">Thao Tác</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($khos as $kho)
                    <tr>
                        <td>
                            <strong>{{ $kho->ten_kho }}</strong>
                            @if($kho->ghi_chu)
                            <br><small class="text-muted">{{ $kho->ghi_chu }}</small>
                            @endif
                        </td>
                        <td>{{ $kho->dia_chi ?: 'Chưa cập nhật' }}</td>
                        <td class="text-center">{{ number_format($kho->total_medicines) }}</td>
                        <td class="text-center">{{ number_format($kho->total_items) }}</td>
                        <td class="text-end">
                            <button type="button" class="btn btn-sm btn-info view-btn" data-id="{{ $kho->kho_id }}" title="Xem chi tiết">
                                <i class="bi bi-eye"></i>
                            </button>
                            <button type="button" class="btn btn-sm btn-warning edit-btn" data-id="{{ $kho->kho_id }}" title="Sửa">
                                <i class="bi bi-pencil"></i>
                            </button>
                            <button type="button" class="btn btn-sm btn-danger delete-btn" 
                                data-id="{{ $kho->kho_id }}" 
                                data-ten="{{ $kho->ten_kho }}" 
                                title="Xóa">
                                <i class="bi bi-trash"></i>
                            </button>
                        </td>
                    </tr>
                    <tr>
                        <td colspan="5" class="p-0">
                            <div class="collapse" id="lots-{{ $kho->kho_id }}">
                                <table class="table mb-0">
                                    <thead>
                                        <tr>
                                            <th>Số Lô</th>
                                            <th class="text-center">Số Lượng Tồn</th>
                                            <th>Ngày Sản Xuất</th>
                                            <th>Hạn Sử Dụng</th>
                                            <th>Trạng Thái</th>
                                        </tr>
                                    </thead>
                                    <tbody id="lot-details-{{ $kho->kho_id }}">
                                        <!-- Nội dung sẽ được thêm bằng JavaScript -->
                                    </tbody>
                                </table>
                            </div>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="5" class="text-center">Chưa có kho nào được tạo</td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <div class="d-flex justify-content-center mt-3">
            {{ $khos->links('vendor.pagination.custom') }}
        </div>
    </div>
</div>

<!-- Modal thêm kho -->
<div class="modal fade" id="addKhoModal" tabindex="-1" aria-labelledby="addKhoModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <form id="addKhoForm">
                <div class="modal-header">
                    <h5 class="modal-title" id="addKhoModalLabel">Thêm Kho Mới</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <div class="mb-3">
                        <label for="ten_kho" class="form-label">Tên Kho <span class="text-danger">*</span></label>
                        <input type="text" class="form-control" id="ten_kho" name="ten_kho" required>
                        <div class="invalid-feedback" id="ten_kho_error"></div>
                    </div>
                    <div class="mb-3">
                        <label for="dia_chi" class="form-label">Địa Chỉ</label>
                        <input type="text" class="form-control" id="dia_chi" name="dia_chi">
                        <div class="invalid-feedback" id="dia_chi_error"></div>
                    </div>
                    <div class="mb-3">
                        <label for="ghi_chu" class="form-label">Ghi Chú</label>
                        <textarea class="form-control" id="ghi_chu" name="ghi_chu" rows="3"></textarea>
                        <div class="invalid-feedback" id="ghi_chu_error"></div>
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

<!-- Modal sửa kho -->
<div class="modal fade" id="editKhoModal" tabindex="-1" aria-labelledby="editKhoModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <form id="editKhoForm">
                <div class="modal-header">
                    <h5 class="modal-title" id="editKhoModalLabel">Sửa Thông Tin Kho</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <input type="hidden" id="edit_kho_id" name="kho_id">
                    <div class="mb-3">
                        <label for="edit_ten_kho" class="form-label">Tên Kho <span class="text-danger">*</span></label>
                        <input type="text" class="form-control" id="edit_ten_kho" name="ten_kho" required>
                        <div class="invalid-feedback" id="edit_ten_kho_error"></div>
                    </div>
                    <div class="mb-3">
                        <label for="edit_dia_chi" class="form-label">Địa Chỉ</label>
                        <input type="text" class="form-control" id="edit_dia_chi" name="dia_chi">
                        <div class="invalid-feedback" id="edit_dia_chi_error"></div>
                    </div>
                    <div class="mb-3">
                        <label for="edit_ghi_chu" class="form-label">Ghi Chú</label>
                        <textarea class="form-control" id="edit_ghi_chu" name="ghi_chu" rows="3"></textarea>
                        <div class="invalid-feedback" id="edit_ghi_chu_error"></div>
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

<!-- Modal xem tồn kho -->
<div class="modal fade" id="viewKhoModal" tabindex="-1" aria-labelledby="viewKhoModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-xl">
        <div class="modal-content">
            <div class="modal-header bg-primary text-white">
                <h5 class="modal-title" id="viewKhoModalLabel">
                    <i class="bi bi-box-seam me-2"></i>
                    Chi Tiết Kho: <span id="view_warehouse_name"></span>
                </h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <!-- Thông tin kho -->
                <div class="row mb-4" id="warehouse-info">
                    <!-- Thông tin kho sẽ được thêm vào đây -->
                </div>

                <!-- Danh sách thuốc -->
                <div class="card shadow-sm">
                    <div class="card-header py-3">
                        <div class="row align-items-center">
                            <div class="col">
                                <h6 class="m-0 font-weight-bold">Danh Sách Thuốc Trong Kho</h6>
                            </div>
                        </div>
                    </div>
                    <div class="card-body">
                        <div class="table-responsive">
                            <table class="table table-hover" id="medicines-table">
                                <thead>
                                    <tr>
                                        <th style="width: 120px">Mã Thuốc</th>
                                        <th>Thông Tin Thuốc</th>
                                        <th style="width: 150px" class="text-center">Số Lượng Tồn</th>
                                        <th style="width: 200px">Trạng Thái</th>
                                        <th style="width: 100px" class="text-end">Thao Tác</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <!-- Danh sách thuốc sẽ được thêm vào đây -->
                                </tbody>
                            </table>
                        </div>
                        <div id="medicines-pagination" class="d-flex justify-content-center mt-3">
                            <!-- Phân trang sẽ được thêm vào đây -->
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

<!-- Modal xem chi tiết thuốc -->
<div class="modal fade" id="viewMedicineModal" tabindex="-1" aria-labelledby="viewMedicineModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-xl">
        <div class="modal-content">
            <div class="modal-header">
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <!-- Thông tin tồn kho theo kho -->
                <div class="row mb-4">
                    <div class="col-12">
                        <div class="row" id="inventory-by-warehouse">
                            <!-- Sẽ được điền bằng JavaScript -->
                        </div>
                    </div>
                </div>

                <!-- Chi tiết các lô -->
                <div id="lots-container" class="position-relative">
                    <h6 class="mb-3">Chi Tiết Các Lô:</h6>
                    <div class="table-responsive">
                        <table class="table" id="lots-table">
                            <thead>
                                <tr>
                                    <th>Kho</th>
                                    <th>Số Lô</th>
                                    <th>Số Lượng Tồn</th>
                                    <th>Ngày Sản Xuất</th>
                                    <th>Hạn Sử Dụng</th>
                                    <th>Trạng Thái</th>
                                </tr>
                            </thead>
                            <tbody>
                                <!-- Danh sách lô sẽ được thêm vào đây -->
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Đóng</button>
            </div>
        </div>
    </div>
</div>

<!-- Modal xác nhận xóa kho -->
<div class="modal fade" id="deleteKhoModal" tabindex="-1" aria-labelledby="deleteKhoModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="deleteKhoModalLabel">Xác nhận xóa</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <p>Bạn có chắc chắn muốn xóa kho <span id="delete_ten_kho" class="fw-bold"></span>?</p>
                <p class="text-danger mb-0">Lưu ý: Kho chỉ có thể xóa khi không còn thuốc tồn kho.</p>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Hủy</button>
                <button type="button" class="btn btn-danger" id="confirmDelete">Xóa</button>
            </div>
        </div>
    </div>
</div>

<!-- Modal xem chi tiết các lô thuốc -->
<div class="modal fade" id="viewMedicineLotModal" tabindex="-1" aria-labelledby="viewMedicineLotModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-xl">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="viewMedicineLotModalLabel">Chi Tiết Các Lô Thuốc: <span id="view_medicine_lot_name"></span></h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <div class="table-responsive">
                    <table class="table table-hover" id="medicine-lots-table">
                        <thead>
                            <tr>
                                <th>Số Lô</th>
                                <th class="text-center">Số Lượng Tồn</th>
                                <th>Ngày Sản Xuất</th>
                                <th>Hạn Sử Dụng</th>
                                <th>Trạng Thái</th>
                            </tr>
                        </thead>
                        <tbody>
                            <!-- Nội dung sẽ được thêm bằng JavaScript -->
                        </tbody>
                    </table>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Đóng</button>
            </div>
        </div>
    </div>
</div>
@endsection

@section('scripts')
<script>
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

    // Hiển thị hộp thoại lỗi
    function showErrorDialog(title, message) {
        const errorHtml = `
            <div class="modal fade" id="errorDialog" tabindex="-1" aria-labelledby="errorDialogLabel" aria-hidden="true">
                <div class="modal-dialog">
                    <div class="modal-content">
                        <div class="modal-header bg-danger text-white">
                            <h5 class="modal-title" id="errorDialogLabel">${title}</h5>
                            <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
                        </div>
                        <div class="modal-body">
                            <p>${message}</p>
                        </div>
                        <div class="modal-footer">
                            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Đóng</button>
                        </div>
                    </div>
                </div>
            </div>
        `;

        // Thêm modal vào body nếu chưa tồn tại
        if (!$('#errorDialog').length) {
            $('body').append(errorHtml);
        }

        // Hiển thị modal
        const errorModal = new bootstrap.Modal(document.getElementById('errorDialog'));
        errorModal.show();
    }

    $(document).ready(function() {

        // Xem chi tiết kho
        function viewKho(id) {
            $('#medicines-table tbody').html(showLoading());
            $('#view_warehouse_name').text('');
            
            $.ajax({
                url: `/kho/${id}`,
                type: "GET",
                dataType: "json",
                success: function(response) {
                    console.log('Response:', response); // Debug log
                    
                    const kho = response.kho;
                    $('#view_warehouse_name').text(kho.ten_kho);
                    
                    let tableHtml = '';
                    const thuocs = response.thuocs;
                    
                    if (thuocs && thuocs.data && Array.isArray(thuocs.data) && thuocs.data.length > 0) {
                        thuocs.data.forEach(function(thuoc) {
                            tableHtml += `
                                <tr>
                                    <td>${thuoc.ma_thuoc}</td>
                                    <td>
                                        <strong>${thuoc.ten_thuoc}</strong><br>
                                        <small class="text-muted">${thuoc.nhom_thuoc ? thuoc.nhom_thuoc.ten_nhom : ''}</small>
                                    </td>
                                    <td class="text-center">${thuoc.lo_thuoc_sum_ton_kho_hien_tai} ${thuoc.don_vi_goc}</td>
                                    <td class="text-center">
                                        ${thuoc.lo_thuoc_sum_ton_kho_hien_tai > 0 ? '<span class="badge bg-success">Còn hàng</span>' : '<span class="badge bg-secondary">Hết hàng</span>'}
                                    </td>
                                    <td class="text-end">
                                        <button type="button" class="btn btn-sm btn-info view-lots-btn" 
                                            data-kho-id="${kho.kho_id}" 
                                            data-thuoc-id="${thuoc.thuoc_id}"
                                            data-thuoc-name="${thuoc.ten_thuoc}">
                                            <i class="bi bi-box"></i> Xem lô
                                        </button>
                                    </td>
                                </tr>
                            `;
                        });
                    } else {
                        tableHtml = '<tr><td colspan="5" class="text-center">Không có thuốc nào trong kho này.</td></tr>';
                    }

                    $('#medicines-table tbody').html(tableHtml);
                    $('#medicines-pagination').html(response.links || '');
                    $('#viewKhoModal').modal('show');

                    // Bind click event cho các nút xem chi tiết lô
                    $('.view-lots-btn').click(function() {
                        const khoId = $(this).data('kho-id');
                        const thuocId = $(this).data('thuoc-id');
                        const thuocName = $(this).data('thuoc-name');
                        viewMedicineLots(khoId, thuocId, thuocName);
                    });
                },
                error: function(xhr) {
                    $('#medicines-table tbody').html('<tr><td colspan="4" class="text-center text-danger">Có lỗi xảy ra khi tải dữ liệu</td></tr>');
                    showToast('Có lỗi xảy ra khi tải dữ liệu', 'danger');
                }
            });
        }
        
        // Format date từ yyyy-mm-dd thành dd/mm/yyyy
        function formatDate(dateString) {
            if (!dateString) return '';
            const date = new Date(dateString);
            return date.toLocaleDateString('vi-VN');
        }
        
        // Tính số ngày còn lại đến hạn sử dụng
        function getDaysRemaining(expiryDate) {
            const today = new Date();
            today.setHours(0, 0, 0, 0);
            const expiry = new Date(expiryDate);
            const diffTime = expiry - today;
            return Math.ceil(diffTime / (1000 * 60 * 60 * 24));
        }
        
        // Thêm kho mới
        $('#addKhoForm').submit(function(e) {
            e.preventDefault();
            
            // Lấy dữ liệu từ form
            const formData = new FormData(this);
            
            // Chuyển FormData thành đối tượng
            const data = {};
            formData.forEach((value, key) => {
                data[key] = value;
            });
            
            $.ajax({
                url: "{{ route('kho.store') }}",
                type: "POST",
                data: data,
                dataType: "json",
                headers: {
                    'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                },
                success: function(response) {
                    $('#addKhoModal').modal('hide');
                    $('#addKhoForm')[0].reset();
                    showToast(response.message);
                    // Reload trang để cập nhật danh sách kho
                    location.reload();
                },
                error: function(xhr) {
                    const errors = xhr.responseJSON.errors;
                    
                    // Xóa tất cả invalid feedback trước
                    $('#addKhoForm .is-invalid').removeClass('is-invalid');
                    
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

        // Lấy thông tin kho để sửa
        function getKho(id) {
            $.ajax({
                url: `/kho/${id}`,
                type: "GET",
                dataType: "json",
                success: function(response) {
                    const kho = response.kho;
                    $('#edit_kho_id').val(kho.kho_id);
                    $('#edit_ten_kho').val(kho.ten_kho);
                    $('#edit_dia_chi').val(kho.dia_chi || '');
                    $('#edit_ghi_chu').val(kho.ghi_chu || '');
                    
                    $('#editKhoModal').modal('show');
                },
                error: function(xhr) {
                    showToast('Có lỗi xảy ra khi lấy thông tin kho', 'danger');
                }
            });
        }

        // Cập nhật thông tin kho
        $('#editKhoForm').submit(function(e) {
            e.preventDefault();
            
            const id = $('#edit_kho_id').val();
            
            // Lấy dữ liệu từ form
            const formData = new FormData(this);
            
            // Chuyển FormData thành đối tượng
            const data = {};
            formData.forEach((value, key) => {
                data[key] = value;
            });
            
            $.ajax({
                url: `/kho/${id}`,
                type: "PUT",
                data: data,
                dataType: "json",
                headers: {
                    'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                },
                success: function(response) {
                    $('#editKhoModal').modal('hide');
                    showToast(response.message);
                    // Reload trang để cập nhật danh sách kho
                    location.reload();
                },
                error: function(xhr) {
                    const errors = xhr.responseJSON.errors;
                    
                    // Xóa tất cả invalid feedback trước
                    $('#editKhoForm .is-invalid').removeClass('is-invalid');
                    
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

        // Xem thông tin tồn kho (updated version)
        function viewKhoDetails(id) {
            $('#medicines-table tbody').html(showLoading());
            $('#view_warehouse_name').text('');
            
            $.ajax({
                url: `/kho/${id}`,
                type: "GET",
                dataType: "json",
                success: function(response) {
                    if (!response.kho || !response.thuocs) {
                        showErrorDialog('Lỗi Dữ Liệu', 'Không thể tải thông tin kho. Dữ liệu không hợp lệ.');
                        return;
                    }

                    // Hiển thị thông tin kho
                    const kho = response.kho;
                    $('#view_warehouse_name').text(kho.ten_kho);
                    $('#warehouse-info').html(`
                        <div class="col-md-6 col-lg-3 mb-3">
                            <div class="card h-100 border-primary">
                                <div class="card-body">
                                    <h6 class="card-subtitle mb-2 text-muted">Mã Kho</h6>
                                    <p class="card-text h5">${kho.kho_id}</p>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-6 col-lg-3 mb-3">
                            <div class="card h-100 border-info">
                                <div class="card-body">
                                    <h6 class="card-subtitle mb-2 text-muted">Tên Kho</h6>
                                    <p class="card-text h5">${kho.ten_kho}</p>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-6 col-lg-3 mb-3">
                            <div class="card h-100 border-success">
                                <div class="card-body">
                                    <h6 class="card-subtitle mb-2 text-muted">Địa Chỉ</h6>
                                    <p class="card-text h5">${kho.dia_chi || 'Chưa cập nhật'}</p>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-6 col-lg-3 mb-3">
                            <div class="card h-100 border-warning">
                                <div class="card-body">
                                    <h6 class="card-subtitle mb-2 text-muted">Ngày Tạo</h6>
                                    <p class="card-text h5">${new Date(kho.ngay_tao).toLocaleDateString('vi-VN')}</p>
                                </div>
                            </div>
                        </div>
                    `);
                    
                    // Xóa dữ liệu cũ trong bảng
                    $('#medicines-table tbody').empty();
                    
                    // Thêm dữ liệu mới vào bảng
                    if (response.thuocs.data && response.thuocs.data.length > 0) {
                        response.thuocs.data.forEach(function(thuoc) {
                            const lotInfo = thuoc.lo_thuoc && thuoc.lo_thuoc.length > 0 
                                ? thuoc.lo_thuoc[0] : null;
                            
                            // Tính trạng thái hạn sử dụng
                            let statusHtml = '';
                            if (lotInfo) {
                                const expiryDate = new Date(lotInfo.han_su_dung);
                                const today = new Date();
                                const daysToExpiry = Math.ceil((expiryDate - today) / (1000 * 60 * 60 * 24));
                                
                                if (daysToExpiry <= 0) {
                                    statusHtml = `<span class="badge bg-danger">Hết hạn</span>`;
                                } else if (daysToExpiry <= 30) {
                                    statusHtml = `<span class="badge bg-warning">Sắp hết hạn (${daysToExpiry} ngày)</span>`;
                                } else {
                                    statusHtml = `<span class="badge bg-success">Còn hạn (${daysToExpiry} ngày)</span>`;
                                }
                            }

                            $('#medicines-table tbody').append(`
                                <tr>
                                    <td>
                                        <span class="fw-bold">${thuoc.ma_thuoc}</span>
                                    </td>
                                    <td>
                                        <div class="d-flex flex-column">
                                            <span class="fw-bold">${thuoc.ten_thuoc}</span>
                                            <small class="text-muted">Nhóm: ${thuoc.nhom_thuoc.ten_nhom}</small>
                                            <small class="text-muted">Đơn vị: ${thuoc.don_vi_goc}</small>
                                            ${lotInfo ? `<small class="text-muted">NSX: ${lotInfo.ngay_san_xuat} | HSD: ${lotInfo.han_su_dung}</small>` : ''}
                                        </div>
                                    </td>
                                    <td class="text-center">
                                        <span class="badge bg-success fs-6">
                                            ${Number(thuoc.lo_thuoc_sum_ton_kho_hien_tai).toLocaleString('vi-VN')}
                                            ${thuoc.don_vi_goc}
                                        </span>
                                    </td>
                                    <td>
                                        ${statusHtml}
                                    </td>
                                    <td class="text-end">
                                        <button class="btn btn-sm btn-primary" onclick="showMedicineDetails(${thuoc.thuoc_id})" title="Xem chi tiết">
                                            <i class="bi bi-eye"></i>
                                        </button>
                                    </td>
                                </tr>
                            `);
                        });
                    } else {
                        $('#medicines-table tbody').append(`
                            <tr>
                                <td colspan="5" class="text-center py-4">
                                    <div class="text-muted">
                                        <i class="bi bi-inbox h3 d-block"></i>
                                        Không có thuốc nào trong kho
                                    </div>
                                </td>
                            </tr>
                        `);
                    }
                    
                    // Cập nhật phân trang
                    if (response.links) {
                        $('#medicines-pagination').html(response.links);
                    }
                    
                    // Hiển thị modal
                    $('#viewKhoModal').modal('show');
                },
                error: function(xhr, status, error) {
                    showErrorDialog('Không thể tải thông tin kho', 'Đã có lỗi xảy ra khi tải thông tin kho. Vui lòng thử lại sau.');
                }
            });
        }

        // Xóa kho
        let deleteId = null;
        
        // Bind các nút hành động
        $('.view-btn').click(function() {
            const id = $(this).data('id');
            viewKhoDetails(id);
        });
        
        $('.edit-btn').click(function() {
            const id = $(this).data('id');
            getKho(id);
        });
        
        $('.delete-btn').click(function() {
            deleteId = $(this).data('id');
            const tenKho = $(this).data('ten');
            
            $('#delete_ten_kho').text(tenKho);
            $('#deleteKhoModal').modal('show');
        });
        
        // Xác nhận xóa kho
        $('#confirmDelete').click(function() {
            if (!deleteId) return;
            
            $.ajax({
                url: `/kho/${deleteId}`,
                type: "DELETE",
                dataType: "json",
                headers: {
                    'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                },
                success: function(response) {
                    $('#deleteKhoModal').modal('hide');
                    showToast(response.message);
                    // Reload trang để cập nhật danh sách kho
                    location.reload();
                },
                error: function(xhr) {
                    $('#deleteKhoModal').modal('hide');
                    showToast(xhr.responseJSON.message, 'danger');
                }
            });
        });

        // Clear form khi đóng modal
        $('#addKhoModal').on('hidden.bs.modal', function() {
            $('#addKhoForm')[0].reset();
            $('#addKhoForm .is-invalid').removeClass('is-invalid');
        });
        
        $('#editKhoModal').on('hidden.bs.modal', function() {
            $('#editKhoForm .is-invalid').removeClass('is-invalid');
        });

        // Hiển thị/ẩn chi tiết lô thuốc
        $(document).on('click', '.toggle-lots-btn', function() {
            const thuocId = $(this).data('thuoc-id');
            const collapseId = `#lots-${thuocId}`;
            const lotDetailsId = `#lot-details-${thuocId}`;

            if ($(collapseId).hasClass('show')) {
                $(collapseId).collapse('hide');
            } else {
                $(collapseId).collapse('show');
                $(lotDetailsId).html(showLoading());

                $.ajax({
                    url: `/thuoc/${thuocId}/lots`,
                    type: 'GET',
                    dataType: 'json',
                    success: function(response) {
                        let rows = '';
                        if (response.lots && response.lots.length > 0) {
                            response.lots.forEach(function(lot) {
                                rows += `
                                    <tr>
                                        <td>${lot.so_lo}</td>
                                        <td class="text-center">${lot.so_luong_ton}</td>
                                        <td>${lot.ngay_san_xuat}</td>
                                        <td>${lot.han_su_dung}</td>
                                        <td>${lot.trang_thai}</td>
                                    </tr>
                                `;
                            });
                        } else {
                            rows = '<tr><td colspan="5" class="text-center">Không có lô nào.</td></tr>';
                        }
                        $(lotDetailsId).html(rows);
                    },
                    error: function() {
                        $(lotDetailsId).html('<tr><td colspan="5" class="text-center text-danger">Lỗi khi tải dữ liệu.</td></tr>');
                    }
                });
            }
        });

        // Expose functions globally for onclick handlers
        window.viewKho = viewKho;
        window.getKho = getKho;
        window.viewKhoDetails = viewKhoDetails;

    });

    // Hiển thị chi tiết thuốc và các lô
    function showMedicineDetails(thuocId) {
        $('#viewMedicineModal').modal('show');
        $('#lots-table tbody').html(showLoading());

        $.ajax({
            url: `/thuoc/${thuocId}/lots`,
            type: "GET",
            dataType: "json",
            success: function(response) {
                if (!response.data) {
                    showErrorDialog('Lỗi Dữ Liệu', 'Không thể tải thông tin lô thuốc.');
                    return;
                }

                const thuoc = response.data.thuoc;
                let tableHtml = '';
                if (response.data.length > 0) {
                    response.data.forEach(function(lot) {
                        const expiryDate = new Date(lot.han_su_dung);
                        const today = new Date();
                        const daysToExpiry = Math.ceil((expiryDate - today) / (1000 * 60 * 60 * 24));

                        let statusBadge = '';
                        if (daysToExpiry <= 0) {
                            statusBadge = '<span class="badge bg-danger">Hết hạn</span>';
                        } else if (daysToExpiry <= 30) {
                            statusBadge = `<span class="badge bg-warning">Sắp hết hạn (còn ${daysToExpiry} ngày)</span>`;
                        } else {
                            statusBadge = `<span class="badge bg-success">Còn hạn (${daysToExpiry} ngày)</span>`;
                        }

                        tableHtml += `
                            <tr>
                                <td>${lot.kho?.ten_kho || ''}</td>
                                <td>${lot.ma_lo || 'N/A'}</td>
                                <td class="text-center">
                                    <span class="badge bg-primary">
                                        ${Number(lot.ton_kho_hien_tai).toLocaleString('vi-VN')}
                                    </span>
                                </td>
                                <td>${lot.ngay_san_xuat}</td>
                                <td>${lot.han_su_dung}</td>
                                <td>${statusBadge}</td>
                            </tr>
                        `;
                    });
                } else {
                    tableHtml = `
                        <tr>
                            <td colspan="6" class="text-center py-4">
                                <div class="text-muted">
                                    <i class="bi bi-inbox h3 d-block"></i>
                                    Không có lô thuốc nào
                                </div>
                            </td>
                        </tr>
                    `;
                }

                $('#lots-table tbody').html(tableHtml);
            },
            error: function(xhr, status, error) {
                showErrorDialog('Lỗi', 'Không thể tải thông tin chi tiết thuốc. Vui lòng thử lại sau.');
                $('#lots-table tbody').html(`
                    <tr>
                        <td colspan="6" class="text-center text-danger">
                            Có lỗi xảy ra khi tải dữ liệu chi tiết thuốc
                        </td>
                    </tr>
                `);
            }
        });
    }

    // Hiển thị chi tiết các lô thuốc theo kho và thuốc
    function viewMedicineLots(khoId, thuocId, thuocName) {
        $('#viewMedicineLotModal').modal('show');
        $('#medicine-lots-table tbody').html(showLoading());
        $('#view_medicine_lot_name').text(thuocName);

        $.ajax({
            url: `/thuoc/${thuocId}/lots`,
            type: "GET",
            dataType: "json",
            success: function(response) {
                if (!response.data || response.data.length === 0) {
                    $('#medicine-lots-table tbody').html(`
                        <tr>
                            <td colspan="5" class="text-center py-4">
                                <div class="text-muted">
                                    <i class="bi bi-inbox h3 d-block"></i>
                                    Không có lô thuốc nào
                                </div>
                            </td>
                        </tr>
                    `);
                    return;
                }

                let tableHtml = '';
                response.data.forEach(function(lot) {
                    console.log('Lot:', lot); // Debug log
                    const expiryDate = new Date(lot.han_su_dung);
                    const today = new Date();
                    const daysToExpiry = Math.ceil((expiryDate - today) / (1000 * 60 * 60 * 24));

                    let statusBadge = '';
                    if (daysToExpiry <= 0) {
                        statusBadge = '<span class="badge bg-danger">Hết hạn</span>';
                    } else if (daysToExpiry <= 30) {
                        statusBadge = `<span class="badge bg-warning">Sắp hết hạn (còn ${daysToExpiry} ngày)</span>`;
                    } else {
                        statusBadge = `<span class="badge bg-success">Còn hạn (${daysToExpiry} ngày)</span>`;
                    }

                    tableHtml += `
                        <tr>
                            <td>${lot.ma_lo || 'N/A'}</td>
                            <td class="text-center">
                                <span class="badge bg-primary">
                                    ${Number(lot.ton_kho_hien_tai).toLocaleString('vi-VN')} ${lot.don_vi || ''}
                                </span>
                            </td>
                            <td>${lot.ngay_san_xuat}</td>
                            <td>${lot.han_su_dung}</td>
                            <td>${statusBadge}</td>
                        </tr>
                    `;
                });

                $('#medicine-lots-table tbody').html(tableHtml);
            },
            error: function(xhr, status, error) {
                showErrorDialog('Lỗi', 'Không thể tải thông tin lô thuốc. Vui lòng thử lại sau.');
                $('#medicine-lots-table tbody').html(`
                    <tr>
                        <td colspan="5" class="text-center text-danger">
                            Có lỗi xảy ra khi tải dữ liệu lô thuốc
                        </td>
                    </tr>
                `);
            }
        });
    }
</script>
@endsection
