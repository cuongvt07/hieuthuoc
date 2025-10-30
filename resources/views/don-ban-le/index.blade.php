@extends('layouts.app')

@section('title', 'Quản lý hóa đơn')

@section('styles')
<style>
    .badge-hoan-thanh {
        background-color: #28a745;
        color: white;
    }
    .badge-da-huy {
        background-color: #dc3545;
        color: white;
    }
    .summary-card {
        border-left: 4px solid #6c757d;
        box-shadow: 0 0.125rem 0.25rem rgba(0, 0, 0, 0.075);
    }
    .summary-card-orders {
        border-left-color: #007bff;
    }
    .summary-card-revenue {
        border-left-color: #28a745;
    }
    .table-hover tbody tr:hover {
        background-color: rgba(0, 123, 255, 0.08);
        cursor: pointer;
    }
    
    /* Dropdown styles */
    .dropdown-menu {
        min-width: 160px;
        box-shadow: 0 0.5rem 1rem rgba(0, 0, 0, 0.15);
        border: 1px solid rgba(0, 0, 0, 0.15);
    }

    .dropdown-item {
        padding: 0.5rem 1rem;
        font-size: 0.875rem;
        transition: all 0.15s ease-in-out;
    }

    .dropdown-item:hover {
        background-color: #f8f9fa;
    }

    .dropdown-item i {
        width: 16px;
        margin-right: 8px;
    }

    .dropdown-toggle::after {
        margin-left: 0.5em;
    }

    .btn-sm {
        font-size: 0.8rem;
        padding: 0.25rem 0.5rem;
    }

    /* Loading state styles */
    .dropdown-item:disabled {
        opacity: 0.6;
        cursor: not-allowed;
    }

    /* Badge styles */
    .badge {
        font-size: 0.75em;
        padding: 0.375em 0.75em;
        font-weight: 500;
    }

    .badge-success {
        background-color: #28a745;
    }

    .badge-warning {
        background-color: #ffc107;
        color: #212529;
    }

    .badge-danger {
        background-color: #dc3545;
    }

    .badge-secondary {
        background-color: #6c757d;
    }
    
    .badge-info {
        background-color: #17a2b8;
        color: white;
    }
</style>
@endsection

