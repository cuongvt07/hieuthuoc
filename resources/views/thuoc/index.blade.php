@extends('layouts.app')

@section('title', 'Quản Lý Thuốc & Nhóm Thuốc - Hệ Thống Quản Lý Hiệu Thuốc')

@section('page-title', 'Quản Lý Thuốc & Nhóm Thuốc')

@section('content')
<div class="alert alert-info">
    <i class="bi bi-info-circle me-2"></i> 
    Giao diện kết hợp quản lý Thuốc và Nhóm thuốc giúp bạn dễ dàng thêm, sửa, xóa thuốc và nhóm thuốc trong một màn hình. Chọn một nhóm thuốc từ danh sách bên trái để lọc thuốc theo nhóm.
</div>
<div class="row">
    <!-- Phần quản lý nhóm thuốc -->
    <div class="col-md-4">
        <div class="card shadow mb-4">
            <div class="card-header py-3 d-flex justify-content-between align-items-center">
                <h6 class="m-0 font-weight-bold">Danh Sách Nhóm Thuốc</h6>
                <button type="button" class="btn btn-primary btn-sm" data-bs-toggle="modal" data-bs-target="#addNhomThuocModal">
                    <i class="bi bi-plus-circle me-1"></i> Thêm Nhóm
                </button>
            </div>
            <div class="card-body">
                <div class="mb-3">
                    <div class="input-group">
                        <input type="text" id="search-nhom" class="form-control form-control-sm" placeholder="Tìm kiếm nhóm thuốc...">
                        <button class="btn btn-outline-secondary btn-sm" type="button" id="searchNhomBtn">
                            <i class="bi bi-search"></i>
                        </button>
                        <button class="btn btn-outline-danger btn-sm" type="button" id="resetNhomBtn" title="Xóa bộ lọc">
                            <i class="bi bi-x-circle"></i>
                        </button>
                    </div>
                </div>
                
                <div class="list-group nhom-thuoc-list">
                    @foreach ($nhomThuoc as $nhom)
                    <a href="#" class="list-group-item list-group-item-action d-flex justify-content-between align-items-center nhom-thuoc-item" 
                       data-id="{{ $nhom->nhom_id }}">
                        <div>
                            <span class="fw-bold">{{ $nhom->ma_nhom }}</span> - {{ $nhom->ten_nhom }}
                        </div>
                        <div>
                            <button type="button" class="btn btn-sm btn-info edit-nhom-btn" data-id="{{ $nhom->nhom_id }}">
                                <i class="bi bi-pencil"></i>
                            </button>
                            <button type="button" class="btn btn-sm btn-danger delete-nhom-btn" 
                                data-id="{{ $nhom->nhom_id }}" data-name="{{ $nhom->ten_nhom }}">
                                <i class="bi bi-trash"></i>
                            </button>
                        </div>
                    </a>
                    @endforeach
                </div>
                
                <div class="d-flex justify-content-center mt-3" id="pagination-nhom">
                    {{ $nhomThuoc->links() }}
                </div>
            </div>
        </div>
    </div>

    <!-- Phần quản lý thuốc -->
    <div class="col-md-8">
        <div class="card shadow mb-4">
            <div class="card-header py-3 d-flex justify-content-between align-items-center">
                <div>
                    <h6 class="m-0 font-weight-bold">Danh Sách Thuốc <span id="selected-nhom-name"></span></h6>
                    <small class="text-muted" id="filter-status">Đang hiển thị tất cả thuốc</small>
                </div>
                <button type="button" class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#addThuocModal">
                    <i class="bi bi-plus-circle me-1"></i> Thêm Thuốc
                </button>
            </div>
            <div class="card-body">
                <div class="row mb-3">
                    <div class="col-md-8">
                        <div class="input-group">
                            <input type="text" id="search-thuoc" class="form-control" placeholder="Tìm kiếm thuốc...">
                            <button class="btn btn-outline-secondary" type="button" id="searchThuocBtn">
                                <i class="bi bi-search"></i>
                            </button>
                            <button class="btn btn-outline-danger" type="button" id="resetThuocBtn" title="Xóa bộ lọc">
                                <i class="bi bi-x-circle"></i>
                            </button>
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="input-group">
                            <select id="filter-nhom" class="form-select">
                                <option value="">-- Tất cả nhóm --</option>
                            @foreach ($nhomThuoc as $nhom)
                            <option value="{{ $nhom->nhom_id }}">{{ $nhom->ten_nhom }}</option>
                            @endforeach
                            </select>
                            <button class="btn btn-outline-danger" type="button" id="resetFilterBtn" title="Xóa bộ lọc nhóm">
                                <i class="bi bi-x-circle"></i>
                            </button>
                        </select>
                    </div>
                </div>
                
                <div class="table-responsive">
                    <table class="table table-bordered table-striped" id="thuoc-table" width="100%" cellspacing="0">
                        <thead>
                            <tr>
                                <th>Mã Thuốc</th>
                                <th>Tên Thuốc</th>
                                <th>Nhóm Thuốc</th>
                                <th>Đơn Vị Bán</th>
                                <th>Tỉ Lệ Quy Đổi</th>
                                <th>Thao Tác</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse ($thuoc as $item)
                            <tr>
                                <td>{{ $item->ma_thuoc }}</td>
                                <td>{{ $item->ten_thuoc }}</td>
                                <td>{{ $item->nhomThuoc->ten_nhom }}</td>
                                <td>{{ $item->don_vi_ban }}</td>
                                <td>{{ $item->ti_le_quy_doi }}</td>
                                <td>
                                    <button type="button" class="btn btn-sm btn-info edit-thuoc-btn" data-id="{{ $item->thuoc_id }}">
                                        <i class="bi bi-pencil"></i>
                                    </button>
                                    <button type="button" class="btn btn-sm btn-danger delete-thuoc-btn" 
                                        data-id="{{ $item->thuoc_id }}" data-name="{{ $item->ten_thuoc }}">
                                        <i class="bi bi-trash"></i>
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
                
                <div class="d-flex justify-content-center mt-3" id="pagination-thuoc">
                    {{ $thuoc->links() }}
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Modal thêm nhóm thuốc -->
<div class="modal fade" id="addNhomThuocModal" tabindex="-1" aria-labelledby="addNhomThuocModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <form id="addNhomForm">
                <div class="modal-header">
                    <h5 class="modal-title" id="addNhomThuocModalLabel">Thêm Nhóm Thuốc</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <div class="mb-3">
                        <label for="ma_nhom" class="form-label">Mã Nhóm <span class="text-danger">*</span></label>
                        <input type="text" class="form-control" id="ma_nhom" name="ma_nhom" required>
                        <div class="invalid-feedback" id="ma_nhom_error"></div>
                    </div>
                    <div class="mb-3">
                        <label for="ten_nhom" class="form-label">Tên Nhóm <span class="text-danger">*</span></label>
                        <input type="text" class="form-control" id="ten_nhom" name="ten_nhom" required>
                        <div class="invalid-feedback" id="ten_nhom_error"></div>
                    </div>
                    <div class="mb-3">
                        <label for="mo_ta" class="form-label">Mô Tả</label>
                        <textarea class="form-control" id="mo_ta" name="mo_ta" rows="3"></textarea>
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

