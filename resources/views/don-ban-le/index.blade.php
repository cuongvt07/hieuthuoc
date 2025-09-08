@extends('layouts.app')

@section('title', 'Quản lý đơn bán lẻ')

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
</style>
@endsection

@section('content')
<div class="container-fluid">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h1 class="h3 mb-0 text-gray-800">Quản lý đơn bán lẻ</h1>
        <button type="button" class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#createOrderModal">
            <i class="fas fa-plus"></i> Tạo đơn mới
        </button>
    </div>

    <!-- Filter Card -->
    <div class="card shadow mb-4">
        <div class="card-header py-3 d-flex justify-content-between align-items-center">
            <h6 class="m-0 font-weight-bold text-primary">Bộ lọc</h6>
            <button class="btn btn-sm btn-outline-secondary" id="clearFilters">
                <i class="fas fa-undo"></i> Đặt lại
            </button>
        </div>
        <div class="card-body">
            <form id="filter-form" method="GET">
                <div class="row">
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
                            <option value="hoan_thanh" {{ request('status') == 'hoan_thanh' ? 'selected' : '' }}>
                                Hoàn thành
                            </option>
                            <option value="da_huy" {{ request('status') == 'da_huy' ? 'selected' : '' }}>
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
                    <div class="col-md-1 mb-3 d-flex align-items-end">
                        <button type="submit" class="btn btn-primary w-100">
                            <i class="fas fa-search"></i> Lọc
                        </button>
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
                                {{ number_format($donBanLes->where('trang_thai', 'hoan_thanh')->sum('tong_cong'), 0, ',', '.') }} đ
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
                                {{ $donBanLes->where('trang_thai', 'hoan_thanh')->count() }}
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
                                {{ $donBanLes->where('trang_thai', 'da_huy')->count() }}
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
            <h6 class="m-0 font-weight-bold text-primary">Danh sách đơn bán lẻ</h6>
            <div>
                <a href="{{ route('don-ban-le.report') }}" class="btn btn-sm btn-info">
                    <i class="fas fa-chart-line"></i> Báo cáo doanh số
                </a>
            </div>
        </div>
        <div class="card-body">
            <div class="table-responsive" id="orders-table-container">
                @include('don-ban-le.partials._list')
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
        // Filter form handling
        $('#filter-form').on('submit', function(e) {
            e.preventDefault();
            const url = '{{ route("don-ban-le.index") }}?' + $(this).serialize();
            loadOrders(url);
            window.history.pushState({}, '', url);
        });
        
        // Clear filters
        $('#clearFilters').on('click', function() {
            $('#keyword, #from_date, #to_date').val('');
            $('#status, #staff').val('');
            $('#filter-form').submit();
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
    });

    function loadOrders(url) {
        $.ajax({
            url: url,
            type: 'GET',
            dataType: 'json',
            success: function(response) {
                $('#orders-table-container').html(response.data);
                updateSummaries();
            },
            error: function(xhr) {
                console.error('Error loading orders:', xhr.responseText);
            }
        });
    }
    
    function updateSummaries() {
        // This would be updated via the AJAX response in a real implementation
        // For now, we'll just use the initial counts
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
</script>
@endsection
