@extends('layouts.app')

@section('title', 'Quản Lý Giá Thuốc - Hệ Thống Quản Lý Hiệu Thuốc')

@section('page-title', 'Quản Lý Giá Thuốc')

@section('styles')
<style>
    .timeline {
        position: relative;
    }
    
    .timeline-item {
        position: relative;
        padding-left: 25px;
        margin-bottom: 15px;
    }
    
    .timeline-item::before {
        content: "";
        position: absolute;
        left: 0;
        top: 4px;
        width: 12px;
        height: 12px;
        border-radius: 50%;
        background-color: #3498db;
        z-index: 2;
    }
    
    .timeline-item::after {
        content: "";
        position: absolute;
        left: 5px;
        top: 16px;
        width: 2px;
        height: calc(100% + 10px);
        background-color: #e9ecef;
        z-index: 1;
    }
    
    .timeline-item:last-child::after {
        display: none;
    }
    
    /* Định dạng giá tiền */
    .price-amount {
        font-weight: bold;
        color: #28a745;
    }
    
    .current-price {
        border-left: 4px solid #28a745 !important;
    }
</style>
@endsection

@section('content')
<div class="row">
    <div class="col-md-12">
        <div class="card shadow mb-4">
            <div class="card-header py-3 d-flex justify-content-between align-items-center">
                <h6 class="m-0 font-weight-bold">Danh Sách Giá Thuốc</h6>
                <button type="button" class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#addGiaThuocModal">
                    <i class="bi bi-plus-circle me-1"></i> Thêm Giá Thuốc
                </button>
            </div>
            <div class="card-body">
                <div class="row mb-3">
                    <div class="col-md-4">
                        <div class="input-group">
                            <select id="filter-thuoc" class="form-select">
                                <option value="">-- Tất cả thuốc --</option>
                                @foreach ($thuoc as $item)
                                <option value="{{ $item->thuoc_id }}">{{ $item->ten_thuoc }}</option>
                                @endforeach
                            </select>
                            <button class="btn btn-outline-danger" type="button" id="resetFilterBtn" title="Xóa bộ lọc">
                                <i class="bi bi-x-circle"></i>
                            </button>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="input-group">
                            <span class="input-group-text">Từ ngày</span>
                            <input type="date" id="filter-from-date" class="form-control">
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="input-group">
                            <span class="input-group-text">Đến ngày</span>
                            <input type="date" id="filter-to-date" class="form-control">
                        </div>
                    </div>
                    <div class="col-md-2">
                        <button id="searchBtn" class="btn btn-primary w-100">
                            <i class="bi bi-search"></i> Lọc
                        </button>
                    </div>
                </div>
                
                <div class="table-responsive">
                                        <table class="table table-bordered table-striped" id="gia-thuoc-table" width="100%" cellspacing="0">
                        <thead>
                            <tr>
                                <th>Mã Thuốc</th>
                                <th>Tên sản phẩm</th>
                                <th>Giá Bán</th>
                                <th>Ngày Bắt Đầu</th>
                                <th>Ngày Kết Thúc</th>
                                <th width="120px">Thao Tác</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($giaThuoc as $gia)
                                @php
                                    $isActive = isset($activeGiaByThuoc[$gia->thuoc_id]) &&
                                                $activeGiaByThuoc[$gia->thuoc_id]->gia_id == $gia->gia_id;

                                    $isFuture = isset($futureGiaByThuoc[$gia->thuoc_id]) &&
                                                $futureGiaByThuoc[$gia->thuoc_id]->gia_id == $gia->gia_id;

                                    // Use Bootstrap table row classes so background shows correctly
                                    $rowClass = $isActive ? 'table-success' : ($isFuture ? 'table-warning' : 'table-danger');
                                @endphp
                                <tr class="{{ $rowClass }}">
                                    <td>{{ $gia->thuoc->ma_thuoc }}</td>
                                    <td>{{ $gia->thuoc->ten_thuoc }}</td>
                                    <td>{{ number_format($gia->gia_ban) }} đ</td>
                                    <td>{{ $gia->ngay_bat_dau ? date('d/m/Y', strtotime($gia->ngay_bat_dau)) : '' }}</td>
                                    <td>{{ $gia->ngay_ket_thuc ? date('d/m/Y', strtotime($gia->ngay_ket_thuc)) : 'Hiện tại' }}</td>
                                    <td class="text-center">
                                        <button class="btn btn-sm btn-primary edit-btn me-1" data-id="{{ $gia->gia_id }}" {{ $isActive ? '' : 'disabled' }}>
                                            <i class="bi bi-pencil"></i>
                                        </button>
                                        <button class="btn btn-sm btn-danger delete-btn" 
                                                data-id="{{ $gia->gia_id }}"
                                                data-thuoc="{{ $gia->thuoc->ten_thuoc }}"
                                                data-date="{{ date('d/m/Y', strtotime($gia->ngay_bat_dau)) }}">
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
                
                <div class="d-flex justify-content-center mt-3" id="pagination">
                    {{ $giaThuoc->links() }}
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Modal thêm giá thuốc -->
<div class="modal fade" id="addGiaThuocModal" tabindex="-1" aria-labelledby="addGiaThuocModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <form id="addGiaThuocForm">
                <div class="modal-header">
                    <h5 class="modal-title" id="addGiaThuocModalLabel">Thêm Giá Thuốc</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <div class="mb-3">
                        <label for="thuoc_id" class="form-label">Thuốc <span class="text-danger">*</span></label>
                        <select class="form-select" id="thuoc_id" name="thuoc_id" requiredmsg="Trường này yêu cầu bắt buộc">
                            <option value="">-- Chọn thuốc --</option>
                            @foreach ($thuoc as $item)
                            <option value="{{ $item->thuoc_id }}">
                                {{ $item->ten_thuoc }}
                            </option>
                            @endforeach
                        </select>
                        <div class="invalid-feedback" id="thuoc_id_error"></div>
                    </div>
                    <div class="mb-3">
                        <label for="gia_ban" class="form-label">Giá Bán (VNĐ) <span class="text-danger">*</span></label>
                        <div class="input-group">
                            <input type="text" class="form-control money-format" id="gia_ban" name="gia_ban" placeholder="Nhập giá bán" requiredmsg="Trường này yêu cầu bắt buộc">
                            <span class="input-group-text">VNĐ</span>
                        </div>
                        <div class="invalid-feedback" id="gia_ban_error"></div>
                    </div>
                    <div class="mb-3">
                        <label for="ngay_bat_dau" class="form-label">Ngày bắt đầu <span class="text-danger">*</span></label>
                        <input type="date" class="form-control" id="ngay_bat_dau" name="ngay_bat_dau" requiredmsg="Trường này yêu cầu bắt buộc">
                        <div class="invalid-feedback" id="ngay_bat_dau_error"></div>
                    </div>
                    <div class="mb-3">
                        <label for="ngay_ket_thuc" class="form-label">Ngày kết thúc</label>
                        <input type="date" class="form-control" id="ngay_ket_thuc" name="ngay_ket_thuc">
                        <div class="invalid-feedback" id="ngay_ket_thuc_error"></div>
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

