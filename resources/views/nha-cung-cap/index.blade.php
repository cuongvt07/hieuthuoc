@extends('layouts.app')

@section('title', 'Quản Lý Nhà Cung Cấp - Hệ Thống Quản Lý Hiệu Thuốc')

@section('page-title', 'Quản Lý Nhà Cung Cấp')

@section('styles')
<style>
    .invoice-item {
        border-left: 4px solid #4e73df;
        padding: 12px;
        margin-bottom: 12px;
        background-color: #f8f9fc;
        border-radius: 0 5px 5px 0;
        box-shadow: 0 2px 4px rgba(0,0,0,0.08);
        transition: all 0.2s ease-in-out;
    }
    
    .invoice-item:hover {
        transform: translateX(3px);
        box-shadow: 0 3px 6px rgba(0,0,0,0.12);
    }
    
    .invoice-code {
        color: #4e73df;
        font-size: 1.1rem;
        font-weight: 600;
    }
    
    .invoice-total {
        color: #1cc88a;
        font-weight: bold;
    }
    
    .invoice-status-pending {
        color: #f6c23e;
        font-weight: 500;
    }
    
    .invoice-status-completed {
        color: #1cc88a;
        font-weight: 500;
    }
    
    .invoice-status-cancelled {
        color: #e74a3b;
        font-weight: 500;
    }
    
    .modal-body {
        max-height: 70vh;
        overflow-y: auto;
    }
    
    .supplier-info-table td {
        padding: 4px 0;
    }
    
    .supplier-info-table td:first-child {
        width: 120px;
        font-weight: bold;
    }
</style>
@endsection

@section('content')
<div class="row">
    <div class="col-md-12">
        <div class="card shadow mb-4">
                <div class="card-header py-3 d-flex justify-content-between align-items-center">
                <h6 class="m-0 font-weight-bold">Danh Sách Nhà Cung Cấp</h6>
                @if(!(Auth::user() && Auth::user()->vai_tro === 'duoc_si'))
                <button type="button" class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#addNhaCungCapModal">
                    <i class="bi bi-plus-circle me-1"></i> Thêm Nhà Cung Cấp
                </button>
                @endif
            </div>
            <div class="card-body">
                <div class="row mb-3">
                    <div class="col-md-6">
                        <div class="input-group">
                            <input type="text" id="search-input" class="form-control" placeholder="Tìm theo tên, SDT, mã số thuế...">
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
                    <table class="table table-bordered table-striped" id="nha-cung-cap-table" width="100%" cellspacing="0">
                        <thead>
                            <tr>
                                <th>Tên NCC</th>
                                <th>Số Điện Thoại</th>
                                <th>Mã Số Thuế</th>
                                <th>Email</th>
                                <th>Số Phiếu Nhập</th>
                                <th>Thao Tác</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse ($nhaCungCap as $item)
                            <tr>
                                <td>{{ $item->ten_ncc }}</td>
                                <td>{{ $item->sdt }}</td>
                                <td>{{ $item->ma_so_thue }}</td>
                                <td>{{ $item->email }}</td>
                                <td class="text-center">{{ $item->phieuNhap->count() }}</td>
                                <td class="text-center">
                                    <button type="button" class="btn btn-sm btn-info view-btn" data-id="{{ $item->ncc_id }}">
                                        <i class="bi bi-eye"></i> Xem
                                    </button>
                                    @if(!(Auth::user() && Auth::user()->vai_tro === 'duoc_si'))
                                    <button type="button" class="btn btn-sm btn-warning edit-btn" data-id="{{ $item->ncc_id }}">
                                        <i class="bi bi-pencil"></i> Sửa
                                    </button>
                                    <button type="button" class="btn btn-sm btn-warning suspend-btn" 
                                        data-id="{{ $item->ncc_id }}" 
                                        data-ten="{{ $item->ten_ncc }}" data-status="{{ $item->trang_thai }}">
                                        <i class="bi bi-ban"></i> {{ $item->trang_thai == 1 ? 'Đình chỉ' : 'Bỏ đình chỉ' }}
                                    </button>
                                    @endif
                                </td>
                            </tr>
                            @empty
                            <tr>
                                <td colspan="6" class="text-center">Không có dữ liệu</td>
                            </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
                
                <div class="d-flex justify-content-center mt-3" id="pagination">
                    {{ $nhaCungCap->onEachSide(1)->links('vendor.pagination.custom') }}
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Modal thêm nhà cung cấp -->
<div class="modal fade" id="addNhaCungCapModal" tabindex="-1" aria-labelledby="addNhaCungCapModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <form id="addNhaCungCapForm">
                <div class="modal-header">
                    <h5 class="modal-title" id="addNhaCungCapModalLabel">Thêm Nhà Cung Cấp</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <div class="mb-3">
                        <label for="ten_ncc" class="form-label">Tên Nhà Cung Cấp <span class="text-danger">*</span></label>
                        <input type="text" class="form-control" id="ten_ncc" name="ten_ncc" requiredmsg="Trường này yêu cầu bắt buộc">
                        <div class="invalid-feedback" id="ten_ncc_error"></div>
                    </div>
                    <div class="mb-3">
                        <label for="sdt" class="form-label">Số Điện Thoại</label>
                        <input type="text" class="form-control" id="sdt" name="sdt">
                        <div class="invalid-feedback" id="sdt_error"></div>
                    </div>
                    <div class="mb-3">
                        <label for="ma_so_thue" class="form-label">Mã Số Thuế</label>
                        <input type="text" class="form-control" id="ma_so_thue" name="ma_so_thue">
                        <div class="invalid-feedback" id="ma_so_thue_error"></div>
                    </div>
                    <div class="mb-3">
                        <label for="email" class="form-label">Email</label>
                        <input type="email" class="form-control" id="email" name="email">
                        <div class="invalid-feedback" id="email_error"></div>
                    </div>
                    <div class="mb-3">
                        <label for="dia_chi" class="form-label">Địa Chỉ</label>
                        <textarea class="form-control" id="dia_chi" name="dia_chi" rows="2"></textarea>
                        <div class="invalid-feedback" id="dia_chi_error"></div>
                    </div>
                    <div class="mb-3">
                        <label for="mo_ta" class="form-label">Mô Tả</label>
                        <textarea class="form-control" id="mo_ta" name="mo_ta" rows="2"></textarea>
                        <div class="invalid-feedback" id="mo_ta_error"></div>
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

