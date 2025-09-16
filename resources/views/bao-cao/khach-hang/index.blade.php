@extends('layouts.app')

@section('title', 'Báo Cáo Khách Hàng - Hệ Thống Quản Lý Hiệu Thuốc')

@section('page-title', 'Báo Cáo Khách Hàng')

@section('styles')
<link rel="stylesheet" href="https://code.jquery.com/ui/1.13.2/themes/base/jquery-ui.css">
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css">
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/select2-bootstrap-5-theme@1.3.0/dist/select2-bootstrap-5-theme.min.css">
@endsection

@section('content')
<div class="row mb-4">
    <div class="col-md-12">
        <div class="card">
            <div class="card-body">
                <form id="filterForm" method="GET" class="row g-3">
                    <div class="col-md-3">
                        <label class="form-label">Khách hàng</label>
                        <select name="khach_hang_id" class="form-select select2">
                            <option value="">-- Tất cả --</option>
                            @foreach($khachHangs as $khachHang)
                                <option value="{{ $khachHang->khach_hang_id }}" {{ request('khach_hang_id') == $khachHang->khach_hang_id ? 'selected' : '' }}>
                                    {{ $khachHang->ho_ten }}
                                </option>
                            @endforeach
                        </select>
                    </div>
                    
                    <div class="col-md-3">
                        <label class="form-label">Từ ngày</label>
                        <input type="date" name="tu_ngay" class="form-control datepicker" value="{{ request('tu_ngay') }}" placeholder="dd/mm/yyyy">
                    </div>
                    
                    <div class="col-md-3">
                        <label class="form-label">Đến ngày</label>
                        <input type="date" name="den_ngay" class="form-control datepicker" value="{{ request('den_ngay') }}" placeholder="dd/mm/yyyy">
                    </div>
                    
                    <div class="col-md-3 d-flex align-items-end">
                        <button type="submit" class="btn btn-primary me-2">Lọc</button>
                        <a href="{{ route('bao-cao.khach-hang.index', ['export' => 'excel'] + request()->all()) }}" class="btn btn-success">
                            <i class="bi bi-file-earmark-excel me-1"></i> Xuất Excel
                        </a>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

<div class="card">
    <div class="card-body">
        <div class="table-responsive">
            <table class="table table-bordered table-hover">
                <thead>
                    <tr>
                        <th>Ngày mua</th>
                        <th>Khách hàng</th>
                        <th>Sản phẩm</th>
                        <th>Số lượng</th>
                        <th>Đơn giá</th>
                        <th>Thành tiền</th>
                    </tr>
                </thead>
                <tbody>
                    @php
                        $tongSoLuong = 0;
                        $tongTien = 0;
                    @endphp
                    @foreach($donHangs as $donHang)
                        @foreach($donHang->chiTietDonBanLe as $chiTiet)
                            @php
                                $tongSoLuong += $chiTiet->so_luong;
                                $tongTien += $chiTiet->thanh_tien;
                            @endphp
                            <tr>
                                <td>{{ \Carbon\Carbon::parse($donHang->created_at)->format('d/m/Y H:i') }}</td>
                                <td>{{ $donHang->khachHang->ho_ten }}</td>
                                <td>{{ $chiTiet->loThuoc->thuoc->ten_thuoc }}</td>
                                <td class="text-end">{{ number_format($chiTiet->so_luong) }}</td>
                                <td class="text-end">{{ number_format($chiTiet->don_gia) }}</td>
                                <td class="text-end">{{ number_format($chiTiet->thanh_tien) }}</td>
                            </tr>
                        @endforeach
                    @endforeach
                </tbody>
                <tfoot>
                    <tr>
                        <td colspan="3" class="text-end"><strong>TỔNG CỘNG:</strong></td>
                        <td class="text-end"><strong>{{ number_format($tongSoLuong) }}</strong></td>
                        <td></td>
                        <td class="text-end"><strong>{{ number_format($tongTien) }}</strong></td>
                    </tr>
                </tfoot>
            </table>
        </div>
        
        {{ $donHangs->withQueryString()->links() }}
    </div>
</div>
@endsection

@section('scripts')
<script src="https://code.jquery.com/ui/1.13.2/jquery-ui.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>
<script>
    $(document).ready(function() {
        $('.datepicker').datepicker({
            dateFormat: 'dd/mm/yy',
            dayNamesMin: ['CN', 'T2', 'T3', 'T4', 'T5', 'T6', 'T7'],
            monthNames: ['Tháng 1', 'Tháng 2', 'Tháng 3', 'Tháng 4', 'Tháng 5', 'Tháng 6', 'Tháng 7', 'Tháng 8', 'Tháng 9', 'Tháng 10', 'Tháng 11', 'Tháng 12'],
        });
        
        $('.select2').select2({
            theme: 'bootstrap-5'
        });
    });
</script>
@endsection