<!-- Modal sửa giá thuốc -->
<div class="modal fade" id="editGiaThuocModal" tabindex="-1" aria-labelledby="editGiaThuocModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <form id="editGiaThuocForm">
                <input type="hidden" id="edit_gia_id" name="gia_id">
                <div class="modal-header">
                    <h5 class="modal-title" id="editGiaThuocModalLabel">Cập Nhật Giá Mới</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <input type="hidden" id="edit_thuoc_id" name="thuoc_id">
                    <div class="mb-3">
                        <label class="form-label">Thuốc</label>
                        <input type="text" class="form-control" id="edit_thuoc_name" readonly>
                    </div>
                    <div class="mb-3" style="display: none;">
                        <label class="form-label">Giá Hiện Tại</label>
                        <input type="text" class="form-control" id="edit_gia_cu" readonly>
                    </div>
                    <div class="mb-3">
                        <label for="edit_gia_ban" class="form-label">Giá Mới (VNĐ) <span class="text-danger">*</span></label>
                        <div class="input-group">
                            <input type="text" class="form-control money-format" id="edit_gia_ban" name="gia_ban" requiredmsg="Trường này yêu cầu bắt buộc">
                            <span class="input-group-text">VNĐ</span>
                        </div>
                        <div class="invalid-feedback" id="edit_gia_ban_error"></div>
                    </div>
                    <div class="mb-3">
                        <label for="edit_ngay_bat_dau" class="form-label">Ngày bắt đầu <span class="text-danger">*</span></label>
                        <input type="date" class="form-control" id="edit_ngay_bat_dau" name="ngay_bat_dau" requiredmsg="Trường này yêu cầu bắt buộc">
                        <div class="invalid-feedback" id="edit_ngay_bat_dau_error"></div>
                    </div>
                    <div class="mb-3">
                        <label for="edit_ngay_ket_thuc" class="form-label">Ngày kết thúc</label>
                        <input type="date" class="form-control" id="edit_ngay_ket_thuc" name="ngay_ket_thuc">
                        <div class="invalid-feedback" id="edit_ngay_ket_thuc_error"></div>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Lịch sử đổi giá</label>
                        <div id="gia-history" style="max-height:200px;overflow-y:auto;"></div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Đóng</button>
                    <button type="submit" class="btn btn-primary">Lưu giá mới</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Modal xác nhận xóa giá thuốc -->
