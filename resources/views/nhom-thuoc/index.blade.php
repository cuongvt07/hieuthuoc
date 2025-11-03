@extends('layouts.app')

@section('title', 'Quản Lý Nhóm Thuốc - Hệ Thống Quản Lý Hiệu Thuốc')

@section('page-title', 'Quản Lý Nhóm Thuốc')

@section('content')
<div class="card shadow mb-4">
    <div class="card-header py-3 d-flex justify-content-between align-items-center">
        <h6 class="m-0 font-weight-bold">Danh Sách Nhóm Thuốc</h6>
        <button type="button" class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#addModal">
            <i class="bi bi-plus-circle me-1"></i> Thêm Nhóm Thuốc
        </button>
    </div>
    <div class="card-body">
        <div class="mb-3">
            <div class="input-group">
                <input type="text" id="search" class="form-control" placeholder="Tìm kiếm theo mã, tên nhóm...">
                <button class="btn btn-outline-secondary" type="button" id="searchBtn">
                    <i class="bi bi-search"></i>
                </button>
            </div>
        </div>
        
        <div class="table-responsive">
            <table class="table table-bordered table-striped" id="dataTable" width="100%" cellspacing="0">
                <thead>
                    <tr>
                        <th>Mã Nhóm</th>
                        <th>Tên Nhóm</th>
                        <th>Mô Tả</th>
                        <th>Thao Tác</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($nhomThuoc as $nhom)
                    <tr>
                        <td>{{ $nhom->ma_nhom }}</td>
                        <td>{{ $nhom->ten_nhom }}</td>
                        <td>{{ $nhom->mo_ta ?? 'N/A' }}</td>
                        <td>
                            <button type="button" class="btn btn-sm btn-info edit-btn" 
                                data-id="{{ $nhom->nhom_id }}">
                                <i class="bi bi-pencil"></i>
                            </button>
                            <button type="button" class="btn btn-sm btn-danger delete-btn" 
                                data-id="{{ $nhom->nhom_id }}" data-name="{{ $nhom->ten_nhom }}">
                                <i class="bi bi-trash"></i>
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
        
        <div class="d-flex justify-content-end mt-3" id="pagination">
            {{ $nhomThuoc->links('pagination::bootstrap-4') }}
        </div>
    </div>
</div>

<!-- Add Modal -->
<div class="modal fade" id="addModal" tabindex="-1" aria-labelledby="addModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <form id="addForm">
                <div class="modal-header">
                    <h5 class="modal-title" id="addModalLabel">Thêm Nhóm Thuốc</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <div class="mb-3">
                        <label for="ma_nhom" class="form-label">Mã Nhóm <span class="text-danger">*</span></label>
                        <input type="text" class="form-control" id="ma_nhom" name="ma_nhom" requiredmsg="Trường này yêu cầu bắt buộc">
                        <div class="invalid-feedback" id="ma_nhom_error"></div>
                    </div>
                    <div class="mb-3">
                        <label for="ten_nhom" class="form-label">Tên Nhóm <span class="text-danger">*</span></label>
                        <input type="text" class="form-control" id="ten_nhom" name="ten_nhom" requiredmsg="Trường này yêu cầu bắt buộc">
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

<!-- Edit Modal -->
<div class="modal fade" id="editModal" tabindex="-1" aria-labelledby="editModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <form id="editForm">
                <div class="modal-header">
                    <h5 class="modal-title" id="editModalLabel">Chỉnh Sửa Nhóm Thuốc</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <input type="hidden" id="edit_nhom_id" name="nhom_id">
                    <div class="mb-3">
                        <label for="edit_ma_nhom" class="form-label">Mã Nhóm <span class="text-danger">*</span></label>
                        <input type="text" class="form-control" id="edit_ma_nhom" name="ma_nhom" requiredmsg="Trường này yêu cầu bắt buộc">
                        <div class="invalid-feedback" id="edit_ma_nhom_error"></div>
                    </div>
                    <div class="mb-3">
                        <label for="edit_ten_nhom" class="form-label">Tên Nhóm <span class="text-danger">*</span></label>
                        <input type="text" class="form-control" id="edit_ten_nhom" name="ten_nhom" requiredmsg="Trường này yêu cầu bắt buộc">
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