<!-- Modal sửa nhóm thuốc -->
<div class="modal fade" id="editNhomThuocModal" tabindex="-1" aria-labelledby="editNhomThuocModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <form id="editNhomForm">
                <div class="modal-header">
                    <h5 class="modal-title" id="editNhomThuocModalLabel">Chỉnh Sửa Nhóm Thuốc</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <input type="hidden" id="edit_nhom_id" name="nhom_id">
                    <div class="mb-3">
                        <label for="edit_ma_nhom" class="form-label">Mã Nhóm <span class="text-danger">*</span></label>
                        <input type="text" class="form-control" id="edit_ma_nhom" name="ma_nhom" required>
                        <div class="invalid-feedback" id="edit_ma_nhom_error"></div>
                    </div>
                    <div class="mb-3">
                        <label for="edit_ten_nhom" class="form-label">Tên Nhóm <span class="text-danger">*</span></label>
                        <input type="text" class="form-control" id="edit_ten_nhom" name="ten_nhom" required>
                        <div class="invalid-feedback" id="edit_ten_nhom_error"></div>
                    </div>
                    <div class="mb-3">
                        <label for="edit_mo_ta" class="form-label">Mô Tả</label>
                        <textarea class="form-control" id="edit_mo_ta" name="mo_ta" rows="3"></textarea>
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

<!-- Modal xác nhận xóa nhóm thuốc -->
<div class="modal fade" id="deleteNhomThuocModal" tabindex="-1" aria-labelledby="deleteNhomThuocModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="deleteNhomThuocModalLabel">Xác nhận xóa</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <p>Bạn có chắc chắn muốn xóa nhóm thuốc <span id="delete_nhom_name" class="fw-bold"></span>?</p>
                <p class="text-danger mb-0">Lưu ý: Hành động này không thể hoàn tác và sẽ xóa tất cả thuốc trong nhóm.</p>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Hủy</button>
                <button type="button" class="btn btn-danger" id="confirmDeleteNhom">Xóa</button>
            </div>
        </div>
    </div>
</div>