@section('content')
<div class="container-fluid">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h1 class="h3 mb-0 text-gray-800">Quản lý hóa đơn</h1>
        @if(Auth::user() && Auth::user()->vai_tro === 'duoc_si')
        <button type="button" class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#createOrderModal">
            <i class="fas fa-plus"></i> Tạo đơn mới
        </button>
        @endif
    </div>

    <!-- Filter Card -->
    <div class="card shadow mb-4">
        <div class="card-header py-3 d-flex justify-content-between align-items-center">
            <h6 class="m-0 font-weight-bold text-primary">Bộ lọc</h6>
        </div>
        <div class="card-body">
            <form id="filter-form" method="GET">
                <div class="row align-items-end">
                    <div class="col-md-3 mb-3">
                        <label for="keyword">Tìm kiếm:</label>
                        <input type="text" class="form-control" id="keyword" name="keyword" 
                            value="{{ request('keyword') }}" placeholder="Mã đơn, tên/SĐT khách hàng...">
                    </div>
                    <div class="col-md-2 mb-3">
                        <label for="from_date">Từ ngày:</label>
                        <input type="date" class="form-control" id="from_date" name="from_date" 
                            value="{{ request('from_date') }}">
                    </div>
                    <div class="col-md-2 mb-3">
                        <label for="to_date">Đến ngày:</label>
                        <input type="date" class="form-control" id="to_date" name="to_date" 
                            value="{{ request('to_date') }}">
                    </div>
                    <div class="col-md-2 mb-3">
                        <label for="status">Trạng thái:</label>
                        <select class="form-control" id="status" name="status">
                            <option value="">Tất cả</option>
                            <option value="hoan_tat" {{ request('status') == 'hoan_tat' || request('status') == 'hoan_thanh' ? 'selected' : '' }}>
                                Hoàn tất
                            </option>
                            <option value="cho_xu_ly" {{ request('status') == 'cho_xu_ly' ? 'selected' : '' }}>
                                Chờ xử lý
                            </option>
                            <option value="huy" {{ request('status') == 'huy' || request('status') == 'da_huy' ? 'selected' : '' }}>
                                Đã hủy
                            </option>
                        </select>
                    </div>
                    <div class="col-md-2 mb-3">
                        <label for="staff">Nhân viên:</label>
                        <select class="form-control" id="staff" name="staff">
                            <option value="">Tất cả</option>
                            @foreach($nhanViens as $nv)
                            <option value="{{ $nv->nguoi_dung_id }}" {{ request('staff') == $nv->nguoi_dung_id ? 'selected' : '' }}>
                                {{ $nv->ho_ten }}
                            </option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-md-2 mb-3 d-flex align-items-end">
                        <div class="btn-group w-100">
                            <button type="submit" class="btn btn-primary">
                                <i class="fas fa-search"></i> Lọc
                            </button>
                            <button type="button" class="btn btn-secondary" id="clearFilters">
                                <i class="bi bi-x-circle"></i> Reset
                            </button>
                        </div>
                    </div>
                </div>
            </form>
        </div>
    </div>

    <!-- Summary Cards Row -->
    <div class="row mb-4">
        <div class="col-md-6 col-lg-3 mb-4">
            <div class="card border-left-primary shadow h-100 py-2 summary-card summary-card-orders">
                <div class="card-body">
                    <div class="row no-gutters align-items-center">
                        <div class="col mr-2">
                            <div class="text-xs font-weight-bold text-primary text-uppercase mb-1">
                                Tổng đơn hàng
                            </div>
                            <div class="h5 mb-0 font-weight-bold text-gray-800" id="summary-total-orders">
                                {{ $donBanLes->total() }}
                            </div>
                        </div>
                        <div class="col-auto">
                            <i class="fas fa-shopping-cart fa-2x text-gray-300"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-md-6 col-lg-3 mb-4">
            <div class="card border-left-success shadow h-100 py-2 summary-card summary-card-revenue">
                <div class="card-body">
                    <div class="row no-gutters align-items-center">
                        <div class="col mr-2">
                            <div class="text-xs font-weight-bold text-success text-uppercase mb-1">
                                Doanh thu
                            </div>
                            <div class="h5 mb-0 font-weight-bold text-gray-800" id="summary-total-revenue">
                                {{ number_format($donBanLes->whereIn('trang_thai', ['hoan_thanh', 'hoan_tat'])->sum('tong_cong'), 0, ',', '.') }} đ
                            </div>
                        </div>
                        <div class="col-auto">
                            <i class="fas fa-dollar-sign fa-2x text-gray-300"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-md-6 col-lg-3 mb-4">
            <div class="card border-left-info shadow h-100 py-2 summary-card">
                <div class="card-body">
                    <div class="row no-gutters align-items-center">
                        <div class="col mr-2">
                            <div class="text-xs font-weight-bold text-info text-uppercase mb-1">
                                Đơn hoàn thành
                            </div>
                            <div class="h5 mb-0 font-weight-bold text-gray-800" id="summary-completed-orders">
                                {{ $donBanLes->whereIn('trang_thai', ['hoan_thanh', 'hoan_tat'])->count() }}
                            </div>
                        </div>
                        <div class="col-auto">
                            <i class="fas fa-check-circle fa-2x text-gray-300"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-md-6 col-lg-3 mb-4">
            <div class="card border-left-danger shadow h-100 py-2 summary-card">
                <div class="card-body">
                    <div class="row no-gutters align-items-center">
                        <div class="col mr-2">
                            <div class="text-xs font-weight-bold text-danger text-uppercase mb-1">
                                Đơn đã hủy
                            </div>
                            <div class="h5 mb-0 font-weight-bold text-gray-800" id="summary-cancelled-orders">
                                {{ $donBanLes->whereIn('trang_thai', ['da_huy', 'huy'])->count() }}
                            </div>
                        </div>
                        <div class="col-auto">
                            <i class="fas fa-ban fa-2x text-gray-300"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Orders Table Card -->
    <div class="card shadow mb-4">
        <div class="card-header py-3 d-flex justify-content-between align-items-center">
            <h6 class="m-0 font-weight-bold text-primary">Danh sách hóa đơn</h6>
        </div>
        <div class="card-body">
            <div class="table-responsive" id="orders-table-container">
                <table class="table table-bordered table-hover" id="dataTable" width="100%" cellspacing="0">
                    <thead>
                        <tr>
                            <th>STT</th>
                            <th>Mã đơn</th>
                            <th>Khách hàng</th>
                            <th>Ngày bán</th>
                            <th>Nhân viên</th>
                            <th>Tổng tiền</th>
                            <th>Trạng thái</th>
                            <th width="120">Thao tác</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($donBanLes as $index => $don)
                        <tr>
                            <td>{{ ($donBanLes->currentPage() - 1) * $donBanLes->perPage() + $index + 1 }}</td>
                            <td>
                                <strong>{{ $don->ma_don }}</strong>
                            </td>
                            <td>
                                @if($don->khachHang)
                                    <div>{{ $don->khachHang->ho_ten }}</div>
                                    <small class="text-muted">{{ $don->khachHang->sdt }}</small>
                                @else
                                    <span class="text-muted">Khách lẻ</span>
                                @endif
                            </td>
                            <td>{{ \Carbon\Carbon::parse($don->ngay_ban)->format('d/m/Y H:i') }}</td>
                            <td>{{ $don->nguoiDung->ho_ten ?? 'N/A' }}</td>
                            <td class="text-right">
                                <strong>{{ number_format($don->tong_cong, 0, ',', '.') }} đ</strong>
                            </td>
                            <td>
                                @switch($don->trang_thai)
                                    @case('hoan_tat')
                                    @case('hoan_thanh')
                                        <span class="badge badge-success">Hoàn tất</span>
                                        @break
                                    @case('cho_xu_ly')
                                        <span class="badge badge-warning">Chờ xử lý</span>
                                        @break
                                    @case('huy')
                                    @case('da_huy')
                                        <span class="badge badge-danger">Đã hủy</span>
                                        @break
                                    @default
                                        <span class="badge badge-secondary">{{ $don->trang_thai }}</span>
                                @endswitch
                            </td>
                            <td>
                                <div class="dropdown">
                                    <button class="btn btn-sm btn-outline-primary dropdown-toggle" type="button" 
                                            id="dropdownMenuButton{{ $don->don_id }}" data-bs-toggle="dropdown" 
                                            aria-expanded="false">
                                        <i class="fas fa-cog"></i> Thao tác
                                    </button>
                                    <ul class="dropdown-menu dropdown-menu-end" aria-labelledby="dropdownMenuButton{{ $don->don_id }}">
                                        <!-- Xem chi tiết - luôn hiển thị -->
                                        <li>
                                            <a class="dropdown-item view-order-btn" href="javascript:void(0)" 
                                               data-id="{{ $don->don_id }}">
                                                <i class="fas fa-eye text-info me-2"></i> Xem chi tiết
                                            </a>
                                        </li>
                                        @if(Auth::user() && Auth::user()->vai_tro === 'duoc_si')
                                            @if($don->trang_thai == 'cho_xu_ly')
                                                <!-- Hoàn tất đơn - chỉ hiển thị khi chờ xử lý -->
                                                <li>
                                                    <a class="dropdown-item complete-order-btn" href="javascript:void(0)" 
                                                       data-id="{{ $don->don_id }}" data-ma-don="{{ $don->ma_don }}">
                                                        <i class="fas fa-check-circle text-success me-2"></i> Hoàn tất đơn
                                                    </a>
                                                </li>
                                                <!-- Hủy đơn - chỉ hiển thị khi chờ xử lý -->
                                                <li>
                                                    <a class="dropdown-item cancel-order-btn" href="javascript:void(0)" 
                                                       data-id="{{ $don->don_id }}" data-ma-don="{{ $don->ma_don }}">
                                                        <i class="fas fa-ban text-danger me-2"></i> Hủy đơn
                                                    </a>
                                                </li>
                                            @endif
                                            @if(in_array($don->trang_thai, ['hoan_tat', 'hoan_thanh']))
                                                <!-- In hóa đơn - chỉ hiển thị khi đã hoàn tất -->
                                                <li><hr class="dropdown-divider"></li>
                                                <li>
                                                    <a class="dropdown-item" href="{{ route('don-ban-le.print', $don->don_id) }}" 
                                                       target="_blank">
                                                        <i class="fas fa-print text-primary me-2"></i> In hóa đơn
                                                    </a>
                                                </li>
                                            @endif
                                        @endif
                                    </ul>
                                </div>
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="8" class="text-center">Không có dữ liệu</td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>

                <!-- Pagination -->
                @if($donBanLes->hasPages())
                <div class="d-flex justify-content-between align-items-center mt-3">
                    <div>
                        Hiển thị {{ $donBanLes->firstItem() }}-{{ $donBanLes->lastItem() }} 
                        trong tổng số {{ $donBanLes->total() }} kết quả
                    </div>
                    {{ $donBanLes->links('pagination::bootstrap-4') }}
                </div>
                @endif
            </div>
        </div>
    </div>
