@extends('layouts.app')

@section('title', 'Báo Cáo Doanh Số Thuốc')

@section('page-title', 'Báo Cáo Doanh Số Thuốc')

@section('content')
<div class="row mb-4">
    <div class="col-md-12">
        <div class="card">
            <div class="card-body">
                <form id="filterForm" method="GET" class="row g-3">
                    <div class="col-md-2">
                        <label class="form-label">Từ</label>
                        <input type="text" name="tu_ngay" class="form-control datepicker" value="{{ request('tu_ngay') }}" autocomplete="off" placeholder="dd/mm/yyyy">
                    </div>
                    <div class="col-md-2">
                        <label class="form-label">Đến</label>
                        <input type="text" name="den_ngay" class="form-control datepicker" value="{{ request('den_ngay') }}" autocomplete="off" placeholder="dd/mm/yyyy">
                    </div>
                    <div class="col-md-4">
                        <label class="form-label">Thuốc</label>
                        <select name="thuoc_id" class="form-select select2">
                            <option value="">-- Tất cả --</option>
                            @foreach(App\Models\Thuoc::orderBy('ten_thuoc')->get() as $thuoc)
                                <option value="{{ $thuoc->thuoc_id }}" {{ request('thuoc_id') == $thuoc->thuoc_id ? 'selected' : '' }}>{{ $thuoc->ten_thuoc }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-md-4 d-flex align-items-end justify-content-end">
                        <div class="btn-group">
                            <button type="submit" class="btn btn-primary">
                                <i class="bi bi-filter"></i> Lọc
                            </button>
                            <a href="{{ route('bao-cao.thuoc.index', ['export' => 'excel'] + request()->all()) }}" class="btn btn-success">
                                <i class="bi bi-file-excel"></i> Xuất Excel
                            </a>
                            <button type="button" class="btn btn-secondary" id="resetFilter">
                                <i class="bi bi-x-circle"></i> Reset
                            </button>
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

@section('scripts')
<link rel="stylesheet" href="https://code.jquery.com/ui/1.13.2/themes/base/jquery-ui.css">
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css">
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/select2-bootstrap-5-theme@1.3.0/dist/select2-bootstrap-5-theme.min.css">
<script src="https://code.jquery.com/ui/1.13.2/jquery-ui.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>
<script>
    $(function() {
        $('.datepicker').datepicker({
            dateFormat: 'dd/mm/yy',
            changeMonth: true,
            changeYear: true
        });
        $('.select2').select2({
            theme: 'bootstrap-5',
            width: '100%'
        });
        $('#resetFilter').click(function() {
            $('#filterForm').find('input, select').val('');
            $('#filterForm').submit();
        });

        // Handle pagination clicks
        $(document).on('click', '.pagination-link', function(e) {
            e.preventDefault();
            const page = $(this).data('page');
            
            // Get current URL parameters
            const urlParams = new URLSearchParams(window.location.search);
            
            // Update page parameter
            urlParams.set('page', page);
            
            // Redirect to new URL with updated page
            window.location.href = window.location.pathname + '?' + urlParams.toString();
        });
    });
</script>
@endsection