<!-- Modal thêm thuốc -->
<div class="modal fade" id="addThuocModal" tabindex="-1" aria-labelledby="addThuocModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <form id="addThuocForm">
                <div class="modal-header">
                    <h5 class="modal-title" id="addThuocModalLabel">Thêm Thuốc</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label for="ma_thuoc" class="form-label">Mã Thuốc <span class="text-danger">*</span></label>
                            <input type="text" class="form-control" id="ma_thuoc" name="ma_thuoc" required>
                            <div class="invalid-feedback" id="ma_thuoc_error"></div>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label for="nhom_id" class="form-label">Nhóm Thuốc <span class="text-danger">*</span></label>
                            <select class="form-select" id="nhom_id" name="nhom_id" required>
                                <option value="">-- Chọn nhóm thuốc --</option>
                                @foreach ($nhomThuoc as $nhom)
                                <option value="{{ $nhom->nhom_id }}">{{ $nhom->ten_nhom }}</option>
                                @endforeach
                            </select>
                            <div class="invalid-feedback" id="nhom_id_error"></div>
                        </div>
                    </div>
                    <div class="mb-3">
                        <label for="ten_thuoc" class="form-label">Tên Thuốc <span class="text-danger">*</span></label>
                        <input type="text" class="form-control" id="ten_thuoc" name="ten_thuoc" required>
                        <div class="invalid-feedback" id="ten_thuoc_error"></div>
                    </div>
                    <div class="row">
                        <div class="col-md-4 mb-3">
                            <label for="don_vi_goc" class="form-label">Đơn Vị Gốc <span class="text-danger">*</span></label>
                            <input type="text" class="form-control" id="don_vi_goc" name="don_vi_goc" required>
                            <div class="invalid-feedback" id="don_vi_goc_error"></div>
                        </div>
                        <div class="col-md-4 mb-3">
                            <label for="don_vi_ban" class="form-label">Đơn Vị Bán <span class="text-danger">*</span></label>
                            <input type="text" class="form-control" id="don_vi_ban" name="don_vi_ban" required>
                            <div class="invalid-feedback" id="don_vi_ban_error"></div>
                        </div>
                        <div class="col-md-4 mb-3">
                            <label for="ti_le_quy_doi" class="form-label">Tỉ Lệ Quy Đổi <span class="text-danger">*</span></label>
                            <input type="number" step="0.01" class="form-control" id="ti_le_quy_doi" name="ti_le_quy_doi" required>
                            <div class="invalid-feedback" id="ti_le_quy_doi_error"></div>
                        </div>
                    </div>
                    <div class="mb-3">
                        <label for="mo_ta_thuoc" class="form-label">Mô Tả</label>
                        <textarea class="form-control" id="mo_ta_thuoc" name="mo_ta" rows="3"></textarea>
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

<!-- Modal sửa thuốc -->
<div class="modal fade" id="editThuocModal" tabindex="-1" aria-labelledby="editThuocModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <form id="editThuocForm">
                <div class="modal-header">
                    <h5 class="modal-title" id="editThuocModalLabel">Chỉnh Sửa Thuốc</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <input type="hidden" id="edit_thuoc_id" name="thuoc_id">
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label for="edit_ma_thuoc" class="form-label">Mã Thuốc <span class="text-danger">*</span></label>
                            <input type="text" class="form-control" id="edit_ma_thuoc" name="ma_thuoc" required>
                            <div class="invalid-feedback" id="edit_ma_thuoc_error"></div>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label for="edit_nhom_id" class="form-label">Nhóm Thuốc <span class="text-danger">*</span></label>
                            <select class="form-select" id="edit_nhom_id" name="nhom_id" required>
                                <option value="">-- Chọn nhóm thuốc --</option>
                                @foreach ($nhomThuoc as $nhom)
                                <option value="{{ $nhom->nhom_id }}">{{ $nhom->ten_nhom }}</option>
                                @endforeach
                            </select>
                            <div class="invalid-feedback" id="edit_nhom_id_error"></div>
                        </div>
                    </div>
                    <div class="mb-3">
                        <label for="edit_ten_thuoc" class="form-label">Tên Thuốc <span class="text-danger">*</span></label>
                        <input type="text" class="form-control" id="edit_ten_thuoc" name="ten_thuoc" required>
                        <div class="invalid-feedback" id="edit_ten_thuoc_error"></div>
                    </div>
                    <div class="row">
                        <div class="col-md-4 mb-3">
                            <label for="edit_don_vi_goc" class="form-label">Đơn Vị Gốc <span class="text-danger">*</span></label>
                            <input type="text" class="form-control" id="edit_don_vi_goc" name="don_vi_goc" required>
                            <div class="invalid-feedback" id="edit_don_vi_goc_error"></div>
                        </div>
                        <div class="col-md-4 mb-3">
                            <label for="edit_don_vi_ban" class="form-label">Đơn Vị Bán <span class="text-danger">*</span></label>
                            <input type="text" class="form-control" id="edit_don_vi_ban" name="don_vi_ban" required>
                            <div class="invalid-feedback" id="edit_don_vi_ban_error"></div>
                        </div>
                        <div class="col-md-4 mb-3">
                            <label for="edit_ti_le_quy_doi" class="form-label">Tỉ Lệ Quy Đổi <span class="text-danger">*</span></label>
                            <input type="number" step="0.01" class="form-control" id="edit_ti_le_quy_doi" name="ti_le_quy_doi" required>
                            <div class="invalid-feedback" id="edit_ti_le_quy_doi_error"></div>
                        </div>
                    </div>
                    <div class="mb-3">
                        <label for="edit_mo_ta_thuoc" class="form-label">Mô Tả</label>
                        <textarea class="form-control" id="edit_mo_ta_thuoc" name="mo_ta" rows="3"></textarea>
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

