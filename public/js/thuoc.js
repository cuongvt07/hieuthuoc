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
        $('#search-nhom').val('');
        showToast('Đã hiển thị lại tất cả nhóm thuốc', 'info');
        loadNhomThuoc(1);
    });
    
    // Tìm kiếm tự động sau khi nhập
    let nhomThuocSearchTimeout;
    $('#search-nhom').keyup(function() {
        clearTimeout(nhomThuocSearchTimeout);
        if ($(this).val().trim() === '') {
            loadNhomThuoc(1);
        } else {
            nhomThuocSearchTimeout = setTimeout(function() {
                loadNhomThuoc(1);
            }, 500);
        }
    });

    // Load danh sách nhóm thuốc
    function loadNhomThuoc(page = 1) {
        const search = $('#search-nhom').val();
        const data = { 
            page: page,
            search_nhom: search ? search.trim() : ''
        };
        
        $.ajax({
            url: "/nhom-thuoc-list",
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
                                    ${nhom.trang_thai == 1 ? '<span class="badge bg-danger ms-2">Đã đình chỉ</span>' : ''}
                                </div>
                                <div>
                                    <button type="button" class="btn btn-sm btn-info edit-nhom-btn" data-id="${nhom.nhom_id}">
                                        <i class="bi bi-pencil"></i>
                                    </button>
                                    <button type="button" class="btn btn-sm btn-danger delete-nhom-btn" 
                                        data-id="${nhom.nhom_id}" data-name="${nhom.ten_nhom}">
                                        <i class="bi bi-trash"></i>
                                    </button>
                                    <button type="button" class="btn btn-sm btn-warning suspend-nhom-btn" 
                                        data-id="${nhom.nhom_id}" data-status="${nhom.trang_thai}">
                                        <i class="bi bi-ban"></i> ${nhom.trang_thai == 0 ? 'Bỏ đình chỉ' : 'Đình chỉ'}
                                    </button>
                                </div>
                            </a>`;
                    });
                } else {
                    html = '<div class="list-group-item">Không có dữ liệu</div>';
                }
                
                $('.nhom-thuoc-list').html(html);
                
                // Cập nhật phân trang cho nhóm thuốc
                let paginationHtml = '';
                if (response.nhomThuoc.last_page > 1) {
                    paginationHtml = '<ul class="pagination justify-content-center">';
                    
                    // Nút Previous
                    if (response.nhomThuoc.current_page > 1) {
                        paginationHtml += `
                            <li class="page-item">
                                <a class="page-link" href="#" data-page="${response.nhomThuoc.current_page - 1}">
                                    <i class="bi bi-chevron-left"></i>
                                </a>
                            </li>`;
                    } else {
                        paginationHtml += `
                            <li class="page-item disabled">
                                <span class="page-link">
                                    <i class="bi bi-chevron-left"></i>
                                </span>
                            </li>`;
                    }
                    
                    // Các trang
                    for (let i = 1; i <= response.nhomThuoc.last_page; i++) {
                        if (i === response.nhomThuoc.current_page) {
                            paginationHtml += `
                                <li class="page-item active">
                                    <span class="page-link">${i}</span>
                                </li>`;
                        } else {
                            paginationHtml += `
                                <li class="page-item">
                                    <a class="page-link" href="#" data-page="${i}">${i}</a>
                                </li>`;
                        }
                    }
                    
                    // Nút Next
                    if (response.nhomThuoc.current_page < response.nhomThuoc.last_page) {
                        paginationHtml += `
                            <li class="page-item">
                                <a class="page-link" href="#" data-page="${response.nhomThuoc.current_page + 1}">
                                    <i class="bi bi-chevron-right"></i>
                                </a>
                            </li>`;
                    } else {
                        paginationHtml += `
                            <li class="page-item disabled">
                                <span class="page-link">
                                    <i class="bi bi-chevron-right"></i>
                                </span>
                            </li>`;
                    }
                    
                    paginationHtml += '</ul>';
                }
                
                $('#pagination-nhom').html(paginationHtml);

                // Xử lý click vào nút phân trang
                $('#pagination-nhom').on('click', '.page-link', function(e) {
                    e.preventDefault();
                    const page = $(this).data('page');
                    if (page) {
                        loadNhomThuoc(page);
                    }
                });

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
            
            $('.nhom-thuoc-item').removeClass('active');
            
            if (isAlreadyActive) {
                selectedNhomId = '';
                $('#filter-nhom').val('');
                $('#selected-nhom-name').text('');
                $('#filter-status').text('Đang hiển thị tất cả thuốc');
                $('#filter-status').removeClass('text-muted').addClass('text-primary');
                setTimeout(function() {
                    $('#filter-status').removeClass('text-primary').addClass('text-muted');
                }, 1500);
            } else {
                $(this).addClass('active');
                selectedNhomId = clickedId;
                $('#filter-nhom').val(selectedNhomId);
                const nhomName = $(this).find('div:first').text();
                $('#selected-nhom-name').text(' - ' + nhomName);
                $('#filter-status').text('Đang lọc theo nhóm thuốc');
            }
            
            loadThuoc();
        });

        // Nút sửa nhóm thuốc
        $('.edit-nhom-btn').click(function(e) {
            e.preventDefault();
            e.stopPropagation();
            
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

        // Nút đình chỉ nhóm thuốc
        $('.suspend-nhom-btn').click(function(e) {
            e.preventDefault();
            e.stopPropagation();
            
            const id = $(this).data('id');
            const currentStatus = $(this).data('status');
            const newStatus = currentStatus == 1 ? 0 : 1;
            
            $.ajax({
                url: `/nhom-thuoc/${id}/suspend`,
                type: "POST",
                data: { 
                    trang_thai: newStatus,
                    _token: $('meta[name="csrf-token"]').attr('content')
                },
                success: function(response) {
                    showToast(response.message);
                    loadNhomThuoc();
                    loadThuoc();

                    // Kiểm tra và cập nhật trạng thái nếu có trong response
                    if (response.trang_thai !== undefined) {
                        const button = $(`.suspend-nhom-btn[data-id="${id}"]`);
                        button.data('status', response.trang_thai);
                        if (response.trang_thai == 0) {
                            button.html('<i class="bi bi-ban"></i> Bỏ đình chỉ');
                        } else {
                            button.html('<i class="bi bi-ban"></i> Đình chỉ');
                        }
                    }
                },
                error: function(xhr) {
                    showToast('Có lỗi xảy ra khi thực hiện thao tác', 'danger');
                }
            });
        });

        // Nút xóa nhóm thuốc
        $('.delete-nhom-btn').click(function(e) {
            e.preventDefault();
            e.stopPropagation();
            
            const id = $(this).data('id');
            const name = $(this).data('name');
            
            $('#delete_nhom_name').text(name);
            $('#deleteNhomThuocModal').modal('show');
            
            $('#confirmDeleteNhom').off('click').on('click', function() {
                $.ajax({
                    url: "/nhom-thuoc/" + id,
                    type: "DELETE",
                    data: {
                        _token: $('meta[name="csrf-token"]').attr('content')
                    },
                    dataType: "json",
                    success: function(response) {
                        $('#deleteNhomThuocModal').modal('hide');
                        showToast(response.message);
                        
                        if (selectedNhomId == id) {
                            selectedNhomId = '';
                            $('#filter-nhom').val('');
                        }
                        
                        loadNhomThuoc();
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
            _token: $('meta[name="csrf-token"]').attr('content')
        };
        
        $.ajax({
            url: "/nhom-thuoc",
            type: "POST",
            data: formData,
            dataType: "json",
            success: function(response) {
                $('#addNhomThuocModal').modal('hide');
                $('#addNhomForm')[0].reset();
                showToast(response.message);
                loadNhomThuoc();
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
            _token: $('meta[name="csrf-token"]').attr('content')
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
                updateNhomThuocDropdowns();
                
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

    // Cập nhật dropdown nhóm thuốc
    function updateNhomThuocDropdowns() {
        $.ajax({
            url: "/nhom-thuoc/all",
            type: "GET",
            dataType: "json",
            success: function(response) {
                let options = '<option value="">-- Chọn nhóm thuốc --</option>';
                
                if (response.nhomThuoc.data.length > 0) {
                    response.nhomThuoc.data.forEach(function(nhom) {
                        options += `<option value="${nhom.nhom_id}">${nhom.ten_nhom}</option>`;
                    });
                }
                
                $('#nhom_id, #edit_nhom_id, #filter-nhom').html(options);
                
                // Giữ lại giá trị đã chọn (nếu có)
                if (selectedNhomId) {
                    $('#filter-nhom').val(selectedNhomId);
                }
            },
            error: function(xhr) {
                showToast('Có lỗi xảy ra khi cập nhật danh sách nhóm thuốc', 'danger');
            }
        });
    }

    // ===== PHẦN XỬ LÝ THUỐC =====
    
    // Search và filter thuốc
    $('#searchThuocBtn').click(function() {
        loadThuoc();
    });

    $('#search-thuoc').keypress(function(e) {
        if (e.which == 13) {
            loadThuoc();
            return false;
        }
    });
    
    // Tìm kiếm tự động sau khi nhập
    let thuocSearchTimeout;
    $('#search-thuoc').keyup(function() {
        clearTimeout(thuocSearchTimeout);
        if ($(this).val().trim() === '') {
            loadThuoc();
        } else {
            thuocSearchTimeout = setTimeout(function() {
                loadThuoc();
            }, 500);
        }
    });
    
    // Reset tìm kiếm thuốc
    $('#resetThuocBtn').click(function() {
        $('#search-thuoc').val('');
        showToast('Đã hiển thị lại tất cả thuốc', 'info');
        loadThuoc();
    });
    
    // Reset bộ lọc nhóm thuốc
    $('#resetFilterBtn').click(function() {
        $('#filter-nhom').val('');
        selectedNhomId = '';
        $('#selected-nhom-name').text('');
        $('.nhom-thuoc-item').removeClass('active');
        $('#filter-status').text('Đang hiển thị tất cả thuốc');
        loadThuoc();
    });
    
    // Xử lý khi thay đổi bộ lọc nhóm thuốc
    $('#filter-nhom').change(function() {
        const nhomId = $(this).val();
        $('.nhom-thuoc-item').removeClass('active');
        
        if (nhomId) {
            selectedNhomId = nhomId;
            $(`.nhom-thuoc-item[data-id="${nhomId}"]`).addClass('active');
            const nhomName = $(this).find('option:selected').text();
            $('#selected-nhom-name').text(' - ' + nhomName);
            $('#filter-status').text('Đang lọc theo nhóm thuốc');
        } else {
            selectedNhomId = '';
            $('#selected-nhom-name').text('');
            $('#filter-status').text('Đang hiển thị tất cả thuốc');
        }
        
        loadThuoc();
    });

    // Nút đình chỉ thuốc
    $(document).on('click', '.suspend-thuoc-btn', function(e) {
        e.preventDefault();
        e.stopPropagation();
        const id = $(this).data('id');
        const currentStatus = $(this).data('status');
        const newStatus = currentStatus == 1 ? 0 : 1;

        $.ajax({
            url: `/thuoc/${id}/suspend`,
            type: "POST",
            data: { 
                trang_thai: newStatus,
                _token: $('meta[name="csrf-token"]').attr('content')
            },
            success: function(response) {
                showToast(response.message);
                loadThuoc();
            },
            error: function(xhr) {
                showToast('Có lỗi xảy ra khi thực hiện thao tác', 'danger');
            }
        });
    });

    // Load danh sách thuốc
    function loadThuoc(page = 1) {
        const search = $('#search-thuoc').val();
        const nhomId = $('#filter-nhom').val() || selectedNhomId;
        
        const data = { page: page };
        
        if (search && search.trim() !== '') {
            data.search = search.trim();
        }
        
        if (nhomId && nhomId !== '') {
            data.nhom_id = nhomId;
        }
        
        $.ajax({
            url: "/thuoc-list",
            type: "GET",
            data: data,
            dataType: "json",
            success: function(response) {
                let html = '';
                
                if (response.thuoc.data.length > 0) {
                    response.thuoc.data.forEach(function(item) {
                        html += `
                            <tr>
                                <td>${item.ma_thuoc}</td>
                                <td>
                                    ${item.ten_thuoc}
                                    ${item.trang_thai == 1 ? '<span class="badge bg-danger ms-2">Đã đình chỉ</span>' : ''}
                                </td>
                                <td>${item.nhomThuoc.ten_nhom}</td>
                                <td>${item.don_vi_ban}</td>
                                <td>${item.ti_le_quy_doi}</td>
                                <td>
                                    ${item.trang_thai == 1 
                                        ? '<span class="badge bg-danger">Đã đình chỉ</span>' 
                                        : '<span class="badge bg-success">Đang hoạt động</span>'
                                    }
                                </td>
                                <td>
                                    <button type="button" class="btn btn-sm btn-info edit-thuoc-btn" data-id="${item.thuoc_id}">
                                        <i class="bi bi-pencil"></i>
                                    </button>
                                    <button type="button" class="btn btn-sm btn-danger delete-thuoc-btn" 
                                        data-id="${item.thuoc_id}" data-name="${item.ten_thuoc}">
                                        <i class="bi bi-trash"></i>
                                    </button>
                                    <button type="button" class="btn btn-sm btn-warning suspend-thuoc-btn" 
                                        data-id="${item.thuoc_id}" data-status="${item.trang_thai}">
                                        <i class="bi bi-ban"></i> 
                                        ${item.trang_thai == 0 ? 'Bỏ đình chỉ' : 'Đình chỉ'}
                                    </button>
                                </td>
                            </tr>`;
                    });
                } else {
                    html = '<tr><td colspan="7" class="text-center">Không có dữ liệu</td></tr>';
                }
                
                $('#thuoc-table tbody').html(html);
                
                // Cập nhật phân trang
                let paginationHtml = '';
                if (response.thuoc.last_page > 1) {
                    paginationHtml = '<ul class="pagination justify-content-center">';
                    
                    // Nút Previous
                    if (response.thuoc.current_page > 1) {
                        paginationHtml += `
                            <li class="page-item">
                                <a class="page-link" href="#" data-page="${response.thuoc.current_page - 1}">
                                    <i class="bi bi-chevron-left"></i>
                                </a>
                            </li>`;
                    } else {
                        paginationHtml += `
                            <li class="page-item disabled">
                                <span class="page-link">
                                    <i class="bi bi-chevron-left"></i>
                                </span>
                            </li>`;
                    }
                    
                    // Các trang
                    for (let i = 1; i <= response.thuoc.last_page; i++) {
                        if (i === response.thuoc.current_page) {
                            paginationHtml += `
                                <li class="page-item active">
                                    <span class="page-link">${i}</span>
                                </li>`;
                        } else {
                            paginationHtml += `
                                <li class="page-item">
                                    <a class="page-link" href="#" data-page="${i}">${i}</a>
                                </li>`;
                        }
                    }
                    
                    // Nút Next
                    if (response.thuoc.current_page < response.thuoc.last_page) {
                        paginationHtml += `
                            <li class="page-item">
                                <a class="page-link" href="#" data-page="${response.thuoc.current_page + 1}">
                                    <i class="bi bi-chevron-right"></i>
                                </a>
                            </li>`;
                    } else {
                        paginationHtml += `
                            <li class="page-item disabled">
                                <span class="page-link">
                                    <i class="bi bi-chevron-right"></i>
                                </span>
                            </li>`;
                    }
                    
                    paginationHtml += '</ul>';
                }
                
                $('#pagination-thuoc').html(paginationHtml);

                // Xử lý click vào nút phân trang
                $('#pagination-thuoc').on('click', '.page-link', function(e) {
                    e.preventDefault();
                    const page = $(this).data('page');
                    if (page) {
                        loadThuoc(page);
                    }
                });
            },
            error: function(xhr) {
                showToast('Có lỗi xảy ra khi tải danh sách thuốc', 'danger');
            }
        });
    }

    // Load dữ liệu ban đầu
    loadNhomThuoc();
    loadThuoc();
});
