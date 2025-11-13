@extends('layouts.app')

@section('title', 'BÁO CÁO TỒN KHO')

@section('content')
<div class="container-fluid">
    <h1 class="h3 mb-4 text-gray-800">BÁO CÁO TỒN KHO</h1>

    <!-- Filters -->
    <div class="card mb-4">
        <div class="card-body">
            <form method="GET" action="{{ route('bao-cao.kho.index') }}" class="row g-3">
                <div class="col-md-4">
                    <label class="form-label">Chọn kho</label>
                    <select name="kho_id" class="form-select">
                        <option value="">Tất cả kho</option>
                        @foreach($khos as $kho)
                            <option value="{{ $kho->kho_id }}" {{ request('kho_id') == $kho->kho_id ? 'selected' : '' }}>
                                {{ $kho->ten_kho }}
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

                <div class="col-12">
                    <div class="btn-group">
                        <button type="submit" class="btn btn-primary me-2">Lọc</button>
                        <a href="{{ route('bao-cao.kho.index', ['export' => 'excel'] + request()->except(['page'])) }}" class="btn btn-success">
                            <i class="bi bi-file-earmark-excel me-1"></i> Xuất Excel
                        </a>
                        <button type="button" class="btn btn-secondary" id="resetFilter">
                            <i class="bi bi-x-circle"></i> Reset
                        </button>
                    </div>
                </div>
            </form>
        </div>
    </div>

    <!-- Table Tổng hợp -->
    <div class="card mt-4">
        <div class="card-header">
            <h6 class="m-0 font-weight-bold text-primary">Bảng tổng hợp tất cả kho</h6>
        </div>
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-bordered table-hover align-middle">
                    <thead class="table-light">
                        <tr>
                            <th>STT</th>
                            <th>Tên kho</th>
                            <th class="text-end">Số lượng mặt hàng</th>
                            <th class="text-end">Tổng số lượng tồn</th>
                            <th class="text-end">Tổng giá trị tồn</th>
                            <th class="text-center">Hành động</th>
                        </tr>
                    </thead>
                    <tbody>
                        @php
                            $tongMatHang = 0;
                            $tongSoLuong = 0;
                            $tongGiaTri = 0;
                        @endphp
                        @forelse($khoList as $index => $kho)
                            @php
                                $tongMatHang += $kho->so_mat_hang;
                                $tongSoLuong += $kho->tong_ton_kho;
                                $tongGiaTri += $kho->tong_gia_tri;
                            @endphp
                            <tr>
                                <td>{{ $index + 1 }}</td>
                                <td>{{ $kho->ten_kho }}</td>
                                <td class="text-end">{{ number_format($kho->so_mat_hang) }}</td>
                                <td class="text-end">{{ ($kho->tong_ton_kho) }}</td>
                                <td class="text-end">{{ number_format($kho->tong_gia_tri) }} VNĐ</td>
                                <td class="text-center">
                                    <a href="{{ route('bao-cao.kho.index', array_merge(['kho_id' => $kho->kho_id], request()->only(['tu_ngay', 'den_ngay']))) }}" class="btn btn-sm btn-info">
                                        <i class="bi bi-eye"></i> Chi tiết
                                    </a>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="6" class="text-center">Không có dữ liệu</td>
                            </tr>
                        @endforelse
                    </tbody>
                    <tfoot class="fw-bold">
                        <tr>
                            <td colspan="2" class="text-end">TỔNG CỘNG:</td>
                            <td class="text-end">{{ number_format($tongMatHang) }}</td>
                            <td class="text-end">{{ ($tongSoLuong) }}</td>
                            <td class="text-end">{{ number_format($tongGiaTri) }} VNĐ</td>
                            <td></td>
                        </tr>
                    </tfoot>
                </table>
            </div>
            <div class="d-flex justify-content-center mt-3">
                {{ $khoList->withQueryString()->onEachSide(1)->links('vendor.pagination.custom') }}
            </div>
        </div>
    </div>
</div>
@endsection

@section('scripts')
<script>
    document.getElementById('resetFilter').onclick = function() {
        var form = this.closest('form');
        Array.from(form.querySelectorAll('input, select')).forEach(function(el) {
            if (el.type === 'select-one' || el.type === 'text' || el.type === 'date') el.value = '';
        });
        form.submit();
    };

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
</script>
@endsection