<!-- Modal xác nhận xóa thuốc -->
<div class="modal fade" id="deleteThuocModal" tabindex="-1" aria-labelledby="deleteThuocModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="deleteThuocModalLabel">Xác nhận xóa</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <p>Bạn có chắc chắn muốn xóa thuốc <span id="delete_thuoc_name" class="fw-bold"></span>?</p>
                <p class="text-danger mb-0">Lưu ý: Hành động này không thể hoàn tác.</p>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Hủy</button>
                <button type="button" class="btn btn-danger" id="confirmDeleteThuoc">Xóa</button>
            </div>
        </div>
    </div>
</div>
@endsection

@section('scripts')
<script>
    $(document).ready(function() {
        // Biến lưu ID nhóm thuốc đang được chọn
        let selectedNhomId = '';
        
        // ===== PHẦN XỬ LÝ NHÓM THUỐC =====
        
        // Search nhóm thuốc
        $('#searchNhomBtn').click(function() {
            loadNhomThuoc();
        });

        $('#search-nhom').keypress(function(e) {
            if (e.which == 13) {
                loadNhomThuoc();
                return false;
            }
        });
        
        // Reset tìm kiếm nhóm thuốc
        $('#resetNhomBtn').click(function() {
            // Xóa giá trị trong ô tìm kiếm
            $('#search-nhom').val('');
            
            // Thêm hiệu ứng thông báo reset tìm kiếm
            showToast('Đã hiển thị lại tất cả nhóm thuốc', 'info');
            
            // Force reload tất cả nhóm thuốc
            loadNhomThuoc(1);
        });
        
        // Thêm tìm kiếm tự động sau khi nhập
        let nhomThuocSearchTimeout;
        $('#search-nhom').keyup(function() {
            clearTimeout(nhomThuocSearchTimeout);
            
            // Nếu ô tìm kiếm trống, ngay lập tức tải lại toàn bộ danh sách
            if ($(this).val().trim() === '') {
                // Hiển thị lại toàn bộ danh sách với trang đầu tiên
                loadNhomThuoc(1);
            } else {
                // Nếu có nội dung, đợi người dùng dừng gõ rồi mới tìm kiếm
                nhomThuocSearchTimeout = setTimeout(function() {
                    loadNhomThuoc(1);  // Luôn tìm kiếm từ trang đầu tiên
                }, 500); // Đợi 500ms sau khi dừng gõ mới tìm kiếm
            }
        });

        // Hàm load danh sách nhóm thuốc
        function loadNhomThuoc(page = 1) {
            const search = $('#search-nhom').val();
            
            // Chuẩn bị dữ liệu gửi đi
            // LUÔN gửi tham số search_nhom để controller biết đang tìm kiếm nhóm thuốc
            const data = { 
                page: page,
                search_nhom: search ? search.trim() : '' // Gửi chuỗi rỗng nếu không có từ khóa
            };
            
            $.ajax({
                url: "{{ route('thuoc.index') }}", // Thay đổi từ nhom-thuoc.index sang thuoc.index
                type: "GET",
                data: data,
                dataType: "json",
                success: function(response) {
                    let html = '';
                    
                    if (response.nhomThuoc.data.length > 0) {
                        $.each(response.nhomThuoc.data, function(index, nhom) {
                            html += `
                                <a href="#" class="list-group-item list-group-item-action d-flex justify-content-between align-items-center nhom-thuoc-item ${selectedNhomId == nhom.nhom_id ? 'active' : ''}" 
                                   data-id="${nhom.nhom_id}">
                                    <div>
                                        <span class="fw-bold">${nhom.ma_nhom}</span> - ${nhom.ten_nhom}
                                    </div>
                                    <div>
                                        <button type="button" class="btn btn-sm btn-info edit-nhom-btn" data-id="${nhom.nhom_id}">
                                            <i class="bi bi-pencil"></i>
                                        </button>
                                        <button type="button" class="btn btn-sm btn-danger delete-nhom-btn" 
                                            data-id="${nhom.nhom_id}" data-name="${nhom.ten_nhom}">
                                            <i class="bi bi-trash"></i>
                                        </button>
                                    </div>
                                </a>
                            `;
                        });
                    } else {
                        html = '<div class="list-group-item">Không có dữ liệu</div>';
                    }
                    
                    $('.nhom-thuoc-list').html(html);
                    $('#pagination-nhom').html(response.links);

                    // Rebind pagination links
                    $('#pagination-nhom').on('click', '.pagination a', function(e) {
                        e.preventDefault();
                        const page = $(this).attr('href').split('page=')[1];
                        
                        // Luôn xử lý bằng AJAX bất kể đường dẫn hiện tại là gì
                        loadNhomThuoc(page);
                    });

                    // Rebind event handlers
                    bindNhomThuocEvents();
                },
                error: function(xhr) {
                    showToast('Có lỗi xảy ra khi tải danh sách nhóm thuốc', 'danger');
                }
            });
        }

        // Bind sự kiện cho các nút trong danh sách nhóm thuốc
        function bindNhomThuocEvents() {
            // Click vào item nhóm thuốc để filter thuốc
            $('.nhom-thuoc-item').click(function(e) {
                e.preventDefault();
                
                const clickedId = $(this).data('id');
                const isAlreadyActive = $(this).hasClass('active');
                
                // Bỏ active tất cả các item
                $('.nhom-thuoc-item').removeClass('active');
                
                // Nếu item này đã active trước đó, thì bỏ chọn hoàn toàn
                if (isAlreadyActive) {
                    // Reset giá trị
                    selectedNhomId = '';
                    $('#filter-nhom').val('');
                    $('#selected-nhom-name').text('');
                    $('#filter-status').text('Đang hiển thị tất cả thuốc');
                    
                    // Thêm hiệu ứng highlight tạm thời cho thông báo
                    $('#filter-status').removeClass('text-muted').addClass('text-primary');
                    setTimeout(function() {
                        $('#filter-status').removeClass('text-primary').addClass('text-muted');
                    }, 1500);
                } else {
                    // Active item được click
                    $(this).addClass('active');
                    
                    // Lưu ID nhóm thuốc được chọn
                    selectedNhomId = clickedId;
                    
                    // Cập nhật filter dropdown
                    $('#filter-nhom').val(selectedNhomId);
                    
                    // Hiển thị tên nhóm đang chọn
                    const nhomName = $(this).find('div:first').text();
                    $('#selected-nhom-name').text(' - ' + nhomName);
                    $('#filter-status').text('Đang lọc theo nhóm thuốc');
                }
                
                // Load danh sách thuốc theo nhóm (hoặc tất cả nếu đã bỏ chọn)
                loadThuoc();
            });

            // Nút sửa nhóm thuốc
            $('.edit-nhom-btn').click(function(e) {
                e.preventDefault();
                e.stopPropagation(); // Ngăn không cho sự kiện click lan tỏa lên parent (nhom-thuoc-item)
                
                const id = $(this).data('id');
                
                $.ajax({
                    url: "/nhom-thuoc/" + id,
                    type: "GET",
                    dataType: "json",
                    success: function(response) {
                        $('#edit_nhom_id').val(response.nhomThuoc.nhom_id);
                        $('#edit_ma_nhom').val(response.nhomThuoc.ma_nhom);
                        $('#edit_ten_nhom').val(response.nhomThuoc.ten_nhom);
                        $('#edit_mo_ta').val(response.nhomThuoc.mo_ta);
                        
                        $('#editNhomThuocModal').modal('show');
                    },
                    error: function(xhr) {
                        showToast('Có lỗi xảy ra khi lấy thông tin nhóm thuốc', 'danger');
                    }
                });
            });

            // Nút xóa nhóm thuốc
            $('.delete-nhom-btn').click(function(e) {
                e.preventDefault();
                e.stopPropagation(); // Ngăn không cho sự kiện click lan tỏa lên parent (nhom-thuoc-item)
                
                const id = $(this).data('id');
                const name = $(this).data('name');
                
                $('#delete_nhom_name').text(name);
                $('#deleteNhomThuocModal').modal('show');
                
                $('#confirmDeleteNhom').off('click').on('click', function() {
                    $.ajax({
                        url: "/nhom-thuoc/" + id,
                        type: "DELETE",
                        dataType: "json",
                        success: function(response) {
                            $('#deleteNhomThuocModal').modal('hide');
                            showToast(response.message);
                            
                            // Nếu xóa nhóm đang được chọn, reset lại filter
                            if (selectedNhomId == id) {
                                selectedNhomId = '';
                                $('#filter-nhom').val('');
                                $('#selected-nhom-name').text('');
                                loadThuoc();
                            }
                            
                            loadNhomThuoc();
                            
                            // Cập nhật lại dropdown nhóm thuốc trong form thêm/sửa thuốc
                            updateNhomThuocDropdowns();
                        },
                        error: function(xhr) {
                            $('#deleteNhomThuocModal').modal('hide');
                            showToast(xhr.responseJSON.message, 'danger');
                        }
                    });
                });
            });
        }

        // Thêm nhóm thuốc mới
        $('#addNhomForm').submit(function(e) {
            e.preventDefault();
            
            const formData = {
                ma_nhom: $('#ma_nhom').val(),
                ten_nhom: $('#ten_nhom').val(),
                mo_ta: $('#mo_ta').val(),
            };
            
            $.ajax({
                url: "{{ route('nhom-thuoc.store') }}",
                type: "POST",
                data: formData,
                dataType: "json",
                success: function(response) {
                    $('#addNhomThuocModal').modal('hide');
                    $('#addNhomForm')[0].reset();
                    showToast(response.message);
                    loadNhomThuoc();
                    
                    // Cập nhật lại dropdown nhóm thuốc trong form thêm/sửa thuốc
                    updateNhomThuocDropdowns();
                },
                error: function(xhr) {
                    const errors = xhr.responseJSON.errors;
                    
                    if (errors.ma_nhom) {
                        $('#ma_nhom').addClass('is-invalid');
                        $('#ma_nhom_error').text(errors.ma_nhom[0]);
                    } else {
                        $('#ma_nhom').removeClass('is-invalid');
                    }
                    
                    if (errors.ten_nhom) {
                        $('#ten_nhom').addClass('is-invalid');
                        $('#ten_nhom_error').text(errors.ten_nhom[0]);
                    } else {
                        $('#ten_nhom').removeClass('is-invalid');
                    }
                }
            });
        });

        // Cập nhật nhóm thuốc
        $('#editNhomForm').submit(function(e) {
            e.preventDefault();
            
            const id = $('#edit_nhom_id').val();
            const formData = {
                ma_nhom: $('#edit_ma_nhom').val(),
                ten_nhom: $('#edit_ten_nhom').val(),
                mo_ta: $('#edit_mo_ta').val(),
            };
            
            $.ajax({
                url: "/nhom-thuoc/" + id,
                type: "PUT",
                data: formData,
                dataType: "json",
                success: function(response) {
                    $('#editNhomThuocModal').modal('hide');
                    showToast(response.message);
                    loadNhomThuoc();
                    
                    // Cập nhật lại dropdown nhóm thuốc trong form thêm/sửa thuốc
                    updateNhomThuocDropdowns();
                    
                    // Cập nhật lại danh sách thuốc nếu cần
                    if (selectedNhomId == id) {
                        loadThuoc();
                    }
                },
                error: function(xhr) {
                    const errors = xhr.responseJSON.errors;
                    
                    if (errors.ma_nhom) {
                        $('#edit_ma_nhom').addClass('is-invalid');
                        $('#edit_ma_nhom_error').text(errors.ma_nhom[0]);
                    } else {
                        $('#edit_ma_nhom').removeClass('is-invalid');
                    }
                    
                    if (errors.ten_nhom) {
                        $('#edit_ten_nhom').addClass('is-invalid');
                        $('#edit_ten_nhom_error').text(errors.ten_nhom[0]);
                    } else {
                        $('#edit_ten_nhom').removeClass('is-invalid');
                    }
                }
            });
        });

        // Cập nhật danh sách nhóm thuốc trong dropdown
        function updateNhomThuocDropdowns() {
            $.ajax({
                url: "/nhom-thuoc",
                type: "GET",
                dataType: "json",
                success: function(response) {
                    let options = '<option value="">-- Chọn nhóm thuốc --</option>';
                    
                    if (response.nhomThuoc.data.length > 0) {
                        $.each(response.nhomThuoc.data, function(index, nhom) {
                            options += `<option value="${nhom.nhom_id}">${nhom.ten_nhom}</option>`;
                        });
                    }
                    
                    $('#nhom_id, #edit_nhom_id, #filter-nhom').each(function() {
                        const currentValue = $(this).val();
                        $(this).html(options);
                        $(this).val(currentValue);
                    });
                }
            });
        }

        // ===== PHẦN XỬ LÝ THUỐC =====
        
        // Search và filter thuốc
        $('#searchThuocBtn').click(function() {
            loadThuoc();
        });
        
        // Xử lý khi thay đổi bộ lọc nhóm thuốc
        $('#filter-nhom').change(function() {
            const nhomId = $(this).val();
            
            // Bỏ chọn tất cả các nhóm thuốc trong danh sách
            $('.nhom-thuoc-item').removeClass('active');
            
            if (nhomId) {
                // Nếu có chọn nhóm thuốc
                selectedNhomId = nhomId;
                
                // Highlight nhóm tương ứng trong danh sách bên trái (nếu có)
                $(`.nhom-thuoc-item[data-id="${nhomId}"]`).addClass('active');
                
                // Cập nhật tên nhóm từ option được chọn
                const nhomName = $(this).find('option:selected').text();
                $('#selected-nhom-name').text(' - ' + nhomName);
                $('#filter-status').text('Đang lọc theo nhóm thuốc');
            } else {
                // Nếu chọn "Tất cả nhóm"
                selectedNhomId = '';
                $('#selected-nhom-name').text('');
                $('#filter-status').text('Đang hiển thị tất cả thuốc');
                
                // Thêm hiệu ứng highlight tạm thời cho thông báo
                $('#filter-status').removeClass('text-muted').addClass('text-primary');
                setTimeout(function() {
                    $('#filter-status').removeClass('text-primary').addClass('text-muted');
                }, 1500);
            }
            
            loadThuoc();
        });

        $('#search-thuoc').keypress(function(e) {
            if (e.which == 13) {
                loadThuoc();
                return false;
            }
        });
        
        // Thêm tìm kiếm tự động sau khi nhập
        let thuocSearchTimeout;
        $('#search-thuoc').keyup(function() {
            clearTimeout(thuocSearchTimeout);
            
            // Nếu ô tìm kiếm trống, ngay lập tức tải lại toàn bộ danh sách
            if ($(this).val().trim() === '') {
                loadThuoc();
            } else {
                // Nếu có nội dung, đợi người dùng dừng gõ rồi mới tìm kiếm
                thuocSearchTimeout = setTimeout(function() {
                    loadThuoc();
                }, 500); // Đợi 500ms sau khi dừng gõ mới tìm kiếm
            }
        });
        
        // Reset tìm kiếm thuốc
        $('#resetThuocBtn').click(function() {
            $('#search-thuoc').val('');
            
            // Thêm hiệu ứng thông báo reset tìm kiếm
            showToast('Đã hiển thị lại tất cả thuốc', 'info');
            
            loadThuoc();
        });
        
        // Reset bộ lọc nhóm thuốc
        $('#resetFilterBtn').click(function() {
            $('#filter-nhom').val('');
            selectedNhomId = '';
            // Bỏ chọn tất cả nhóm thuốc
            $('.nhom-thuoc-item').removeClass('active');
            // Xóa tên nhóm đang hiển thị
            $('#selected-nhom-name').text('');
            $('#filter-status').text('Đang hiển thị tất cả thuốc');
            
            // Thêm hiệu ứng highlight tạm thời cho thông báo
            $('#filter-status').removeClass('text-muted').addClass('text-primary');
            setTimeout(function() {
                $('#filter-status').removeClass('text-primary').addClass('text-muted');
            }, 1500);
            
            loadThuoc();
        });

        // Hàm load danh sách thuốc
        function loadThuoc(page = 1) {
            const search = $('#search-thuoc').val();
            const nhomId = $('#filter-nhom').val() || selectedNhomId;
            
            // Chuẩn bị dữ liệu gửi đi
            const data = { 
                page: page,
                search: search ? search.trim() : '' // Luôn gửi tham số search
            };
            
            // Thêm tham số nhom_id nếu có
            if (nhomId && nhomId !== '') {
                data.nhom_id = nhomId;
            }
            
            $.ajax({
                url: "{{ route('thuoc.index') }}",
                type: "GET",
                data: data,
                dataType: "json",
                success: function(response) {
                    let html = '';
                    
                    if (response.thuoc.data.length > 0) {
                        $.each(response.thuoc.data, function(index, thuoc) {
                            html += `
                                <tr>
                                    <td>${thuoc.ma_thuoc}</td>
                                    <td>${thuoc.ten_thuoc}</td>
                                    <td>${thuoc.nhom_thuoc.ten_nhom}</td>
                                    <td>${thuoc.don_vi_ban}</td>
                                    <td>${thuoc.ti_le_quy_doi}</td>
                                    <td>
                                        <button type="button" class="btn btn-sm btn-info edit-thuoc-btn" data-id="${thuoc.thuoc_id}">
                                            <i class="bi bi-pencil"></i>
                                        </button>
                                        <button type="button" class="btn btn-sm btn-danger delete-thuoc-btn" 
                                            data-id="${thuoc.thuoc_id}" data-name="${thuoc.ten_thuoc}">
                                            <i class="bi bi-trash"></i>
                                        </button>
                                    </td>
                                </tr>
                            `;
                        });
                    } else {
                        html = '<tr><td colspan="6" class="text-center">Không có dữ liệu</td></tr>';
                    }
                    
                    $('#thuoc-table tbody').html(html);
                    $('#pagination-thuoc').html(response.links);

                    // Rebind pagination links
                    $('#pagination-thuoc').on('click', '.pagination a', function(e) {
                        e.preventDefault();
                        const page = $(this).attr('href').split('page=')[1];
                        loadThuoc(page);
                    });

                    // Rebind event handlers
                    bindThuocEvents();
                },
                error: function(xhr) {
                    showToast('Có lỗi xảy ra khi tải danh sách thuốc', 'danger');
                }
            });
        }

        // Bind sự kiện cho các nút trong bảng thuốc
        function bindThuocEvents() {
            // Nút sửa thuốc
            $('.edit-thuoc-btn').click(function() {
                const id = $(this).data('id');
                
                $.ajax({
                    url: "/thuoc/" + id,
                    type: "GET",
                    dataType: "json",
                    success: function(response) {
                        $('#edit_thuoc_id').val(response.thuoc.thuoc_id);
                        $('#edit_ma_thuoc').val(response.thuoc.ma_thuoc);
                        $('#edit_nhom_id').val(response.thuoc.nhom_id);
                        $('#edit_ten_thuoc').val(response.thuoc.ten_thuoc);
                        $('#edit_don_vi_goc').val(response.thuoc.don_vi_goc);
                        $('#edit_don_vi_ban').val(response.thuoc.don_vi_ban);
                        $('#edit_ti_le_quy_doi').val(response.thuoc.ti_le_quy_doi);
                        $('#edit_mo_ta_thuoc').val(response.thuoc.mo_ta);
                        
                        $('#editThuocModal').modal('show');
                    },
                    error: function(xhr) {
                        showToast('Có lỗi xảy ra khi lấy thông tin thuốc', 'danger');
                    }
                });
            });

            // Nút xóa thuốc
            $('.delete-thuoc-btn').click(function() {
                const id = $(this).data('id');
                const name = $(this).data('name');
                
                $('#delete_thuoc_name').text(name);
                $('#deleteThuocModal').modal('show');
                
                $('#confirmDeleteThuoc').off('click').on('click', function() {
                    $.ajax({
                        url: "/thuoc/" + id,
                        type: "DELETE",
                        dataType: "json",
                        success: function(response) {
                            $('#deleteThuocModal').modal('hide');
                            showToast(response.message);
                            loadThuoc();
                        },
                        error: function(xhr) {
                            $('#deleteThuocModal').modal('hide');
                            showToast(xhr.responseJSON.message, 'danger');
                        }
                    });
                });
            });
        }

        // Thêm thuốc mới
        $('#addThuocForm').submit(function(e) {
            e.preventDefault();
            
            const formData = {
                ma_thuoc: $('#ma_thuoc').val(),
                nhom_id: $('#nhom_id').val(),
                ten_thuoc: $('#ten_thuoc').val(),
                don_vi_goc: $('#don_vi_goc').val(),
                don_vi_ban: $('#don_vi_ban').val(),
                ti_le_quy_doi: $('#ti_le_quy_doi').val(),
                mo_ta: $('#mo_ta_thuoc').val(),
            };
            
            $.ajax({
                url: "{{ route('thuoc.store') }}",
                type: "POST",
                data: formData,
                dataType: "json",
                success: function(response) {
                    $('#addThuocModal').modal('hide');
                    $('#addThuocForm')[0].reset();
                    showToast(response.message);
                    loadThuoc();
                },
                error: function(xhr) {
                    const errors = xhr.responseJSON.errors;
                    
                    // Xóa tất cả invalid feedback trước
                    $('#addThuocForm .is-invalid').removeClass('is-invalid');
                    
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

        // Cập nhật thuốc
        $('#editThuocForm').submit(function(e) {
            e.preventDefault();
            
            const id = $('#edit_thuoc_id').val();
            const formData = {
                ma_thuoc: $('#edit_ma_thuoc').val(),
                nhom_id: $('#edit_nhom_id').val(),
                ten_thuoc: $('#edit_ten_thuoc').val(),
                don_vi_goc: $('#edit_don_vi_goc').val(),
                don_vi_ban: $('#edit_don_vi_ban').val(),
                ti_le_quy_doi: $('#edit_ti_le_quy_doi').val(),
                mo_ta: $('#edit_mo_ta_thuoc').val(),
            };
            
            $.ajax({
                url: "/thuoc/" + id,
                type: "PUT",
                data: formData,
                dataType: "json",
                success: function(response) {
                    $('#editThuocModal').modal('hide');
                    showToast(response.message);
                    loadThuoc();
                },
                error: function(xhr) {
                    const errors = xhr.responseJSON.errors;
                    
                    // Xóa tất cả invalid feedback trước
                    $('#editThuocForm .is-invalid').removeClass('is-invalid');
                    
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

        // ===== KHỞI TẠO =====
        
        // Bind các sự kiện ban đầu
        bindNhomThuocEvents();
        bindThuocEvents();

        // Clear form khi modal đóng
        $('#addNhomThuocModal').on('hidden.bs.modal', function() {
            $('#addNhomForm')[0].reset();
            $('#ma_nhom, #ten_nhom').removeClass('is-invalid');
        });

        $('#editNhomThuocModal').on('hidden.bs.modal', function() {
            $('#edit_ma_nhom, #edit_ten_nhom').removeClass('is-invalid');
        });

        $('#addThuocModal').on('hidden.bs.modal', function() {
            $('#addThuocForm')[0].reset();
            $('#addThuocForm .is-invalid').removeClass('is-invalid');
        });

        $('#editThuocModal').on('hidden.bs.modal', function() {
            $('#editThuocForm .is-invalid').removeClass('is-invalid');
        });
        
        // Nếu có tham số tìm kiếm nhóm thuốc trong URL thì điền vào ô tìm kiếm
        @if(request()->has('search_nhom'))
            $('#search-nhom').val("{{ request()->search_nhom }}");
            // Tự động tìm kiếm
            loadNhomThuoc();
        @endif
        
        // Nếu đã chọn nhóm từ URL (ví dụ: ?nhom_id=1)
        @if(request()->has('nhom_id') && request()->nhom_id)
            selectedNhomId = '{{ request()->nhom_id }}';
            $('#filter-nhom').val(selectedNhomId);
            
            // Tìm và highlight item nhóm thuốc tương ứng
            $('.nhom-thuoc-item[data-id="{{ request()->nhom_id }}"]').addClass('active');
            
            // Hiển thị tên nhóm đang chọn
            const nhomName = $('.nhom-thuoc-item[data-id="{{ request()->nhom_id }}"]').find('div:first').text();
            $('#selected-nhom-name').text(' - ' + nhomName);
            $('#filter-status').text('Đang lọc theo nhóm thuốc');
        @else
            $('#filter-status').text('Đang hiển thị tất cả thuốc');
        @endif
    });
</script>
@endsection