<!-- Modal sửa nhà cung cấp -->
<div class="modal fade" id="editNhaCungCapModal" tabindex="-1" aria-labelledby="editNhaCungCapModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <form id="editNhaCungCapForm">
                <div class="modal-header">
                    <h5 class="modal-title" id="editNhaCungCapModalLabel">Chỉnh Sửa Nhà Cung Cấp</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <input type="hidden" id="edit_ncc_id" name="ncc_id">
                    <div class="mb-3">
                        <label for="edit_ten_ncc" class="form-label">Tên Nhà Cung Cấp <span class="text-danger">*</span></label>
                        <input type="text" class="form-control" id="edit_ten_ncc" name="ten_ncc" requiredmsg="Trường này yêu cầu bắt buộc">
                        <div class="invalid-feedback" id="edit_ten_ncc_error"></div>
                    </div>
                    <div class="mb-3">
                        <label for="edit_sdt" class="form-label">Số Điện Thoại</label>
                        <input type="text" class="form-control" id="edit_sdt" name="sdt">
                        <div class="invalid-feedback" id="edit_sdt_error"></div>
                    </div>
                    <div class="mb-3">
                        <label for="edit_ma_so_thue" class="form-label">Mã Số Thuế</label>
                        <input type="text" class="form-control" id="edit_ma_so_thue" name="ma_so_thue">
                        <div class="invalid-feedback" id="edit_ma_so_thue_error"></div>
                    </div>
                    <div class="mb-3">
                        <label for="edit_email" class="form-label">Email</label>
                        <input type="email" class="form-control" id="edit_email" name="email">
                        <div class="invalid-feedback" id="edit_email_error"></div>
                    </div>
                    <div class="mb-3">
                        <label for="edit_dia_chi" class="form-label">Địa Chỉ</label>
                        <textarea class="form-control" id="edit_dia_chi" name="dia_chi" rows="2"></textarea>
                        <div class="invalid-feedback" id="edit_dia_chi_error"></div>
                    </div>
                    <div class="mb-3">
                        <label for="edit_mo_ta" class="form-label">Mô Tả</label>
                        <textarea class="form-control" id="edit_mo_ta" name="mo_ta" rows="2"></textarea>
                        <div class="invalid-feedback" id="edit_mo_ta_error"></div>
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