</div>

<!-- Modals -->
@include('don-ban-le.includes._create-modal')
@include('don-ban-le.includes._detail-modal')
@endsection

@section('scripts')
<script src="{{ asset('js/don-ban-le.js') }}"></script>
<script>
    $(document).ready(function() {
        // Handle batch links in order details
        $(document).on('click', '.batch-link', function(e) {
            e.preventDefault();
            const batchId = $(this).data('id');
            // Open batch details in a new tab
            window.open(`/lo-thuoc/${batchId}`, '_blank');
        });
        
        // Filter form handling
        $('#filter-form').on('submit', function(e) {
            e.preventDefault();
            const url = '{{ route("don-ban-le.index") }}?' + $(this).serialize();
            loadOrders(url);
            window.history.pushState({}, '', url);
        });
        
        // Clear filters
        $('#clearFilters').on('click', function() {
            window.location.href = '{{ route("don-ban-le.index") }}';
        });
        
        // Initialize order details modal handler
        $(document).on('click', '.view-order-btn', function() {
            const orderId = $(this).data('id');
            viewOrderDetails(orderId);
        });
        
        // Support for pagination links
        $(document).on('click', '.pagination a', function(e) {
            e.preventDefault();
            const url = $(this).attr('href');
            loadOrders(url);
            window.history.pushState({}, '', url);
        });
        
        // Xử lý hủy đơn hàng
        $('#detail-cancel-btn').on('click', function() {
            if (confirm('Bạn có chắc chắn muốn hủy đơn hàng này không?')) {
                const orderId = $(this).data('id');
                
                // Ẩn thông báo lỗi trước đó (nếu có)
                $('#cancel-error-message').hide();
                
                $.ajax({
                    url: `/don-ban-le/${orderId}/cancel`,
                    type: 'POST',
                    data: {
                        _token: '{{ csrf_token() }}'
                    },
                    success: function(response) {
                        if (response.success) {
                            // Cập nhật lại danh sách đơn hàng
                            loadOrders(window.location.href);
                            // Đóng modal
                            $('#orderDetailModal').modal('hide');
                            // Hiển thị thông báo thành công
                            alert('Hủy đơn hàng thành công!');
                        } else {
                            $('#cancel-error-message').text(response.message).show();
                        }
                    },
                    error: function(xhr) {
                        try {
                            const response = JSON.parse(xhr.responseText);
                            $('#cancel-error-message').text(response.message || 'Có lỗi xảy ra khi hủy đơn hàng.').show();
                        } catch (e) {
                            $('#cancel-error-message').text('Có lỗi xảy ra khi hủy đơn hàng.').show();
                        }
                    }
                });
            }
        });
    });

    function loadOrders(url) {
        $.ajax({
            url: url,
            type: 'GET',
            dataType: 'json',
            success: function(response) {
                $('#orders-table-container').html(response.data);
                if (response.pagination) {
                    $('#pagination-container').html(response.pagination);
                }
                if (response.summaries) {
                    updateSummaries(response.summaries);
                }
                    // Khởi tạo lại dropdown, modal, và bind lại các event sau khi load AJAX
                    reinitBootstrapComponents();
            },
            error: function(xhr) {
                console.error('Error loading orders:', xhr.responseText);
            }
        });
    }
    
    function updateSummaries(summaries) {
        if (!summaries) return;
        
        // Update total orders
        if (summaries.totalOrders !== undefined) {
            $('#summary-total-orders').text(summaries.totalOrders);
        }
        
        // Update completed orders
        if (summaries.completedOrders !== undefined) {
            $('#summary-completed-orders').text(summaries.completedOrders);
        }
        
        // Update cancelled orders
        if (summaries.cancelledOrders !== undefined) {
            $('#summary-cancelled-orders').text(summaries.cancelledOrders);
        }
        
        // Update total revenue with formatting
        if (summaries.totalRevenue !== undefined) {
            $('#summary-total-revenue').text(formatCurrency(summaries.totalRevenue));
        }
    }
    
    function viewOrderDetails(orderId) {
        $.ajax({
            url: `/don-ban-le/${orderId}`,
            type: 'GET',
            dataType: 'json',
            success: function(response) {
                populateOrderModal(response.donBanLe);
                $('#orderDetailModal').modal('show');
            },
            error: function(xhr) {
                alert('Không thể tải thông tin đơn hàng. Vui lòng thử lại sau.');
                console.error(xhr.responseText);
            }
        });
    }
    
    function populateOrderModal(donBanLe) {
        // Điền thông tin đơn hàng
        $('#detail-ma-don').text(donBanLe.ma_don);
        
        // Định dạng ngày bán
        const ngayBan = new Date(donBanLe.ngay_ban);
        const formattedDate = ngayBan.toLocaleDateString('vi-VN', {
            day: '2-digit',
            month: '2-digit',
            year: 'numeric',
            hour: '2-digit',
            minute: '2-digit'
        });
        $('#detail-ngay-ban').text(formattedDate);
        
        // Hiển thị trạng thái
        let trangThaiText = 'Không xác định';
        let trangThaiClass = 'badge-secondary';
        
        switch (donBanLe.trang_thai) {
            case 'hoan_thanh':
            case 'hoan_tat':
                trangThaiText = 'Hoàn tất';
                trangThaiClass = 'badge-hoan-thanh';
                break;
            case 'da_huy':
            case 'huy':
                trangThaiText = 'Đã hủy';
                trangThaiClass = 'badge-da-huy';
                break;
            case 'cho_xu_ly':
                trangThaiText = 'Chờ xử lý';
                trangThaiClass = 'badge-info';
                break;
        }
        
        $('#detail-trang-thai').html(`<span class="badge ${trangThaiClass}">${trangThaiText}</span>`);
        
        // Điền thông tin khách hàng
        if (donBanLe.khach_hang) {
            $('#detail-ten-khach').text(donBanLe.khach_hang.ho_ten);
            $('#detail-sdt-khach').text(donBanLe.khach_hang.sdt);
        } else {
            $('#detail-ten-khach').text('Khách lẻ');
            $('#detail-sdt-khach').text('Không có');
        }
        
        // Điền thông tin nhân viên
        $('#detail-nhan-vien').text(donBanLe.nguoi_dung ? donBanLe.nguoi_dung.ho_ten : 'Không có thông tin');
        
        // Xóa dữ liệu cũ trong bảng sản phẩm
        $('#detail-products-table tbody').empty();
        
        // Thêm chi tiết sản phẩm vào bảng
        if (donBanLe.chi_tiet_don_ban_le && donBanLe.chi_tiet_don_ban_le.length > 0) {
            donBanLe.chi_tiet_don_ban_le.forEach((item, index) => {
                // Hiển thị đơn vị đúng
                const donViText = item.don_vi === "0" ? 
                    item.lo_thuoc.thuoc.don_vi_goc : 
                    item.lo_thuoc.thuoc.don_vi_ban;
                
                // Định dạng thông tin thuế
                const tienThue = item.tien_thue || 0;
                const thueSuat = item.thue_suat || 0;
                const thueText = `${formatCurrency(tienThue)} (${thueSuat}%)`;
                
                const row = `
                    <tr>
                        <td>${index + 1}</td>
                        <td>${item.lo_thuoc.thuoc.ten_thuoc}</td>
                        <td>${donViText}</td>
                        <td>
                            <a href="/lo-thuoc/${item.lo_thuoc.lo_id}" class="text-primary batch-link" data-id="${item.lo_thuoc.lo_id}">
                                ${item.lo_thuoc.ma_lo}
                            </a>
                        </td>
                        <td class="text-right">${formatNumber(item.so_luong)}</td>
                        <td class="text-right">${formatCurrency(item.gia_ban)}</td>
                        <td class="text-right">${thueText}</td>
                        <td class="text-right">${formatCurrency(item.thanh_tien)}</td>
                    </tr>
                `;
                $('#detail-products-table tbody').append(row);
            });
        } else {
            $('#detail-products-table tbody').append('<tr><td colspan="8" class="text-center">Không có dữ liệu sản phẩm</td></tr>');
        }
        
        // Hiển thị tổng tiền, VAT và tổng cộng
        $('#detail-tong-tien').text(formatCurrency(donBanLe.tong_tien || 0));
        $('#detail-tong-vat').text(formatCurrency(donBanLe.vat || 0));
        $('#detail-tong-cong').text(formatCurrency(donBanLe.tong_cong));
        
        // Cập nhật liên kết in đơn hàng
        $('#detail-print-btn').attr('href', `/don-ban-le/${donBanLe.don_id}/print`);
        
        // Chỉ dược sĩ mới được thao tác hủy đơn
        var isDuocSi = {{ Auth::user() && Auth::user()->vai_tro === 'duoc_si' ? 'true' : 'false' }};
        if (isDuocSi && (donBanLe.trang_thai === 'hoan_thanh' || donBanLe.trang_thai === 'hoan_tat' || donBanLe.trang_thai === 'cho_xu_ly')) {
            $('#detail-cancel-btn').show().data('id', donBanLe.don_id);
        } else {
            $('#detail-cancel-btn').hide();
        }
    }
    
    // Hàm định dạng số
    function formatNumber(value) {
        return parseFloat(value).toLocaleString('vi-VN', {
            minimumFractionDigits: 0,
            maximumFractionDigits: 2
        });
    }
    
    // Hàm định dạng tiền tệ
    function formatCurrency(value) {
        return parseFloat(value).toLocaleString('vi-VN', {
            style: 'currency',
            currency: 'VND'
        }).replace(/\s₫$/, ' đ');
    }

        // Hàm khởi tạo lại các component Bootstrap và bind lại event sau khi load AJAX
        function reinitBootstrapComponents() {
            // Nếu dùng Bootstrap 5, dropdown/modal sẽ tự động hoạt động nếu HTML đúng
            // Bind lại các event click cho các nút thao tác trong bảng
            $(document).off('click', '.view-order-btn').on('click', '.view-order-btn', function() {
                const orderId = $(this).data('id');
                viewOrderDetails(orderId);
            });

            // Bind lại event cho pagination
            $(document).off('click', '.pagination a').on('click', '.pagination a', function(e) {
                e.preventDefault();
                const url = $(this).attr('href');
                loadOrders(url);
                window.history.pushState({}, '', url);
            });

            // Bind lại event cho nút hoàn tất đơn
            $(document).off('click', '.complete-order-btn').on('click', '.complete-order-btn', function() {
                // ...nếu có logic hoàn tất đơn, bind lại ở đây...
            });

            // Bind lại event cho nút hủy đơn
            $(document).off('click', '.cancel-order-btn').on('click', '.cancel-order-btn', function() {
                // ...nếu có logic hủy đơn, bind lại ở đây...
            });
        }
</script>
@endsection
