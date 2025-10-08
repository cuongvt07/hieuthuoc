@extends('layouts.app')

@section('title', 'Báo Cáo Tổng Hợp Kho')

@section('content')
<div class="container-fluid">
    <h1 class="h3 mb-4 text-gray-800">Báo Cáo Tổng Hợp Kho</h1>

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
                    <button type="submit" class="btn btn-primary me-2">Lọc</button>
                    <a href="{{ route('bao-cao.kho.index', ['export' => 'excel'] + request()->except(['page'])) }}" 
                       class="btn btn-success">
                        <i class="bi bi-file-earmark-excel me-1"></i> Xuất Excel
                    </a>
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
                                <td class="text-end">{{ number_format($kho->tong_ton_kho) }}</td>
                                <td class="text-end">{{ number_format($kho->tong_gia_tri) }} VNĐ</td>
                                <td class="text-center">
                                    <a href="{{ route('bao-cao.kho.index', ['kho_id' => $kho->kho_id]) }}" class="btn btn-sm btn-info">
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
                            <td class="text-end">{{ number_format($tongSoLuong) }}</td>
                            <td class="text-end">{{ number_format($tongGiaTri) }} VNĐ</td>
                            <td></td>
                        </tr>
                    </tfoot>
                </table>
            </div>
        </div>
    </div>
</div>
@endsection