<!-- Modal xem chi tiết nhà cung cấp -->
<div class="modal fade" id="viewNhaCungCapModal" tabindex="-1" aria-labelledby="viewNhaCungCapModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header bg-info text-white">
                <h5 class="modal-title" id="viewNhaCungCapModalLabel">
                    <i class="bi bi-building me-1"></i> Chi Tiết Nhà Cung Cấp: <span id="view_ncc_title"></span>
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <div class="row">
<div class="col-12">
    <div class="card mb-4 shadow-sm">
        <div class="card-header py-2 bg-light">
            <h6 class="m-0 font-weight-bold text-primary">
                <i class="bi bi-info-circle me-1"></i> Thông Tin Nhà Cung Cấp
            </h6>
        </div>
        <div class="card-body">
            <div class="row gy-2 gx-3 align-items-center supplier-info-table">
                <!-- Dòng 1 -->
                <div class="col-md-4 col-sm-6">
                    <div class="d-flex align-items-center">
                        <span class="text-muted small me-1"><i class="bi bi-building me-1"></i> Tên NCC:</span>
                        <strong id="view_ten_ncc"></strong>
                    </div>
                </div>
                <div class="col-md-4 col-sm-6">
                    <div class="d-flex align-items-center">
                        <span class="text-muted small me-1"><i class="bi bi-telephone me-1"></i> Số ĐT:</span>
                        <span id="view_sdt"></span>
                    </div>
                </div>
                <div class="col-md-4 col-sm-6">
                    <div class="d-flex align-items-center">
                        <span class="text-muted small me-1"><i class="bi bi-credit-card me-1"></i> MST:</span>
                        <span id="view_ma_so_thue"></span>
                    </div>
                </div>

                <!-- Dòng 2 -->
                <div class="col-md-6 col-sm-6">
                    <div class="d-flex align-items-center">
                        <span class="text-muted small me-1"><i class="bi bi-envelope me-1"></i> Email:</span>
                        <span id="view_email"></span>
                    </div>
                </div>
                <div class="col-md-6 col-sm-6">
                    <div class="d-flex align-items-center">
                        <span class="text-muted small me-1"><i class="bi bi-geo-alt me-1"></i> Địa Chỉ:</span>
                        <span id="view_dia_chi"></span>
                    </div>
                </div>

                <!-- Dòng cuối -->
                <div class="col-12">
                    <div class="d-flex align-items-start">
                        <span class="text-muted small me-1"><i class="bi bi-sticky me-1"></i> Mô Tả:</span>
                        <div id="view_mo_ta" class="flex-grow-1"></div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

                </div>
                
                <div class="card shadow-sm">
                    <div class="card-header py-2 bg-light d-flex justify-content-between align-items-center">
                        <h6 class="m-0 font-weight-bold text-primary">
                            <i class="bi bi-list-check me-1"></i> Danh Sách Phiếu Nhập
                        </h6>
                        <span class="badge bg-primary" id="phieu_nhap_count">0 phiếu</span>
                    </div>
                    <div class="card-body">
                        <div id="phieu_nhap_list" class="timeline">
                            <!-- Danh sách phiếu nhập sẽ được render bởi JavaScript -->
                        </div>
                    </div>
                </div>
            </div>
            <div class="modal-footer">
                @if(!(Auth::user() && Auth::user()->vai_tro === 'duoc_si'))
                <a href="{{ route('phieu-nhap.create') }}" class="btn btn-primary" id="create-phieu-nhap-btn">
                    <i class="bi bi-plus-circle me-1"></i> Tạo Phiếu Nhập Mới
                </a>
                @endif
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">
                    <i class="bi bi-x-circle me-1"></i> Đóng
                </button>
            </div>
        </div>
    </div>
</div>

