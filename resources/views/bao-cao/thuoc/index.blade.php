@extends('layouts.app')

@section('title', 'Báo Cáo Thuốc - Hệ Thống Quản Lý Hiệu Thuốc')

@section('page-title', 'Báo Cáo Thuốc')

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
                        <label class="form-label">Loại báo cáo</label>
                        <select name="loai_bao_cao" class="form-select">
                            <option value="trang_thai" {{ request('loai_bao_cao', 'trang_thai') == 'trang_thai' ? 'selected' : '' }}>Theo trạng thái HSD</option>
                            <option value="ban_chay" {{ request('loai_bao_cao') == 'ban_chay' ? 'selected' : '' }}>Top bán chạy/bán ế</option>
                        </select>
                    </div>

                    <div class="col-md-4" id="trangThaiFilter" {{ request('loai_bao_cao') == 'ban_chay' ? 'style=display:none' : '' }}>
                        <label class="form-label">Trạng thái HSD</label>
                        <select name="trang_thai" class="form-select">
                            <option value="">-- Tất cả --</option>
                            <option value="het_han" {{ request('trang_thai') == 'het_han' ? 'selected' : '' }}>Hết hạn</option>
                            <option value="sap_het_han" {{ request('trang_thai') == 'sap_het_han' ? 'selected' : '' }}>Sắp hết hạn (< 6 tháng)</option>
                            <option value="con_han" {{ request('trang_thai') == 'con_han' ? 'selected' : '' }}>Còn hạn</option>
                        </select>
                    </div>

                    <div class="col-md-4" id="topFilter" {{ request('loai_bao_cao') != 'ban_chay' ? 'style=display:none' : '' }}>
                        <label class="form-label">Số lượng hiển thị</label>
                        <select name="limit" class="form-select">
                            <option value="5" {{ request('limit', 5) == 5 ? 'selected' : '' }}>Top 5</option>
                            <option value="10" {{ request('limit') == 10 ? 'selected' : '' }}>Top 10</option>
                            <option value="20" {{ request('limit') == 20 ? 'selected' : '' }}>Top 20</option>
                        </select>
                    </div>

                    <div class="col-md-4" id="sortFilter" {{ request('loai_bao_cao') != 'ban_chay' ? 'style=display:none' : '' }}>
                        <label class="form-label">Sắp xếp</label>
                        <select name="sort" class="form-select">
                            <option value="ban_chay" {{ request('sort', 'ban_chay') == 'ban_chay' ? 'selected' : '' }}>Bán chạy nhất</option>
                            <option value="ban_e" {{ request('sort') == 'ban_e' ? 'selected' : '' }}>Bán ế nhất</option>
                        </select>
                    </div>

                    <div class="col-md-4" id="tuNgayFilter" {{ request('loai_bao_cao') != 'ban_chay' ? 'style=display:none' : '' }}>
                        <label class="form-label">Từ ngày</label>
                        <input type="text" name="tu_ngay" class="form-control datepicker" value="{{ request('tu_ngay') }}" placeholder="dd/mm/yyyy">
                    </div>

                    <div class="col-md-4" id="denNgayFilter" {{ request('loai_bao_cao') != 'ban_chay' ? 'style=display:none' : '' }}>
                        <label class="form-label">Đến ngày</label>
                        <input type="text" name="den_ngay" class="form-control datepicker" value="{{ request('den_ngay') }}" placeholder="dd/mm/yyyy">
                    </div>

                    <div class="col-12">
                        <div class="btn-group">
                            <button type="submit" class="btn btn-primary">
                                <i class="bi bi-filter"></i> Lọc
                            </button>
                            <a href="{{ route('bao-cao.thuoc.index', ['export' => 'excel'] + request()->all()) }}" class="btn btn-success">
                                <i class="bi bi-file-excel"></i> Xuất Excel
                            </a>
                        </div>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

@if(request('loai_bao_cao', 'trang_thai') == 'trang_thai')
    @include('bao-cao.thuoc.partials._trang-thai')
@else
    @include('bao-cao.thuoc.partials._ban-chay')
@endif

@endsection

@section('scripts')
<script src="https://code.jquery.com/ui/1.13.2/jquery-ui.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>
<script>
    $(document).ready(function() {
        $('.datepicker').datepicker({
            dateFormat: 'dd/mm/yy',
            autoclose: true
        });
        
        $('select[name="loai_bao_cao"]').change(function() {
            let val = $(this).val();
            if (val == 'trang_thai') {
                $('#trangThaiFilter').show();
                $('#topFilter, #sortFilter, #tuNgayFilter, #denNgayFilter').hide();
            } else {
                $('#trangThaiFilter').hide();
                $('#topFilter, #sortFilter, #tuNgayFilter, #denNgayFilter').show();
            }
        });
        
        $('.form-select').change(function() {
            if ($(this).attr('name') != 'loai_bao_cao') {
                $('#filterForm').submit();
            }
        });
    });
</script>
@endsection