<div class="modal fade" id="deleteGiaThuocModal" tabindex="-1" aria-labelledby="deleteGiaThuocModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="deleteGiaThuocModalLabel">Xác nhận xóa</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <p>Bạn có chắc chắn muốn xóa giá của thuốc <span id="delete_thuoc_name" class="fw-bold"></span> từ ngày <span id="delete_date" class="fw-bold"></span>?</p>
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

        <!-- Hidden container for page-level data to avoid Blade-to-JS interpolation issues -->
        <div id="page-data" data-user-role="{{ Auth::user()->vai_tro ?? '' }}" style="display:none"></div>

        @section('scripts')
<script>
    $(document).ready(function() {
    // Lấy vai trò của người dùng từ DOM data attribute (safer than Blade-to-JS interpolation)
    const userRole = $('#page-data').data('user-role') || '';

        // Nếu là dược sĩ thì ẩn/vô hiệu hóa các nút thao tác
        function disableDuocSiActions() {
            if (userRole === 'duoc_si') {
                // Vô hiệu hóa nút thêm giá thuốc
                $("[data-bs-target='#addGiaThuocModal']").prop('disabled', true).addClass('disabled');
                // Vô hiệu hóa nút sửa/xóa trong bảng
                $('#gia-thuoc-table .edit-btn, #gia-thuoc-table .delete-btn').prop('disabled', true).addClass('disabled');
            }
        }

        // Gọi khi load trang
        disableDuocSiActions();

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
        
        // Format tiền tệ
        function formatMoney(input) {
            // Loại bỏ các ký tự không phải số
            let value = input.val().replace(/\D/g, '');
            
            // Định dạng số với dấu chấm ngăn cách hàng nghìn
            if (value !== '') {
                value = parseInt(value).toLocaleString('vi-VN');
            }
            
            input.val(value);
        }
        
        // Áp dụng định dạng tiền tệ cho input
        $(document).on('input', '.money-format', function() {
            formatMoney($(this));
        });
        
        // Hàm xử lý khi submit form để loại bỏ định dạng tiền tệ
        function prepareMoneyValue(formData) {
            // Kiểm tra xem đầu vào có phải là FormData không
            if (formData instanceof FormData) {
                // Xử lý định dạng tiền tệ
                const giaBan = formData.get('gia_ban');
                if (giaBan) {
                    const giaBanValue = giaBan.replace(/\./g, '');
                    formData.set('gia_ban', giaBanValue);
                }
                return formData;
            } else {
                // Nếu không phải FormData, giả định là jQuery form
                const form = $(formData);
                form.find('.money-format').each(function() {
                    const value = $(this).val();
                    if (value) {
                        $(this).val(value.replace(/\./g, ''));
                    }
                });
                return form;
            }
        }
        
        // Filter giá thuốc
        $('#searchBtn').click(function() {
            loadGiaThuoc();
        });

        // Helper: enable/disable the search button based on filter inputs
        function updateFilterButtonState() {
            const thuocId = $('#filter-thuoc').val();
            const fromDate = $('#filter-from-date').val();
            const toDate = $('#filter-to-date').val();

            // If both dates provided and invalid range -> disable
            if (fromDate && toDate && new Date(fromDate) > new Date(toDate)) {
                $('#searchBtn').prop('disabled', true).addClass('disabled');
                return;
            }

            // Enable search only if any filter value is present
            if ((thuocId && thuocId !== '') || (fromDate && fromDate !== '') || (toDate && toDate !== '')) {
                $('#searchBtn').prop('disabled', false).removeClass('disabled');
            } else {
                // initial state: disabled until user changes any filter
                $('#searchBtn').prop('disabled', true).addClass('disabled');
            }
        }

        // Reset filter
        $('#resetFilterBtn').click(function() {
            $('#filter-thuoc').val('');
            $('#filter-from-date').val('');
            $('#filter-to-date').val('');
            updateFilterButtonState();
            // Reload list to default (empty filters)
            loadGiaThuoc();
        });
        
        // Hàm load danh sách giá thuốc
        function loadGiaThuoc(page = 1) {
            const thuocId = $('#filter-thuoc').val();
            const fromDate = $('#filter-from-date').val();
            const toDate = $('#filter-to-date').val();
            
            const tableBody = $('#gia-thuoc-table tbody');
            showLoading(tableBody);
            
            $.ajax({
                url: "{{ route('gia-thuoc.index') }}",
                type: "GET",
                data: {
                    page: page,
                    thuoc_id: thuocId,
                    ngay_bat_dau: fromDate,
                    ngay_ket_thuc: toDate
                },
                dataType: "json",
                success: function(response) {
                    let html = '';

                    if (response.giaThuoc.data.length > 0) {
                        $.each(response.giaThuoc.data, function(index, item) {
                            const giaBan = parseInt(item.gia_ban).toLocaleString('vi-VN');
                            const ngayBatDau = formatDate(item.ngay_bat_dau);
                            const ngayKetThuc = item.ngay_ket_thuc ? formatDate(item.ngay_ket_thuc) : 'Hiện tại';

                            const activeGia = response.activeGiaByThuoc?.[item.thuoc_id];
                            const futureGia = response.futureGiaByThuoc?.[item.thuoc_id];

                            let rowClass = 'table-danger';
                            let disabledEdit = 'disabled';
                            let disabledDelete = '';

                            if (activeGia && activeGia.gia_id === item.gia_id) {
                                rowClass = 'table-success'; // đang hiệu lực
                                disabledEdit = ''; // cho phép sửa
                            } else if (futureGia && futureGia.gia_id === item.gia_id) {
                                rowClass = 'table-warning'; // sắp hiệu lực
                                disabledEdit = 'disabled'; // chưa hiệu lực -> chưa sửa
                            } else {
                                rowClass = 'table-danger'; // đã hết hạn
                                disabledEdit = 'disabled'; // không sửa giá cũ
                            }

                            html += `
                                <tr class="${rowClass}">
                                    <td>${item.thuoc.ma_thuoc}</td>
                                    <td>${item.thuoc.ten_thuoc}</td>
                                    <td class="text-end">${giaBan} đ</td>
                                    <td>${ngayBatDau}</td>
                                    <td>${ngayKetThuc}</td>
                                    <td class="text-center">
                                        <button type="button" class="btn btn-sm btn-primary edit-btn me-1" data-id="${item.gia_id}" ${disabledEdit}>
                                            <i class="bi bi-pencil"></i>
                                        </button>
                                        <button type="button" class="btn btn-sm btn-danger delete-btn" 
                                            data-id="${item.gia_id}" 
                                            data-thuoc="${item.thuoc.ten_thuoc}" 
                                            data-date="${ngayBatDau}" ${disabledDelete}>
                                            <i class="bi bi-trash"></i>
                                        </button>
                                    </td>
                                </tr>
                            `;
                        });
                    } else {
                        html = '<tr><td colspan="6" class="text-center">Không có dữ liệu</td></tr>';
                    }

                    $('#gia-thuoc-table tbody').html(html);
                    $('#pagination').html(response.links);

                    // Gắn lại sự kiện click
                    $('#pagination').on('click', '.pagination a', function(e) {
                        e.preventDefault();
                        const page = $(this).attr('href').split('page=')[1];
                        loadGiaThuoc(page);
                    });

                    bindButtons();
                    disableDuocSiActions(); // vẫn áp dụng logic ẩn cho dược sĩ
                },
                error: function() {
                    tableBody.html('<tr><td colspan="4" class="text-center text-danger">Đã xảy ra lỗi khi tải dữ liệu</td></tr>');
                    showToast('Đã xảy ra lỗi khi tải dữ liệu giá thuốc', 'danger');
                }
            });
        }
        
        // Format date từ yyyy-mm-dd thành dd/mm/yyyy
        function formatDate(dateString) {
            if (!dateString) return '';
            const date = new Date(dateString);
            return date.toLocaleDateString('vi-VN');
        }
        
        // Parse date từ dd/mm/yyyy thành yyyy-mm-dd
        function parseDate(dateString) {
            if (!dateString) return '';
            const parts = dateString.split('/');
            return `${parts[2]}-${parts[1].padStart(2, '0')}-${parts[0].padStart(2, '0')}`;
        }
        
        // Thêm giá thuốc
        $('#addGiaThuocForm').submit(function(e) {
            e.preventDefault();
            
            // Lấy dữ liệu từ form
            const formData = new FormData(this);
            const processedFormData = prepareMoneyValue(formData);
            
            // Chuyển FormData thành đối tượng
            const data = {};
            processedFormData.forEach((value, key) => {
                data[key] = value;
            });
            
            console.log('Dữ liệu gửi đi:', data); // Debug
            
            $.ajax({
                url: "{{ route('gia-thuoc.store') }}",
                type: "POST",
                data: data,
                dataType: "json",
                headers: {
                    'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                },
                success: function(response) {
                    $('#addGiaThuocModal').modal('hide');
                    $('#addGiaThuocForm')[0].reset();
                    showToast(response.message);
                    loadGiaThuoc();
                },
                error: function(xhr) {
                    const errors = xhr.responseJSON.errors;
                    
                    // Xóa tất cả invalid feedback trước
                    $('#addGiaThuocForm .is-invalid').removeClass('is-invalid');
                    
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

        // Lấy thông tin giá thuốc để sửa và lịch sử giá
        function getGiaThuoc(id) {
            $.ajax({
                url: `/gia-thuoc/${id}`,
                type: "GET",
                dataType: "json",
                success: function(response) {
                    const giaThuoc = response.giaThuoc;
                    $('#edit_gia_id').val(giaThuoc.gia_id);
                    $('#edit_thuoc_id').val(giaThuoc.thuoc_id);
                    $('#edit_thuoc_name').val(giaThuoc.thuoc.ten_thuoc);
                    $('#edit_gia_ban').val(parseInt(giaThuoc.gia_ban).toLocaleString('vi-VN'));

                    // Format ngày
                    if (giaThuoc.ngay_bat_dau) {
                        const ngayBatDau = giaThuoc.ngay_bat_dau.split('T')[0];
                        $('#edit_ngay_bat_dau').val(ngayBatDau);
                    }

                    if (giaThuoc.ngay_ket_thuc) {
                        const ngayKetThuc = giaThuoc.ngay_ket_thuc.split('T')[0];
                        $('#edit_ngay_ket_thuc').val(ngayKetThuc);
                    } else {
                        $('#edit_ngay_ket_thuc').val('');
                    }

                    // Lấy lịch sử giá của thuốc
                    $.ajax({
                        url: `/gia-thuoc-history/${giaThuoc.thuoc_id}`,
                        type: "GET",
                        dataType: "json",
                        success: function(res) {
                            const history = res.history || [];
                            let html = '';
                            if (history.length > 0) {
                                html += '<ul class="list-group">';
                                history.forEach(function(item) {
                                    html += `<li class="list-group-item d-flex justify-content-between align-items-center">
                                        <span>${item.gia_ban.toLocaleString('vi-VN')} đ</span>
                                        <span>${formatDate(item.ngay_bat_dau)} - ${item.ngay_ket_thuc ? formatDate(item.ngay_ket_thuc) : 'Hiện tại'}</span>
                                    </li>`;
                                });
                                html += '</ul>';
                            } else {
                                html = '<div class="text-muted">Không có lịch sử giá</div>';
                            }
                            $('#gia-history').html(html);
                        },
                        error: function() {
                            $('#gia-history').html('<div class="text-danger">Không thể tải lịch sử giá</div>');
                        }
                    });

                    $('#editGiaThuocModal').modal('show');
                },
                error: function(xhr) {
                    showToast('Có lỗi xảy ra khi lấy thông tin giá thuốc', 'danger');
                }
            });
        }

        // Cập nhật giá thuốc
        $('#editGiaThuocForm').submit(function(e) {
            e.preventDefault();
            
            const id = $('#edit_gia_id').val();
            
            // Lấy dữ liệu từ form
            const formData = new FormData(this);
            const processedFormData = prepareMoneyValue(formData);
            
            // Chuyển FormData thành đối tượng
            const data = {};
            processedFormData.forEach((value, key) => {
                data[key] = value;
            });
            
            console.log('Dữ liệu cập nhật:', data); // Debug
            
            $.ajax({
                url: `/gia-thuoc/${id}`,
                type: "PUT",
                data: data,
                dataType: "json",
                headers: {
                    'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                },
                success: function(response) {
                    $('#editGiaThuocModal').modal('hide');
                    showToast(response.message);
                    loadGiaThuoc();
                },
                error: function(xhr) {
                    const errors = xhr.responseJSON.errors;
                    
                    // Xóa tất cả invalid feedback trước
                    $('#editGiaThuocForm .is-invalid').removeClass('is-invalid');
                    
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

        // Xóa giá thuốc
        let deleteId = null;
        
        function bindButtons() {
            // Nút sửa giá thuốc
            $('.edit-btn').click(function() {
                const id = $(this).data('id');
                getGiaThuoc(id);
            });
            
            // Nút xóa giá thuốc
            $('.delete-btn').click(function() {
                deleteId = $(this).data('id');
                const thuocName = $(this).data('thuoc');
                const date = $(this).data('date');
                
                $('#delete_thuoc_name').text(thuocName);
                $('#delete_date').text(date);
                $('#deleteGiaThuocModal').modal('show');
            });
        }
        
        // Xác nhận xóa giá thuốc
        $('#confirmDelete').click(function() {
            if (!deleteId) return;
            
            $.ajax({
                url: `/gia-thuoc/${deleteId}`,
                type: "DELETE",
                dataType: "json",
                headers: {
                    'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                },
                success: function(response) {
                    $('#deleteGiaThuocModal').modal('hide');
                    showToast(response.message);
                    loadGiaThuoc();
                },
                error: function(xhr) {
                    $('#deleteGiaThuocModal').modal('hide');
                    showToast(xhr.responseJSON.message, 'danger');
                }
            });
        });

        // Theo dõi thay đổi khi chọn thuốc/ngày để cập nhật trạng thái nút Lọc
        $('#filter-thuoc').change(function() {
            updateFilterButtonState();
        });
        $('#filter-from-date, #filter-to-date').on('input change', function() {
            updateFilterButtonState();
        });
        
    // Khởi tạo
    bindButtons();
    disableDuocSiActions();
    // Ensure search button initial state matches fresh page (disabled until filters set)
    updateFilterButtonState();

        // Clear form khi đóng modal
        $('#addGiaThuocModal').on('hidden.bs.modal', function() {
            $('#addGiaThuocForm')[0].reset();
            $('#addGiaThuocForm .is-invalid').removeClass('is-invalid');
        });
        
        $('#editGiaThuocModal').on('hidden.bs.modal', function() {
            $('#editGiaThuocForm .is-invalid').removeClass('is-invalid');
        });
        
        // Set ngày hôm nay cho input ngày bắt đầu khi mở modal thêm mới
        $('#addGiaThuocModal').on('show.bs.modal', function() {
            const today = new Date().toISOString().split('T')[0];
            $('#ngay_bat_dau').val(today);
        });
        
        // Kiểm tra nếu không có thuốc nào khả dụng thì vô hiệu hóa nút submit
        if ($('#thuoc_id option').length <= 1) {
            $('#addGiaThuocForm button[type="submit"]').prop('disabled', true);
        }
    });
</script>
@endsection
