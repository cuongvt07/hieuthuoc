@extends('layouts.app')

@section('title', 'Báo Cáo Thuốc Theo Trạng Thái')

@section('content')
<div class="container-fluid">
    <h1 class="h3 mb-4 text-gray-800">Báo Cáo Thuốc Theo Trạng Thái HSD</h1>

    <!-- Filters -->
    <div class="card mb-4">
        <div class="card-body">
            <form method="GET" action="{{ route('bao-cao.thuoc.index') }}" class="row g-3">
                <input type="hidden" name="loai_bao_cao" value="trang_thai">
                
                <div class="col-md-4">
                    <label class="form-label">Trạng thái HSD</label>
                    <select name="trang_thai" class="form-select">
                        <option value="">Tất cả</option>
                        <option value="het_han" {{ request('trang_thai') == 'het_han' ? 'selected' : '' }}>Hết hạn</option>
                        <option value="sap_het_han" {{ request('trang_thai') == 'sap_het_han' ? 'selected' : '' }}>Sắp hết hạn</option>
                        <option value="con_han" {{ request('trang_thai') == 'con_han' ? 'selected' : '' }}>Còn hạn</option>
                    </select>
                </div>

                <div class="col-12">
                    <button type="submit" class="btn btn-primary me-2">Lọc</button>
                    <a href="{{ route('bao-cao.thuoc.index', ['loai_bao_cao' => 'trang_thai', 'export' => 'excel'] + request()->except(['page'])) }}" 
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
                            <th>Tên Thuốc</th>
                            <th>Số lượng hết hạn</th>
                            <th>Số lượng sắp hết hạn</th>
                            <th>Số lượng còn hạn</th>
                            <th>Tổng tồn kho</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($thuocs as $index => $thuoc)
                            <tr>
                                <td>{{ $thuocs->firstItem() + $index }}</td>
                                <td>{{ $thuoc->ma_thuoc }}</td>
                                <td>{{ $thuoc->ten_thuoc }}</td>
                                <td class="text-danger">{{ number_format($thuoc->sl_het_han) }}</td>
                                <td class="text-warning">{{ number_format($thuoc->sl_sap_het_han) }}</td>
                                <td class="text-success">{{ number_format($thuoc->sl_con_han) }}</td>
                                <td>{{ number_format($thuoc->tong_ton_kho) }}</td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="7" class="text-center">Không có dữ liệu</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
            {{ $thuocs->withQueryString()->links() }}
        </div>
    </div>
</div>
@endsection