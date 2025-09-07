@extends('layouts.app')

@section('title', 'Quản Lý Nhà Cung Cấp - Hệ Thống Quản Lý Hiệu Thuốc')

@section('page-title', 'Quản Lý Nhà Cung Cấp')

@section('styles')
<style>
    .invoice-item {
        border-left: 3px solid #4e73df;
        padding-left: 10px;
        margin-bottom: 15px;
    }
    
    .invoice-date {
        color: #4e73df;
        font-weight: bold;
    }
    
    .invoice-details {
        margin-top: 10px;
    }
    
    .invoice-products {
        margin-top: 5px;
        padding-left: 15px;
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
                <button type="button" class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#addNhaCungCapModal">
                    <i class="bi bi-plus-circle me-1"></i> Thêm Nhà Cung Cấp
                </button>
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
                                    <button type="button" class="btn btn-sm btn-warning edit-btn" data-id="{{ $item->ncc_id }}">
                                        <i class="bi bi-pencil"></i> Sửa
                                    </button>
                                    <button type="button" class="btn btn-sm btn-danger delete-btn" 
                                        data-id="{{ $item->ncc_id }}" 
                                        data-ten="{{ $item->ten_ncc }}">
                                        <i class="bi bi-trash"></i> Xóa
                                    </button>
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
                    {{ $nhaCungCap->links() }}
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
                        <input type="text" class="form-control" id="ten_ncc" name="ten_ncc" required>
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
                        <input type="text" class="form-control" id="edit_ten_ncc" name="ten_ncc" required>
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
            <div class="modal-header">
                <h5 class="modal-title" id="viewNhaCungCapModalLabel">Chi Tiết Nhà Cung Cấp</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <div class="card mb-4">
                    <div class="card-header py-2">
                        <h6 class="m-0 font-weight-bold">Thông Tin Nhà Cung Cấp</h6>
                    </div>
                    <div class="card-body">
                        <table class="table table-borderless supplier-info-table">
                            <tbody>
                                <tr>
                                    <td>Tên NCC:</td>
                                    <td id="view_ten_ncc"></td>
                                </tr>
                                <tr>
                                    <td>Số Điện Thoại:</td>
                                    <td id="view_sdt"></td>
                                </tr>
                                <tr>
                                    <td>Email:</td>
                                    <td id="view_email"></td>
                                </tr>
                                <tr>
                                    <td>Mã Số Thuế:</td>
                                    <td id="view_ma_so_thue"></td>
                                </tr>
                                <tr>
                                    <td>Địa Chỉ:</td>
                                    <td id="view_dia_chi"></td>
                                </tr>
                                <tr>
                                    <td>Mô Tả:</td>
                                    <td id="view_mo_ta"></td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </div>
                
                <div class="card">
                    <div class="card-header py-2 d-flex justify-content-between align-items-center">
                        <h6 class="m-0 font-weight-bold">Lịch Sử Phiếu Nhập</h6>
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
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Đóng</button>
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
        
        // Format giá tiền
        function formatMoney(amount) {
            return parseInt(amount).toLocaleString('vi-VN') + ' đ';
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
                            html += `
                                <tr>
                                    <td>${item.ten_ncc}</td>
                                    <td>${item.sdt || ''}</td>
                                    <td>${item.ma_so_thue || ''}</td>
                                    <td>${item.email || ''}</td>
                                    <td class="text-center">${item.phieu_nhap_count || 0}</td>
                                    <td class="text-center">
                                        <button type="button" class="btn btn-sm btn-info view-btn" data-id="${item.ncc_id}">
                                            <i class="bi bi-eye"></i> Xem
                                        </button>
                                        <button type="button" class="btn btn-sm btn-warning edit-btn" data-id="${item.ncc_id}">
                                            <i class="bi bi-pencil"></i> Sửa
                                        </button>
                                        <button type="button" class="btn btn-sm btn-danger delete-btn" 
                                            data-id="${item.ncc_id}" 
                                            data-ten="${item.ten_ncc}">
                                            <i class="bi bi-trash"></i> Xóa
                                        </button>
                                    </td>
                                </tr>
                            `;
                        });
                    } else {
                        html = '<tr><td colspan="6" class="text-center">Không có dữ liệu</td></tr>';
                    }
                    
                    tableBody.html(html);
                    $('#pagination').html(response.links);

                    // Rebind pagination links
                    $('#pagination').on('click', '.pagination a', function(e) {
                        e.preventDefault();
                        const page = $(this).attr('href').split('page=')[1];
                        loadNhaCungCap(page, search);
                    });

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
            
            // Lấy dữ liệu từ form
            const formData = new FormData(this);
            
            // Chuyển FormData thành đối tượng
            const data = {};
            formData.forEach((value, key) => {
                data[key] = value;
            });
            
            $.ajax({
                url: "{{ route('nha-cung-cap.store') }}",
                type: "POST",
                data: data,
                dataType: "json",
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
                    
                    // Xóa tất cả invalid feedback trước
                    $('#addNhaCungCapForm .is-invalid').removeClass('is-invalid');
                    
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

        // Lấy thông tin nhà cung cấp để sửa
        function getNhaCungCap(id) {
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
                    
                    // Hiển thị thông tin nhà cung cấp
                    $('#view_ten_ncc').text(nhaCungCap.ten_ncc);
                    $('#view_sdt').text(nhaCungCap.sdt || 'Không có');
                    $('#view_email').text(nhaCungCap.email || 'Không có');
                    $('#view_ma_so_thue').text(nhaCungCap.ma_so_thue || 'Không có');
                    $('#view_dia_chi').text(nhaCungCap.dia_chi || 'Không có');
                    $('#view_mo_ta').text(nhaCungCap.mo_ta || 'Không có');
                    
                    // Hiển thị số lượng phiếu nhập
                    $('#phieu_nhap_count').text(phieuNhap.length + ' phiếu');
                    
                    // Render danh sách phiếu nhập
                    let phieuNhapHtml = '';
                    if (phieuNhap.length > 0) {
                        $.each(phieuNhap, function(index, item) {
                            const ngayNhap = formatDate(item.ngay_nhap);
                            const trangThai = item.trang_thai === 1 ? 
                                '<span class="badge bg-success">Hoàn thành</span>' : 
                                '<span class="badge bg-warning text-dark">Đang xử lý</span>';
                            
                            phieuNhapHtml += `
                                <div class="invoice-item">
                                    <div class="d-flex justify-content-between align-items-center">
                                        <div class="invoice-date">${ngayNhap}</div>
                                        <div class="invoice-total fw-bold">${formatMoney(item.tong_cong)}</div>
                                    </div>
                                    <div class="invoice-code">Mã phiếu: <strong>${item.ma_phieu}</strong></div>
                                    <div class="d-flex justify-content-between">
                                        <div>Người nhập: <strong>${item.nguoi_dung?.ho_ten || 'N/A'}</strong></div>
                                        <div>${trangThai}</div>
                                    </div>
                                    <div class="invoice-details">
                                        <div class="fw-bold">Danh sách thuốc:</div>
                                        <ul class="invoice-products">
                            `;
                            
                            // Render chi tiết phiếu
                            if (item.chi_tiet_lo_nhap && item.chi_tiet_lo_nhap.length > 0) {
                                $.each(item.chi_tiet_lo_nhap, function(i, chiTiet) {
                                    phieuNhapHtml += `
                                        <li>
                                            ${chiTiet.lo_thuoc?.thuoc?.ten_thuoc || 'Không có thông tin'} - 
                                            ${chiTiet.so_luong} ${chiTiet.don_vi_tinh || 'Đơn vị'} x 
                                            ${formatMoney(chiTiet.don_gia)} = 
                                            ${formatMoney(chiTiet.thanh_tien)}
                                        </li>
                                    `;
                                });
                            } else {
                                phieuNhapHtml += '<li>Không có dữ liệu chi tiết</li>';
                            }
                            
                            phieuNhapHtml += `
                                        </ul>
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
            
            const id = $('#edit_ncc_id').val();
            
            // Lấy dữ liệu từ form
            const formData = new FormData(this);
            
            // Chuyển FormData thành đối tượng
            const data = {};
            formData.forEach((value, key) => {
                data[key] = value;
            });
            
            $.ajax({
                url: `/nha-cung-cap/${id}`,
                type: "PUT",
                data: data,
                dataType: "json",
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
                    
                    // Xóa tất cả invalid feedback trước
                    $('#editNhaCungCapForm .is-invalid').removeClass('is-invalid');
                    
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

        // Xóa nhà cung cấp
        let deleteId = null;
        
        function bindButtons() {
            // Nút xem chi tiết nhà cung cấp
            $('.view-btn').click(function() {
                const id = $(this).data('id');
                viewNhaCungCap(id);
            });
            
            // Nút sửa nhà cung cấp
            $('.edit-btn').click(function() {
                const id = $(this).data('id');
                getNhaCungCap(id);
            });
            
            // Nút xóa nhà cung cấp
            $('.delete-btn').click(function() {
                deleteId = $(this).data('id');
                const tenNcc = $(this).data('ten');
                
                $('#delete_ten_ncc').text(tenNcc);
                $('#deleteNhaCungCapModal').modal('show');
            });
        }
        
        // Xác nhận xóa nhà cung cấp
        $('#confirmDelete').click(function() {
            if (!deleteId) return;
            
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

        // Tìm nhà cung cấp theo số điện thoại hoặc mã số thuế
        function findSupplierByPhoneOrTax(params) {
            return $.ajax({
                url: "{{ route('nha-cung-cap.findByPhoneOrTax') }}",
                type: "GET",
                data: params,
                dataType: "json"
            });
        }

        // Khởi tạo
        bindButtons();
        
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
        window.nhaCungCapModule = {
            findByPhoneOrTax: findSupplierByPhoneOrTax
        };
    });
</script>
@endsection
