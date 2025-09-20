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
                        <th>STT</th>
                        <th>Khách hàng</th>
                        <th>Mã đơn</th>
                        <th>Số lượng</th>
                        <th>Thành tiền</th>
                        <th>Ngày tạo đơn</th>
                    </tr>
                </thead>
<tbody>
            @php
                $tongSoLuong = $donHangs->sum('tong_so_luong');
                $tongTien = $donHangs->sum('thanh_tien_don_hang');
            @endphp
            @foreach($donHangs as $donHang)
                <tr>
                    <td>{{ $donHang->don_id }}</td>
                    <td>{{ $donHang->khachHang->ho_ten }}</td>
                    <td>{{ $donHang->ma_don }}</td>
                    <td class="text-end">{{ number_format($donHang->tong_so_luong) }}</td>
                    <td class="text-end">{{ number_format($donHang->thanh_tien_don_hang) }}</td>
                    <td>{{ \Carbon\Carbon::parse($donHang->created_at)->format('d/m/Y H:i') }}</td>
                </tr>
            @endforeach
        </tbody>
                <tfoot>
                    <tr>
                        <td colspan="3" class="text-end"><strong>TỔNG CỘNG:</strong></td>
                        <td class="text-end"><strong>{{ number_format($tongSoLuong) }}</strong></td>
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
@endsection
