@extends('layouts.app')

@section('title', 'Báo Cáo Chi Tiết Kho')

@section('content')
<div class="container-fluid">
    <h1 class="h3 mb-4 text-gray-800">Báo Cáo Chi Tiết Kho</h1>

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

    <!-- Danh sách thuốc -->
    <div class="card">
        <div class="card-header">
            <h6 class="m-0 font-weight-bold text-primary">Danh sách thuốc trong kho</h6>
        </div>
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-bordered table-hover align-middle">
                    <thead class="table-light">
                        <tr>
                            <th>STT</th>
                            <th>Tên thuốc</th>
                            <th>Đơn vị</th>
                            <th class="text-end">Số lượng tồn</th>
                            <th class="text-end">Giá trị tồn</th>
                        </tr>
                    </thead>
                    <tbody>
                        @php
                            $tongSoLuong = 0;
                            $tongGiaTri = 0;
                        @endphp
                        @forelse($thuocs as $index => $thuoc)
                            @php
                                $tongSoLuong += $thuoc->tong_ton_kho;
                                $tongGiaTri += $thuoc->gia_tri_ton;
                            @endphp
                            <tr>
                                <td>{{ $thuocs->firstItem() + $index }}</td>
                                <td>{{ $thuoc->ten_thuoc }}</td>
                                <td>{{ $thuoc->don_vi_goc }}</td>
                                <td class="text-end">{{ number_format($thuoc->tong_ton_kho) }}</td>
                                <td class="text-end">{{ number_format($thuoc->gia_tri_ton) }} VNĐ</td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="5" class="text-center">Không có dữ liệu</td>
                            </tr>
                        @endforelse
                    </tbody>
                    <tfoot class="fw-bold">
                        <tr>
                            <td colspan="3" class="text-end">TỔNG CỘNG:</td>
                            <td class="text-end">{{ number_format($tongSoLuong) }}</td>
                            <td class="text-end">{{ number_format($tongGiaTri) }} VNĐ</td>
                        </tr>
                    </tfoot>
                </table>
            </div>
            <div class="d-flex justify-content-center">
                {{ $thuocs->links() }}
            </div>
        </div>
    </div>
</div>
@endsection
