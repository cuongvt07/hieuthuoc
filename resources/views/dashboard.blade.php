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
                <h6 class="m-0 font-weight-bold">Thuốc sắp sắp hết hạn & thuốc đã hết hạn</h6>
            </div>
            <div class="card-body">
                <div class="table-responsive">
                    @php
                        $expiredAndNearExpired = \App\Models\LoThuoc::with('thuoc')
                            ->where(function($query) {
                                $query->whereDate('han_su_dung', '<=', now()->addMonths(3))
                                    ->whereDate('han_su_dung', '>', now());
                            })
                            ->orWhere(function($query) {
                                $query->whereDate('han_su_dung', '<=', now());
                            })
                            ->where('ton_kho_hien_tai', '>', 0)
                            ->orderBy('han_su_dung', 'asc')
                            ->get();
                    @endphp
                    <table class="table table-sm">
                        <thead>
                            <tr>
                                <th>Tên thuốc</th>
                                <th>Mã lô</th>
                                <th>Hạn dùng</th>
                                <th>SL tồn</th>
                                <th>Trạng thái</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($expiredAndNearExpired as $lo)
                            <tr class="{{ strtotime($lo->han_su_dung) < time() ? 'table-danger' : 'table-warning' }}">
                                <td>{{ $lo->thuoc->ten_thuoc }}</td>
                                <td>{{ $lo->ma_lo }}</td>
                                <td>{{ date('d/m/Y', strtotime($lo->han_su_dung)) }}</td>
                                <td>{{ $lo->ton_kho_hien_tai }}</td>
                                <td>
                                    @if(strtotime($lo->han_su_dung) < time())
                                        <span class="badge bg-danger">Đã hết hạn</span>
                                    @else
                                        <span class="badge bg-warning text-dark">
                                            Còn {{ now()->diffInDays($lo->han_su_dung) }} ngày
                                        </span>
                                    @endif
                                </td>
                            </tr>
                            @empty
                            <tr>
                                <td colspan="5" class="text-center">Không có thuốc sắp hết hạn hoặc hết hạn</td>
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
                        // Lấy tổng số lượng tồn kho theo từng loại thuốc
                        $lowStock = \App\Models\LoThuoc::with('thuoc')
                            ->select('thuoc_id', 
                                \DB::raw('SUM(ton_kho_hien_tai) as total_stock'),
                                \DB::raw('MIN(han_su_dung) as earliest_expiry'))
                            ->whereDate('han_su_dung', '>=', now())
                            ->where('ton_kho_hien_tai', '>', 0)
                            ->groupBy('thuoc_id')
                            ->having('total_stock', '<=', 10)
                            ->get();
                    @endphp
                    <table class="table table-sm">
                        <thead>
                            <tr>
                                <th>Tên thuốc</th>
                                <th>Tổng tồn kho</th>
                                <th>Hạn dùng gần nhất</th>
                                <th>Trạng thái</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($lowStock as $item)
                            <tr class="{{ $item->total_stock <= 5 ? 'table-danger' : 'table-warning' }}">
                                <td>{{ $item->thuoc->ten_thuoc }}</td>
                                <td>{{ $item->total_stock }}</td>
                                <td>{{ date('d/m/Y', strtotime($item->earliest_expiry)) }}</td>
                                <td>
                                    @if($item->total_stock <= 5)
                                        <span class="badge bg-danger">Cần nhập thêm gấp</span>
                                    @else
                                        <span class="badge bg-warning text-dark">Sắp hết hàng</span>
                                    @endif
                                </td>
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
