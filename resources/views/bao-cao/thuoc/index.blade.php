@extends('layouts.app')

@section('title', 'Báo Cáo Doanh Số Thuốc')

@section('page-title', 'Báo Cáo Doanh Số Thuốc')

@section('content')
<div class="row mb-4">
    <div class="col-md-12">
        <div class="card">
            <div class="card-body">
                <form id="filterForm" method="GET" class="row g-3">
                    <div class="col-md-4">
                        <label class="form-label">Loại báo cáo</label>
                        <select name="loai_bao_cao" class="form-select">
                            <option value="doanh_so" {{ request('loai_bao_cao', 'doanh_so') == 'doanh_so' ? 'selected' : '' }}>Báo cáo doanh số thuốc</option>
                        </select>
                    </div>

                    <div class="col-md-4">
                        <label class="form-label">Từ ngày</label>
                        <input type="text" name="tu_ngay" class="form-control datepicker" value="{{ request('tu_ngay') }}" placeholder="dd/mm/yyyy">
                    </div>

                    <div class="col-md-4">
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

@if(request('loai_bao_cao', 'doanh_so') == 'doanh_so')
    @include('bao-cao.thuoc.partials._doanh-so')
@endif
@endsection
