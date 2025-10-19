@extends('layouts.app')

@section('title', 'Lịch Sử Tồn Kho')

@section('content')
<div class="container-fluid">
    <h1 class="h3 mb-4 text-gray-800">Lịch Sử Tồn Kho</h1>

    <!-- Filters -->
    <div class="card mb-4">
        <div class="card-body">
            <form id="filterForm" method="GET" action="{{ route('lich-su-ton-kho.index') }}" class="row g-3">
                <div class="col-md-2">
                    <label class="form-label">Thuốc</label>
                    <select name="thuoc_id" class="form-select">
                        <option value="">-- Tất cả --</option>
                        @foreach($thuocs as $thuoc)
                            <option value="{{ $thuoc->thuoc_id }}" {{ request('thuoc_id') == $thuoc->thuoc_id ? 'selected' : '' }}>
                                {{ $thuoc->ten_thuoc }}
                            </option>
                        @endforeach
                    </select>
                </div>

                <div class="col-md-2">
                    <label class="form-label">Loại thay đổi</label>
                    <select name="loai_thay_doi" class="form-select">
                        <option value="">-- Tất cả --</option>
                        <option value="nhap" {{ request('loai_thay_doi') == 'nhap' ? 'selected' : '' }}>Nhập hàng</option>
                        <option value="ban" {{ request('loai_thay_doi') == 'ban' ? 'selected' : '' }}>Bán hàng</option>
                    </select>
                </div>
                <div class="col-md-2 mb-3">
                    <label for="tu_ngay">Từ ngày:</label>
                    <input type="date" class="form-control" id="tu_ngay" name="tu_ngay" 
                        value="{{ request('tu_ngay') }}">
                </div>
                <div class="col-md-2 mb-3">
                    <label for="den_ngay">Đến ngày:</label>
                    <input type="date" class="form-control" id="den_ngay" name="den_ngay" 
                        value="{{ request('den_ngay') }}">
                </div>
                <div class="col-md-4 d-flex align-items-end">
                    <button type="submit" class="btn btn-primary me-2">
                        <i class="bi bi-search me-1"></i> Tìm kiếm
                    </button>
                    <button type="button" id="resetFilterBtn" class="btn btn-secondary">
                        <i class="bi bi-arrow-counterclockwise me-1"></i> Reset
                    </button>
                </div>
            </form>
        </div>
    </div>

    <!-- Danh sách lịch sử -->
    <div class="card">
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-hover align-middle">
                    <thead>
                        <tr>
                            <th>Thời gian</th>
                            <th>Thuốc</th>
                            <th>Lô</th>
                            <th>Loại</th>
                            <th class="text-end">Số lượng thay đổi</th>
                            <th class="text-end">Tồn kho mới</th>
                            <th>Người thực hiện</th>
                            <th>Mô tả</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($lichSu as $ls)
                            <tr>
                                <td>{{ \Carbon\Carbon::parse($ls->created_at)->format('d/m/Y H:i') }}</td>
                                <td>{{ $ls->loThuoc->thuoc->ten_thuoc }}</td>
                                <td>{{ $ls->loThuoc->ma_lo }}</td>
                                <td>
                                    @if($ls->loai_thay_doi == 'nhap')
                                        <span class="badge bg-success">Nhập hàng</span>
                                    @else
                                        <span class="badge bg-info">Bán hàng</span>
                                    @endif
                                </td>
                                <td class="text-end">
                                    @if($ls->so_luong_thay_doi > 0)
                                        <span class="text-success">
                                            <i class="bi bi-arrow-up-circle-fill"></i>
                                            +{{ number_format($ls->so_luong_thay_doi, 2) }}
                                        </span>
                                    @else
                                        <span class="text-danger">
                                            <i class="bi bi-arrow-down-circle-fill"></i>
                                            {{ number_format($ls->so_luong_thay_doi, 2) }}
                                        </span>
                                    @endif
                                </td>
                                <td class="text-end">{{ number_format($ls->ton_kho_moi, 2) }}</td>
                                <td>{{ $ls->nguoiDung->ho_ten }}</td>
                                <td>{{ $ls->mo_ta }}</td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="8" class="text-center">Không có dữ liệu</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            <div class="d-flex justify-content-center mt-3">
                {{ $lichSu->links() }}
            </div>
        </div>
    </div>
</div>
@endsection

@section('scripts')
<script src="https://code.jquery.com/ui/1.13.2/jquery-ui.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>
<script>
    $(document).ready(function() {
        // Initialize datepicker
        $('.datepicker').datepicker({
            format: 'dd/mm/yyyy',
            autoclose: true,
            todayHighlight: true,
            language: 'vi'
        });

        // Reset filter button
        $('#resetFilterBtn').click(function() {
            $('select[name="thuoc_id"]').val('').trigger('change');
            $('select[name="loai_thay_doi"]').val('');
            $('input[name="tu_ngay"]').val('');
            $('input[name="den_ngay"]').val('');
            $('#filterForm').submit();
        });
    });
</script>
@endsection
