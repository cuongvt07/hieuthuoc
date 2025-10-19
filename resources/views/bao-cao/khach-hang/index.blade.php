<!-- resources/views/bao-cao/khach-hang/index.blade.php -->
@extends('layouts.app')

@section('title', 'Báo Cáo Khách Hàng - Hệ Thống Quản Lý Hiệu Thuốc')

@section('page-title', 'Báo Cáo Khách Hàng')

@section('styles')
<link rel="stylesheet" href="https://code.jquery.com/ui/1.13.2/themes/base/jquery-ui.css">
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css">
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/select2-bootstrap-5-theme@1.3.0/dist/select2-bootstrap-5-theme.min.css">
<style>
    .table { width: 100%; border-collapse: collapse; }
    .table th, .table td { border: 1px solid #ddd; padding: 8px; }
    .table th { background-color: #f8f9fa; }
    .text-end { text-align: right; }
</style>
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
                                <option value="{{ $khachHang->khach_hang_id }}"
                                    {{ request('khach_hang_id') == $khachHang->khach_hang_id ? 'selected' : '' }}>
                                    {{ $khachHang->ho_ten }}
                                </option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-md-3">
                        <label class="form-label">Từ ngày</label>
                        <input type="date" name="tu_ngay" class="form-control datepicker"
                               value="{{ request('tu_ngay') }}" placeholder="dd/mm/yyyy">
                    </div>
                    <div class="col-md-3">
                        <label class="form-label">Đến ngày</label>
                        <input type="date" name="den_ngay" class="form-control datepicker"
                               value="{{ request('den_ngay') }}" placeholder="dd/mm/yyyy">
                    </div>
                    <div class="col-md-3 d-flex align-items-end">
                        <button type="submit" class="btn btn-primary me-2">Lọc</button>
                        <button type="button" id="resetFilterBtn" class="btn btn-secondary me-2">Reset</button>
                        <a href="{{ route('bao-cao.khach-hang.index', ['export' => 'excel'] + request()->all()) }}"
                           class="btn btn-success">
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
                        <th>Số điện thoại</th>
                        <th>Tổng số đơn</th>
                        <th>Tổng chi tiêu</th>
                    </tr>
                </thead>
                <tbody>
                    @php
                        $tongSoDon = $baoCaoKhachHangs->sum('so_luong_don');
                        $tongChiTieu = $baoCaoKhachHangs->sum('tong_chi_tieu');
                    @endphp
                    @forelse($baoCaoKhachHangs as $index => $baoCao)
                        <tr>
                            <td>{{ $baoCaoKhachHangs->firstItem() + $index }}</td>
                            <td>{{ $baoCao->khachHang ? $baoCao->khachHang->ho_ten : 'Khách lẻ' }}</td>
                            <td>{{ $baoCao->khachHang ? $baoCao->khachHang->sdt : 'N/A' }}</td>
                            <td class="text-end">{{ number_format($baoCao->so_luong_don, 0, ',', '.') }}</td>
                            <td class="text-end">{{ number_format($baoCao->tong_chi_tieu, 0, ',', '.') }} VNĐ</td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="5" class="text-center">Không có dữ liệu.</td>
                        </tr>
                    @endforelse
                </tbody>
                <tfoot>
                    <tr>
                        <td colspan="3" class="text-end"><strong>TỔNG CỘNG:</strong></td>
                        <td class="text-end"><strong>{{ $tongSoDon }}</strong></td>
                        <td class="text-end"><strong>{{ number_format($tongChiTieu, 0, ',', '.') }} VNĐ</strong></td>
                    </tr>
                </tfoot>
            </table>
        </div>
        {{ $baoCaoKhachHangs->withQueryString()->links() }}
    </div>
</div>
@endsection

@section('scripts')
<script src="https://code.jquery.com/ui/1.13.2/jquery-ui.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>
<script>
    $(document).ready(function() {
        $('.select2').select2({ theme: 'bootstrap-5' });
        $('#resetFilterBtn').click(function() {
            $('select[name="khach_hang_id"]').val('').trigger('change');
            $('input[name="tu_ngay"]').val('');
            $('input[name="den_ngay"]').val('');
            $('#filterForm').submit();
        });
    });
</script>
@endsection