<!-- Delete Confirmation Modal -->
<div class="modal fade" id="deleteModal" tabindex="-1" aria-labelledby="deleteModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="deleteModalLabel">Xác nhận xóa</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <p>Bạn có chắc chắn muốn xóa nhóm thuốc <span id="delete_nhom_name" class="fw-bold"></span>?</p>
                <p class="text-danger mb-0">Lưu ý: Hành động này không thể hoàn tác.</p>
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
        // Search function
        $('#searchBtn').click(function() {
            loadData();
        });

        $('#search').keypress(function(e) {
            if (e.which == 13) {
                loadData();
                return false;
            }
        });

        function loadData(page = 1) {
            const search = $('#search').val();
            
            $.ajax({
                url: "{{ route('nhom-thuoc.index') }}",
                type: "GET",
                data: {
                    search: search,
                    page: page
                },
                dataType: "json",
                success: function(response) {
                    let html = '';
                    
                    if (response.nhomThuoc.data.length > 0) {
                        $.each(response.nhomThuoc.data, function(index, nhom) {
                            html += `
                                <tr>
                                    <td>${nhom.ma_nhom}</td>
                                    <td>${nhom.ten_nhom}</td>
                                    <td>${nhom.mo_ta || 'N/A'}</td>
                                    <td>
                                        <button type="button" class="btn btn-sm btn-info edit-btn" 
                                            data-id="${nhom.nhom_id}">
                                            <i class="bi bi-pencil"></i>
                                        </button>
                                        <button type="button" class="btn btn-sm btn-danger delete-btn" 
                                            data-id="${nhom.nhom_id}" data-name="${nhom.ten_nhom}">
                                            <i class="bi bi-trash"></i>
                                        </button>
                                    </td>
                                </tr>
                            `;
                        });
                    } else {
                        html = '<tr><td colspan="4" class="text-center">Không có dữ liệu</td></tr>';
                    }
                    
                    $('#dataTable tbody').html(html);
                    $('#pagination').html(response.links);

                    // Rebind pagination links
                    $('body').on('click', '.pagination a', function(e) {
                        e.preventDefault();
                        const page = $(this).attr('href').split('page=')[1];
                        loadData(page);
                    });

                    // Rebind edit and delete buttons
                    bindEditButtons();
                    bindDeleteButtons();
                },
                error: function(xhr) {
                    showToast('Có lỗi xảy ra khi tải dữ liệu', 'danger');
                }
            });
        }

        // Add new nhom thuoc
        $('#addForm').submit(function(e) {
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
                    $('#addModal').modal('hide');
                    $('#addForm')[0].reset();
                    showToast(response.message);
                    loadData();
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

        // Get nhom thuoc details for editing
        function bindEditButtons() {
            $('.edit-btn').click(function() {
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
                        
                        $('#editModal').modal('show');
                    },
                    error: function(xhr) {
                        showToast('Có lỗi xảy ra khi lấy thông tin nhóm thuốc', 'danger');
                    }
                });
            });
        }

        // Update nhom thuoc
        $('#editForm').submit(function(e) {
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
                    $('#editModal').modal('hide');
                    showToast(response.message);
                    loadData();
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

        // Delete nhom thuoc
        function bindDeleteButtons() {
            $('.delete-btn').click(function() {
                const id = $(this).data('id');
                const name = $(this).data('name');
                
                $('#delete_nhom_name').text(name);
                $('#deleteModal').modal('show');
                
                $('#confirmDelete').off('click').on('click', function() {
                    $.ajax({
                        url: "/nhom-thuoc/" + id,
                        type: "DELETE",
                        dataType: "json",
                        success: function(response) {
                            $('#deleteModal').modal('hide');
                            showToast(response.message);
                            loadData();
                        },
                        error: function(xhr) {
                            $('#deleteModal').modal('hide');
                            showToast(xhr.responseJSON.message, 'danger');
                        }
                    });
                });
            });
        }

        // Initial binding of buttons
        bindEditButtons();
        bindDeleteButtons();

        // Clear form when modal is closed
        $('#addModal').on('hidden.bs.modal', function() {
            $('#addForm')[0].reset();
            $('#ma_nhom, #ten_nhom').removeClass('is-invalid');
        });

        $('#editModal').on('hidden.bs.modal', function() {
            $('#edit_ma_nhom, #edit_ten_nhom').removeClass('is-invalid');
        });
    });
</script>
@endsection
