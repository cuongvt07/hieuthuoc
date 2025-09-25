@extends('layouts.app')

@section('title', 'Báo Cáo Lô Thuốc - Hệ Thống Quản Lý Hiệu Thuốc')

@section('page-title', 'Báo Cáo Lô Thuốc')

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
                    <div class="col-md-4">
                        <label class="form-label">Trạng thái</label>
                        <select name="trang_thai" class="form-select">
                            <option value="">-- Tất cả --</option>
                            <option value="con_han" {{ request('trang_thai') == 'con_han' ? 'selected' : '' }}>Còn hạn</option>
                            <option value="sap_het_han" {{ request('trang_thai') == 'sap_het_han' ? 'selected' : '' }}>Sắp hết hạn (<  tháng)</option>
                            <option value="het_han" {{ request('trang_thai') == 'het_han' ? 'selected' : '' }}>Hết hạn</option>
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

                    <div class="col-12">
                        <div class="btn-group">
                            <button type="submit" class="btn btn-primary">
                                <i class="bi bi-filter"></i> Lọc
                            </button>
                            <a href="{{ route('bao-cao.lo-thuoc.index', ['export' => 'excel'] + request()->all()) }}" class="btn btn-success">
                                <i class="bi bi-file-excel"></i> Xuất Excel
                            </a>
                        </div>
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
                        <th>Mã lô</th>
                        <th>Tên sản phẩm</th>
                        <th>Kho</th>
                        <th>Số lượng tồn</th>
                        <th>Hạn sử dụng</th>
                        <th>Trạng thái</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($loThuocs as $lo)
                        <tr>
                            <td>{{ $lo->ma_lo }}</td>
                            <td>{{ $lo->thuoc->ten_thuoc }}</td>
                            <td>{{ $lo->kho->ten_kho }}</td>
                            <td class="text-end">{{ number_format($lo->ton_kho_hien_tai) }} / {{ $lo->thuoc->don_vi_goc}}</td>
                            <td>{{ \Carbon\Carbon::parse($lo->han_su_dung)->format('d/m/Y') }}</td>
                            <td>
                                @php
                                    $now = \Carbon\Carbon::now();
                                    $hsd = \Carbon\Carbon::parse($lo->han_su_dung);
                                    $monthsDiff = $now->diffInMonths($hsd, false);
                                @endphp
                                
                                @if($now > $hsd)
                                    <span class="badge bg-danger">Hết hạn</span>
                                @elseif($monthsDiff <= 6)
                                    <span class="badge bg-warning">Sắp hết hạn</span>
                                @else
                                    <span class="badge bg-success">Còn hạn</span>
                                @endif
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
        
        {{ $loThuocs->withQueryString()->links() }}
    </div>
</div>
@endsection

@section('scripts')
<script src="https://code.jquery.com/ui/1.13.2/jquery-ui.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>
<script>
    $(document).ready(function() {
        $('.select2').select2({
            theme: 'bootstrap-5'
        });
        
        $('.form-select').change(function() {
            $('#filterForm').submit();
        });
    });
</script>
@endsection