<!-- Modal xác nhận xóa nhà cung cấp -->
<div class="modal fade" id="deleteNhaCungCapModal" tabindex="-1" aria-labelledby="deleteNhaCungCapModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="deleteNhaCungCapModalLabel">Xác nhận xóa</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <p>Bạn có chắc chắn muốn xóa nhà cung cấp <span id="delete_ten_ncc" class="fw-bold"></span>?</p>
                <p class="text-danger mb-0">Lưu ý: Nếu nhà cung cấp đã có phiếu nhập thì không thể xóa.</p>
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
        
        function formatDateTime(dateString) {
            if (!dateString) return '';
            const date = new Date(dateString);
            return date.toLocaleDateString('vi-VN') + ' ' + date.toLocaleTimeString('vi-VN', {
                hour: '2-digit',
                minute: '2-digit'
            });
        }
        
        // Format giá tiền
        function formatMoney(amount) {
            return parseInt(amount).toLocaleString('vi-VN') + ' đ';
        }
        
        // Lấy vai trò của người dùng từ session hoặc meta tag
        const userRole = '{{ Auth::user()->vai_tro }}'; // Lấy vai trò từ PHP

        // Hàm kiểm tra quyền chỉnh sửa
        function hasEditPermission() {
            return userRole === 'admin';
        }

        // Vô hiệu hóa các nút thao tác nếu không phải admin
        if (!hasEditPermission()) {
            // Disable only the button that opens the "Thêm Nhà Cung Cấp" modal (don't touch other primary buttons like search)
            $('button[data-bs-target="#addNhaCungCapModal"]').prop('disabled', true).addClass('disabled');

            // Make sure the search button stays enabled for all roles
            $('#searchBtn').prop('disabled', false).removeClass('disabled');

            // Vô hiệu hóa các nút chỉnh sửa, xóa, đình chỉ trong bảng
            $('.edit-btn, .delete-btn, .suspend-btn').prop('disabled', true).addClass('disabled');
        }

        // Tìm kiếm nhà cung cấp
        $('#searchBtn').click(function() {
            const searchValue = $('#search-input').val();
            loadNhaCungCap(1, searchValue);
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
            loadNhaCungCap();
        });
        
        // Hàm load danh sách nhà cung cấp
        function loadNhaCungCap(page = 1, search = '') {
            const tableBody = $('#nha-cung-cap-table tbody');
            showLoading(tableBody);
            
            $.ajax({
                url: "{{ route('nha-cung-cap.index') }}",
                type: "GET",
                data: {
                    page: page,
                    search: search
                },
                dataType: "json",
                success: function(response) {
                    let html = '';
                    
                    if (response.nhaCungCap.data.length > 0) {
                        $.each(response.nhaCungCap.data, function(index, item) {
                            // Build action buttons: View always visible; hide Edit/Suspend for duoc_si role
                            const viewBtn = `
                                        <button type="button" class="btn btn-sm btn-info view-btn" data-id="${item.ncc_id}">
                                            <i class="bi bi-eye"></i> Xem
                                        </button>`;

                            const editBtn = `
                                        <button type="button" class="btn btn-sm btn-warning edit-btn" data-id="${item.ncc_id}" ${!hasEditPermission() ? 'disabled' : ''}>
                                            <i class="bi bi-pencil"></i> Sửa
                                        </button>`;

                            const suspendBtn = `
                                        <button type="button" class="btn btn-sm btn-warning suspend-btn" 
                                            data-id="${item.ncc_id}" 
                                            data-ten="${item.ten_ncc}" data-status="${item.trang_thai}" ${!hasEditPermission() ? 'disabled' : ''}>
                                            <i class="bi bi-ban"></i> ${item.trang_thai == 1 ? 'Đình chỉ' : 'Bỏ đình chỉ'}
                                        </button>`;

                            const actionButtons = userRole !== 'duoc_si' ? (viewBtn + editBtn + suspendBtn) : viewBtn;

                            html += `
                                <tr>
                                    <td>${item.ten_ncc}</td>
                                    <td>${item.sdt || ''}</td>
                                    <td>${item.ma_so_thue || ''}</td>
                                    <td>${item.email || ''}</td>
                                    <td class="text-center">${item.phieu_nhap_count || 0}</td>
                                    <td class="text-center">
                                        ${actionButtons}
                                    </td>
                                </tr>
                            `;
                        });
                    } else {
                        html = '<tr><td colspan="6" class="text-center">Không có dữ liệu</td></tr>';
                    }
                    
                    tableBody.html(html);
                    $('#pagination').html(response.links);

                    // Rebind buttons
                    bindButtons();
                },
                error: function() {
                    tableBody.html('<tr><td colspan="6" class="text-center text-danger">Đã xảy ra lỗi khi tải dữ liệu</td></tr>');
                    showToast('Đã xảy ra lỗi khi tải dữ liệu nhà cung cấp', 'danger');
                }
            });
        }
        
        // Thêm nhà cung cấp
        $('#addNhaCungCapForm').submit(function(e) {
            e.preventDefault();
            if (!hasEditPermission()) {
                showToast('Bạn không có quyền thêm nhà cung cấp', 'warning');
                return;
            }
            
            const formData = new FormData(this);
            $.ajax({
                url: "{{ route('nha-cung-cap.store') }}",
                type: "POST",
                data: formData,
                dataType: "json",
                processData: false,
                contentType: false,
                headers: {
                    'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                },
                success: function(response) {
                    $('#addNhaCungCapModal').modal('hide');
                    $('#addNhaCungCapForm')[0].reset();
                    showToast(response.message);
                    loadNhaCungCap();
                },
                error: function(xhr) {
                    const errors = xhr.responseJSON.errors;
                    $('#addNhaCungCapForm .is-invalid').removeClass('is-invalid');
                    if (errors) {
                        Object.keys(errors).forEach(function(key) {
                            $(`#${key}`).addClass('is-invalid');
                            $(`#${key}_error`).text(errors[key][0]);
                        });
                    }
                }
            });
        });

        // Lấy thông tin nhà cung cấp để sửa
        function getNhaCungCap(id) {
            if (!hasEditPermission()) {
                showToast('Bạn không có quyền sửa nhà cung cấp', 'warning');
                return;
            }
            $.ajax({
                url: `/nha-cung-cap/${id}`,
                type: "GET",
                dataType: "json",
                success: function(response) {
                    const nhaCungCap = response.nhaCungCap;
                    $('#edit_ncc_id').val(nhaCungCap.ncc_id);
                    $('#edit_ten_ncc').val(nhaCungCap.ten_ncc);
                    $('#edit_sdt').val(nhaCungCap.sdt || '');
                    $('#edit_ma_so_thue').val(nhaCungCap.ma_so_thue || '');
                    $('#edit_email').val(nhaCungCap.email || '');
                    $('#edit_dia_chi').val(nhaCungCap.dia_chi || '');
                    $('#edit_mo_ta').val(nhaCungCap.mo_ta || '');
                    $('#editNhaCungCapModal').modal('show');
                },
                error: function(xhr) {
                    showToast('Có lỗi xảy ra khi lấy thông tin nhà cung cấp', 'danger');
                }
            });
        }

        // Xem chi tiết nhà cung cấp
        function viewNhaCungCap(id) {
            $.ajax({
                url: `/nha-cung-cap/${id}`,
                type: "GET",
                dataType: "json",
                success: function(response) {
                    const nhaCungCap = response.nhaCungCap;
                    const phieuNhap = response.phieuNhap;
                    
                    $('#view_ten_ncc').text(nhaCungCap.ten_ncc);
                    $('#view_ncc_title').text(nhaCungCap.ten_ncc);
                    $('#view_sdt').text(nhaCungCap.sdt || 'Không có');
                    $('#view_email').text(nhaCungCap.email || 'Không có');
                    $('#view_ma_so_thue').text(nhaCungCap.ma_so_thue || 'Không có');
                    $('#view_dia_chi').text(nhaCungCap.dia_chi || 'Không có');
                    $('#view_mo_ta').text(nhaCungCap.mo_ta || 'Không có');
                    
                    const createdAt = nhaCungCap.created_at ? formatDateTime(nhaCungCap.created_at) : 'Không có';
                    const updatedAt = nhaCungCap.updated_at ? formatDateTime(nhaCungCap.updated_at) : 'Không có';
                    $('#view_created_at').text(createdAt);
                    $('#view_updated_at').text(updatedAt);
                    
                    $('#phieu_nhap_count').text(phieuNhap.length + ' phiếu');
                    
                    $('#create-phieu-nhap-btn').click(function(e) {
                        sessionStorage.setItem('selected_supplier_id', nhaCungCap.ncc_id);
                        sessionStorage.setItem('selected_supplier_name', nhaCungCap.ten_ncc);
                        window.location.href = $(this).attr('href');
                    });
                    
                    let phieuNhapHtml = '';
                    if (phieuNhap.length > 0) {
                        $.each(phieuNhap, function(index, item) {
                            const ngayNhap = formatDate(item.ngay_nhap);
                            let trangThai = '';
                            if (item.trang_thai === 'hoan_tat') {
                                trangThai = '<span class="badge bg-success invoice-status-completed"><i class="bi bi-check-circle me-1"></i>Hoàn thành</span>';
                            } else if (item.trang_thai === 'cho_xu_ly') {
                                trangThai = '<span class="badge bg-warning text-dark invoice-status-pending"><i class="bi bi-clock me-1"></i>Đang xử lý</span>';
                            } else if (item.trang_thai === 'huy') {
                                trangThai = '<span class="badge bg-danger invoice-status-cancelled"><i class="bi bi-x-circle me-1"></i>Đã hủy</span>';
                            } else {
                                trangThai = '<span class="badge bg-secondary"><i class="bi bi-question-circle me-1"></i>Không xác định</span>';
                            }
                            
                            phieuNhapHtml += `
                                <div class="invoice-item">
                                    <div class="d-flex justify-content-between align-items-center">
                                        <div class="invoice-code">
                                            <i class="bi bi-receipt me-1"></i>
                                            <strong>${item.ma_phieu}</strong>
                                        </div>
                                        <div>${trangThai}</div>
                                    </div>
                                    <div class="d-flex justify-content-between align-items-center mt-2">
                                        <div>
                                            <i class="bi bi-person me-1"></i>
                                            <strong>${item.nguoi_dung?.ho_ten || 'N/A'}</strong>
                                        </div>
                                        <div class="invoice-total">
                                            <i class="bi bi-currency-dollar me-1"></i>
                                            ${formatMoney(item.tong_cong)}
                                        </div>
                                    </div>
                                </div>
                            `;
                        });
                    } else {
                        phieuNhapHtml = '<div class="alert alert-info">Nhà cung cấp chưa có phiếu nhập nào</div>';
                    }
                    
                    $('#phieu_nhap_list').html(phieuNhapHtml);
                    $('#viewNhaCungCapModal').modal('show');
                },
                error: function(xhr) {
                    showToast('Có lỗi xảy ra khi lấy thông tin chi tiết nhà cung cấp', 'danger');
                }
            });
        }

        // Cập nhật nhà cung cấp
        $('#editNhaCungCapForm').submit(function(e) {
            e.preventDefault();
            if (!hasEditPermission()) {
                showToast('Bạn không có quyền sửa nhà cung cấp', 'warning');
                return;
            }
            
            const id = $('#edit_ncc_id').val();
            const data = $(this).serializeArray();
            data.push({name: '_method', value: 'PUT'});
            $.ajax({
                url: `/nha-cung-cap/${id}`,
                type: 'POST',
                data: $.param(data),
                dataType: 'json',
                headers: {
                    'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                },
                success: function(response) {
                    $('#editNhaCungCapModal').modal('hide');
                    showToast(response.message);
                    loadNhaCungCap();
                },
                error: function(xhr) {
                    const errors = xhr.responseJSON.errors;
                    $('#editNhaCungCapForm .is-invalid').removeClass('is-invalid');
                    if (errors) {
                        Object.keys(errors).forEach(function(key) {
                            $(`#edit_${key}`).addClass('is-invalid');
                            $(`#edit_${key}_error`).text(errors[key][0]);
                        });
                    }
                }
            });
        });

        // Xóa nhà cung cấp
        let deleteId = null;
        
        function bindButtons() {
            // Nút xem chi tiết nhà cung cấp
            $(document).off('click', '.view-btn').on('click', '.view-btn', function() {
                const id = $(this).data('id');
                viewNhaCungCap(id);
            });

            // Nút sửa nhà cung cấp
            $(document).off('click', '.edit-btn').on('click', '.edit-btn', function() {
                if (!hasEditPermission()) {
                    showToast('Bạn không có quyền sửa nhà cung cấp', 'warning');
                    return;
                }
                const id = $(this).data('id');
                getNhaCungCap(id);
            });

            // Nút xóa nhà cung cấp
            $(document).off('click', '.delete-btn').on('click', '.delete-btn', function() {
                if (!hasEditPermission()) {
                    showToast('Bạn không có quyền xóa nhà cung cấp', 'warning');
                    return;
                }
                deleteId = $(this).data('id');
                const tenNcc = $(this).data('ten');
                $('#delete_ten_ncc').text(tenNcc);
                $('#deleteNhaCungCapModal').modal('show');
            });

            // Nút đình chỉ/bỏ đình chỉ nhà cung cấp (delegated)
            $(document).off('click', '.suspend-btn').on('click', '.suspend-btn', function() {
                if (!hasEditPermission()) {
                    showToast('Bạn không có quyền đình chỉ nhà cung cấp', 'warning');
                    return;
                }
                var id = $(this).data('id');
                var status = $(this).data('status');
                var btn = $(this);
                $.ajax({
                    url: '/nha-cung-cap/' + id + '/suspend',
                    type: 'POST',
                    data: {
                        _token: $('meta[name="csrf-token"]').attr('content')
                    },
                    success: function(res) {
                        if(res.success) {
                            btn.data('status', res.trang_thai);
                            btn.html('<i class="bi bi-ban"></i> ' + (res.trang_thai == 1 ? 'Bỏ đình chỉ' : 'Đình chỉ'));
                            showToast(res.message, 'info');
                            loadNhaCungCap();
                        }
                    },
                    error: function() {
                        showToast('Có lỗi xảy ra khi thực hiện thao tác', 'danger');
                    }
                });
            });
        }
        
        // Xác nhận xóa nhà cung cấp
        $('#confirmDelete').click(function() {
            if (!deleteId || !hasEditPermission()) {
                showToast('Bạn không có quyền xóa nhà cung cấp', 'warning');
                $('#deleteNhaCungCapModal').modal('hide');
                return;
            }
            
            $.ajax({
                url: `/nha-cung-cap/${deleteId}`,
                type: "DELETE",
                dataType: "json",
                headers: {
                    'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                },
                success: function(response) {
                    $('#deleteNhaCungCapModal').modal('hide');
                    showToast(response.message);
                    loadNhaCungCap();
                },
                error: function(xhr) {
                    $('#deleteNhaCungCapModal').modal('hide');
                    showToast(xhr.responseJSON.message, 'danger');
                }
            });
        });

        // Đình chỉ/bỏ đình chỉ nhà cung cấp
        $('.suspend-btn').click(function() {
            if (!hasEditPermission()) {
                showToast('Bạn không có quyền đình chỉ nhà cung cấp', 'warning');
                return;
            }
            var id = $(this).data('id');
            var status = $(this).data('status');
            var btn = $(this);
            $.ajax({
                url: '/nha-cung-cap/' + id + '/suspend',
                type: 'POST',
                data: {
                    _token: $('meta[name="csrf-token"]').attr('content')
                },
                success: function(res) {
                    if(res.success) {
                        btn.data('status', res.trang_thai);
                        btn.html('<i class="bi bi-ban"></i> ' + (res.trang_thai == 1 ? 'Bỏ đình chỉ' : 'Đình chỉ'));
                        showToast(res.message, 'info');
                    }
                },
                error: function() {
                    showToast('Có lỗi xảy ra khi thực hiện thao tác', 'danger');
                }
            });
        });
        
        // Khởi tạo
        bindButtons();
        loadNhaCungCap();
        
        // ===== XỬ LÝ PHÂN TRANG =====
        $(document).on('click', '.pagination-link', function(e) {
            e.preventDefault();
            
            const page = $(this).data('page');
            if (!page) return;
            
            const searchValue = $('#search-input').val();
            loadNhaCungCap(page, searchValue);
        });
        
        // Clear form khi đóng modal
        $('#addNhaCungCapModal').on('hidden.bs.modal', function() {
            $('#addNhaCungCapForm')[0].reset();
            $('#addNhaCungCapForm .is-invalid').removeClass('is-invalid');
        });
        
        $('#editNhaCungCapModal').on('hidden.bs.modal', function() {
            $('#editNhaCungCapForm .is-invalid').removeClass('is-invalid');
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
        
        // Expose function để có thể gọi từ bên ngoài
        window.nhaCungCapModule = {
            findByPhoneOrTax: function(params) {
                return $.ajax({
                    url: "{{ route('nha-cung-cap.findByPhoneOrTax') }}",
                    type: "GET",
                    data: params,
                    dataType: "json"
                });
            }
        };
    });
</script>
@endsection
