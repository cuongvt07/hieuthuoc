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
<div class="row mb-4">
    <div class="col-12 text-end">
        <button type="button" class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#addKhoModal">
            <i class="bi bi-plus-circle me-1"></i> Thêm Kho
        </button>
    </div>
</div>

<div class="row">
    @forelse ($khos as $kho)
    <div class="col-md-6 col-lg-4 mb-4">
        <div class="card warehouse-card h-100">
            <div class="card-body">
                <div class="d-flex justify-content-between">
                    <h5 class="card-title">{{ $kho->ten_kho }}</h5>
                    <div class="warehouse-icon">
                        <i class="bi bi-box-seam"></i>
                    </div>
                </div>
                
                <p class="card-text text-muted small mb-2">
                    <i class="bi bi-geo-alt me-1"></i> 
                    {{ $kho->dia_chi ?: 'Chưa cập nhật địa chỉ' }}
                </p>
                
                @if($kho->ghi_chu)
                <p class="card-text small">
                    <strong>Ghi chú:</strong> {{ $kho->ghi_chu }}
                </p>
                @endif
                
                <div class="mt-3">
                    <button type="button" class="btn btn-sm btn-info view-btn" data-id="{{ $kho->kho_id }}">
                        <i class="bi bi-eye"></i> Xem Tồn Kho
                    </button>
                    <button type="button" class="btn btn-sm btn-warning edit-btn" data-id="{{ $kho->kho_id }}">
                        <i class="bi bi-pencil"></i> Sửa
                    </button>
                    <button type="button" class="btn btn-sm btn-danger delete-btn" 
                        data-id="{{ $kho->kho_id }}" 
                        data-ten="{{ $kho->ten_kho }}">
                        <i class="bi bi-trash"></i> Xóa
                    </button>
                </div>
            </div>
            <div class="warehouse-stats">
                <div>
                    <strong>Tổng số thuốc:</strong> {{ number_format($kho->total_items) }} đơn vị
                </div>
            </div>
        </div>
    </div>
    @empty
    <div class="col-12">
        <div class="alert alert-info text-center">
            Chưa có kho nào được tạo. Hãy thêm kho mới bằng cách nhấn nút "Thêm Kho".
        </div>
    </div>
    @endforelse
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
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="viewKhoModalLabel">Tồn Kho: <span id="view_ten_kho"></span></h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <div id="inventory-container" class="position-relative">
                    <!-- Danh sách thuốc tồn kho sẽ được hiển thị ở đây -->
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
@endsection

@section('scripts')
<script>
    $(document).ready(function() {
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

        // Xem thông tin tồn kho
        function viewKho(id) {
            const inventoryContainer = $('#inventory-container');
            inventoryContainer.html(showLoading());
            
            $.ajax({
                url: `/kho/${id}`,
                type: "GET",
                dataType: "json",
                success: function(response) {
                    const kho = response.kho;
                    const loThuoc = response.loThuoc;
                    
                    $('#view_ten_kho').text(kho.ten_kho);
                    
                    let inventoryHtml = '';
                    
                    if (loThuoc.length > 0) {
                        inventoryHtml += '<div class="list-group">';
                        
                        $.each(loThuoc, function(index, lo) {
                            const daysRemaining = getDaysRemaining(lo.han_su_dung);
                            let warningClass = '';
                            let warningText = '';
                            
                            if (daysRemaining <= 30 && daysRemaining > 0) {
                                warningClass = 'expiry-warning';
                                warningText = `<span class="warning-text"><i class="bi bi-exclamation-triangle-fill me-1"></i> Sắp hết hạn (còn ${daysRemaining} ngày)</span>`;
                            } else if (daysRemaining <= 0) {
                                warningClass = 'expiry-danger';
                                warningText = '<span class="danger-text"><i class="bi bi-exclamation-octagon-fill me-1"></i> Đã hết hạn</span>';
                            }
                            
                            inventoryHtml += `
                                <div class="inventory-item ${warningClass} p-3 mb-3">
                                    <div class="d-flex justify-content-between">
                                        <h6 class="mb-1">${lo.thuoc.ten_thuoc}</h6>
                                        <div>${warningText}</div>
                                    </div>
                                    <div class="row mt-2">
                                        <div class="col-md-6">
                                            <p class="mb-1"><strong>Số lô:</strong> ${lo.ma_lo || lo.so_lo_nha_san_xuat}</p>
                                            <p class="mb-1"><strong>Số lượng:</strong> ${lo.ton_kho_hien_tai} ${lo.thuoc ? lo.thuoc.don_vi_goc || '' : ''}</p>
                                        </div>
                                        <div class="col-md-6">
                                            <p class="mb-1"><strong>NSX:</strong> ${formatDate(lo.ngay_san_xuat)}</p>
                                            <p class="mb-1">
                                                <strong>HSD:</strong> 
                                                <span class="expiry-date">${formatDate(lo.han_su_dung)}</span>
                                            </p>
                                        </div>
                                    </div>
                                </div>
                            `;
                        });
                        
                        inventoryHtml += '</div>';
                    } else {
                        inventoryHtml = '<div class="alert alert-info text-center">Kho này hiện không có thuốc nào.</div>';
                    }
                    
                    inventoryContainer.html(inventoryHtml);
                    $('#viewKhoModal').modal('show');
                },
                error: function(xhr) {
                    inventoryContainer.html('<div class="alert alert-danger">Có lỗi xảy ra khi tải dữ liệu tồn kho</div>');
                    showToast('Có lỗi xảy ra khi tải dữ liệu tồn kho', 'danger');
                }
            });
        }

        // Xóa kho
        let deleteId = null;
        
        // Bind các nút hành động
        $('.view-btn').click(function() {
            const id = $(this).data('id');
            viewKho(id);
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
    });
</script>
@endsection
