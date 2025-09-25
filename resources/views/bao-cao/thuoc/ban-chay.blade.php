@extends('layouts.app')

@section('title', 'Báo Cáo Top Thuốc')

@section('content')
<div class="container-fluid">
    <h1 class="h3 mb-4 text-gray-800">Báo Cáo Top Thuốc Bán Chạy/Bán Ế</h1>

    <!-- Filters -->
    <div class="card mb-4">
        <div class="card-body">
            <form method="GET" action="{{ route('bao-cao.thuoc.index') }}" class="row g-3">
                <input type="hidden" name="loai_bao_cao" value="ban_chay">
                
                <div class="col-md-3">
                    <label class="form-label">Từ ngày</label>
                    <input type="text" name="tu_ngay" class="form-control datepicker" 
                           value="{{ request('tu_ngay', $startDate->format('d/m/Y')) }}" 
                           placeholder="dd/mm/yyyy">
                </div>

                <div class="col-md-3">
                    <label class="form-label">Đến ngày</label>
                    <input type="text" name="den_ngay" class="form-control datepicker" 
                           value="{{ request('den_ngay', $endDate->format('d/m/Y')) }}" 
                           placeholder="dd/mm/yyyy">
                </div>

                <div class="col-md-2">
                    <label class="form-label">Số lượng hiển thị</label>
                    <select name="limit" class="form-select">
                        <option value="5" {{ request('limit') == 5 ? 'selected' : '' }}>Top 5</option>
                        <option value="10" {{ request('limit') == 10 ? 'selected' : '' }}>Top 10</option>
                        <option value="20" {{ request('limit') == 20 ? 'selected' : '' }}>Top 20</option>
                    </select>
                </div>

                <div class="col-md-2">
                    <label class="form-label">Sắp xếp</label>
                    <select name="sort" class="form-select">
                        <option value="ban_chay" {{ request('sort') == 'ban_chay' ? 'selected' : '' }}>Bán chạy nhất</option>
                        <option value="ban_e" {{ request('sort') == 'ban_e' ? 'selected' : '' }}>Bán ế nhất</option>
                    </select>
                </div>

                <div class="col-12">
                    <button type="submit" class="btn btn-primary me-2">Lọc</button>
                    <a href="{{ route('bao-cao.thuoc.index', ['loai_bao_cao' => 'ban_chay', 'export' => 'excel'] + request()->except(['page'])) }}" 
                       class="btn btn-success">
                        <i class="bi bi-file-earmark-excel me-1"></i>Xuất Excel
                    </a>
                </div>
            </form>
        </div>
    </div>

    <!-- Results Table -->
    <div class="card">
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-bordered table-hover">
                    <thead>
                        <tr>
                            <th>STT</th>
                            <th>Mã Thuốc</th>
                            <th>Tên sản phẩm</th>
                            <th>Số đơn</th>
                            <th>Tổng số lượng</th>
                            <th>Doanh số</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($thuocs as $index => $thuoc)
                            <tr>
                                <td>{{ $index + 1 }}</td>
                                <td>{{ $thuoc->ma_thuoc }}</td>
                                <td>{{ $thuoc->ten_thuoc }}</td>
                                <td>{{ number_format($thuoc->so_don) }}</td>
                                <td>{{ number_format($thuoc->tong_so_luong) }}</td>
                                <td>{{ number_format($thuoc->doanh_so) }} đ</td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="6" class="text-center">Không có dữ liệu</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>
@endsection

@section('scripts')
<script>
    $(document).ready(function() {
        $('.datepicker').datepicker({
            format: 'dd/mm/yyyy',
            autoclose: true,
            todayHighlight: true,
            language: 'vi'
        });
    });
</script>
@endsection