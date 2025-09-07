@extends('layouts.app')

@section('title', 'Dashboard - Hệ Thống Quản Lý Hiệu Thuốc')

@section('page-title', 'Dashboard')

@section('content')
<div class="row">
    <div class="col-md-4 mb-4">
        <div class="card border-left-primary h-100 py-2">
            <div class="card-body">
                <div class="row no-gutters align-items-center">
                    <div class="col mr-2">
                        <div class="text-xs font-weight-bold text-primary text-uppercase mb-1">
                            Tổng số thuốc</div>
                        <div class="h5 mb-0 font-weight-bold text-gray-800">{{ \App\Models\Thuoc::count() }}</div>
                    </div>
                    <div class="col-auto">
                        <i class="bi bi-capsule fa-2x text-gray-300"></i>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="col-md-4 mb-4">
        <div class="card border-left-success h-100 py-2">
            <div class="card-body">
                <div class="row no-gutters align-items-center">
                    <div class="col mr-2">
                        <div class="text-xs font-weight-bold text-success text-uppercase mb-1">
                            Tổng số khách hàng</div>
                        <div class="h5 mb-0 font-weight-bold text-gray-800">{{ \App\Models\KhachHang::count() }}</div>
                    </div>
                    <div class="col-auto">
                        <i class="bi bi-people fa-2x text-gray-300"></i>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="col-md-4 mb-4">
        <div class="card border-left-info h-100 py-2">
            <div class="card-body">
                <div class="row no-gutters align-items-center">
                    <div class="col mr-2">
                        <div class="text-xs font-weight-bold text-info text-uppercase mb-1">
                            Tổng số nhà cung cấp</div>
                        <div class="h5 mb-0 font-weight-bold text-gray-800">{{ \App\Models\NhaCungCap::count() }}</div>
                    </div>
                    <div class="col-auto">
                        <i class="bi bi-building fa-2x text-gray-300"></i>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<div class="row">
    <div class="col-md-6 mb-4">
        <div class="card shadow mb-4">
            <div class="card-header py-3 d-flex flex-row align-items-center justify-content-between">
                <h6 class="m-0 font-weight-bold">Thuốc sắp hết hạn</h6>
            </div>
            <div class="card-body">
                <div class="table-responsive">
                    @php
                        $nearExpired = \App\Models\LoThuoc::with('thuoc')
                            ->whereDate('han_su_dung', '<=', now()->addMonth())
                            ->whereDate('han_su_dung', '>=', now())
                            ->where('ton_kho_hien_tai', '>', 0)
                            ->get();
                    @endphp
                    <table class="table table-sm">
                        <thead>
                            <tr>
                                <th>Tên thuốc</th>
                                <th>Mã lô</th>
                                <th>Hạn dùng</th>
                                <th>SL tồn</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($nearExpired as $lo)
                            <tr>
                                <td>{{ $lo->thuoc->ten_thuoc }}</td>
                                <td>{{ $lo->ma_lo }}</td>
                                <td>{{ date('d/m/Y', strtotime($lo->han_su_dung)) }}</td>
                                <td>{{ $lo->ton_kho_hien_tai }}</td>
                            </tr>
                            @empty
                            <tr>
                                <td colspan="4" class="text-center">Không có thuốc sắp hết hạn</td>
                            </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
    
    <div class="col-md-6 mb-4">
        <div class="card shadow mb-4">
            <div class="card-header py-3 d-flex flex-row align-items-center justify-content-between">
                <h6 class="m-0 font-weight-bold">Thuốc sắp hết hàng</h6>
            </div>
            <div class="card-body">
                <div class="table-responsive">
                    @php
                        $lowStock = \App\Models\LoThuoc::with('thuoc')
                            ->whereDate('han_su_dung', '>=', now())
                            ->where('ton_kho_hien_tai', '<=', 5)
                            ->where('ton_kho_hien_tai', '>', 0)
                            ->get();
                    @endphp
                    <table class="table table-sm">
                        <thead>
                            <tr>
                                <th>Tên thuốc</th>
                                <th>Mã lô</th>
                                <th>Hạn dùng</th>
                                <th>SL tồn</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($lowStock as $lo)
                            <tr>
                                <td>{{ $lo->thuoc->ten_thuoc }}</td>
                                <td>{{ $lo->ma_lo }}</td>
                                <td>{{ date('d/m/Y', strtotime($lo->han_su_dung)) }}</td>
                                <td>{{ $lo->ton_kho_hien_tai }}</td>
                            </tr>
                            @empty
                            <tr>
                                <td colspan="4" class="text-center">Không có thuốc sắp hết hàng</td>
                            </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
