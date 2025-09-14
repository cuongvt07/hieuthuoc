@extends('layouts.app')

@section('title', 'Dashboard - Hệ Thống Quản Lý Hiệu Thuốc')

@section('page-title', 'Dashboard')

@section('content')
<div class="row">
    <!-- Tổng số thuốc -->
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

    <!-- Tổng số khách hàng -->
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

    <!-- Tổng số nhà cung cấp -->
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
    <!-- Top khách hàng mua nhiều nhất -->
    <div class="col-md-6 mb-4">
        <div class="card border-left-primary h-100 py-2">
            <div class="card-header py-3 d-flex flex-row align-items-center justify-content-between">
                <h6 class="m-0 font-weight-bold">Top khách hàng mua nhiều nhất</h6>
            </div>
            <div class="card-body">
                <form method="GET" class="mb-3" id="customer-top-filter-form">
                    <div class="row g-2 align-items-end">
                        <div class="col-auto">
                            <label for="customer_month" class="form-label mb-0">Tháng</label>
                            <select class="form-select form-select-sm" name="customer_month" id="customer_month">
                                @for($m = 1; $m <= 12; $m++)
                                    <option value="{{ $m }}" {{ request('customer_month', now()->month) == $m ? 'selected' : '' }}>Tháng {{ $m }}</option>
                                @endfor
                            </select>
                        </div>
                        <div class="col-auto">
                            <label for="customer_year" class="form-label mb-0">Năm</label>
                            <select class="form-select form-select-sm" name="customer_year" id="customer_year">
                                @for($y = now()->year; $y >= now()->year - 5; $y--)
                                    <option value="{{ $y }}" {{ request('customer_year', now()->year) == $y ? 'selected' : '' }}>{{ $y }}</option>
                                @endfor
                            </select>
                        </div>
                        <div class="col-auto">
                            <label for="customer_top" class="form-label mb-0">Top</label>
                            <select class="form-select form-select-sm" name="customer_top" id="customer_top">
                                <option value="3" {{ request('customer_top', 3) == 3 ? 'selected' : '' }}>3</option>
                                <option value="5" {{ request('customer_top', 3) == 5 ? 'selected' : '' }}>5</option>
                                <option value="10" {{ request('customer_top', 3) == 10 ? 'selected' : '' }}>10</option>
                            </select>
                        </div>
                        <div class="col-auto">
                            <button type="submit" class="btn btn-sm btn-primary">Lọc</button>
                        </div>
                    </div>
                </form>
                @php
                    $cMonth = request('customer_month', now()->month);
                    $cYear = request('customer_year', now()->year);
                    $cTop = request('customer_top', 3);
                    $cStartDate = now()->setDate($cYear, $cMonth, 1)->startOfMonth()->format('Y-m-d');
                    $cEndDate = now()->setDate($cYear, $cMonth, 1)->endOfMonth()->format('Y-m-d');
                    $topCustomers = \App\Models\DonBanLe::select('khach_hang_id', \DB::raw('SUM(tong_tien) as total_spent'))
                        ->whereDate('ngay_ban', '>=', $cStartDate)
                        ->whereDate('ngay_ban', '<=', $cEndDate)
                        ->groupBy('khach_hang_id')
                        ->orderByDesc('total_spent')
                        ->take($cTop)
                        ->get();
                @endphp
                <table class="table table-sm">
                    <thead>
                        <tr>
                            <th>Tên khách hàng</th>
                            <th>Tổng tiền mua</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($topCustomers as $item)
                        <tr>
                            <td>{{ optional(\App\Models\KhachHang::find($item->khach_hang_id))->ho_ten}}</td>
                            <td>{{ number_format($item->total_spent, 0, ',', '.') }} VNĐ</td>
                        </tr>
                        @empty
                        <tr><td colspan="2" class="text-center">Không có dữ liệu</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <!-- Tổng nhập hàng -->
    <div class="col-md-6 mb-4">
        <div class="card border-left-success h-100 py-2">
            <div class="card-header py-3 d-flex flex-row align-items-center justify-content-between">
                <h6 class="m-0 font-weight-bold">Tổng nhập hàng</h6>
            </div>
            <div class="card-body">
                <form method="GET" class="mb-3" id="purchase-filter-form">
                    <div class="row g-2 align-items-end">
                        <div class="col-auto">
                            <label for="purchase_from" class="form-label mb-0">Từ ngày</label>
                            <input type="date" class="form-control form-control-sm" name="purchase_from" id="purchase_from" value="{{ request('purchase_from', now()->startOfMonth()->format('Y-m-d')) }}">
                        </div>
                        <div class="col-auto">
                            <label for="purchase_to" class="form-label mb-0">Đến ngày</label>
                            <input type="date" class="form-control form-control-sm" name="purchase_to" id="purchase_to" value="{{ request('purchase_to', now()->format('Y-m-d')) }}">
                        </div>
                        <div class="col-auto">
                            <button type="submit" class="btn btn-sm btn-primary">Lọc</button>
                        </div>
                    </div>
                </form>
                @php
                    $purchaseFrom = request('purchase_from', now()->startOfMonth()->format('Y-m-d'));
                    $purchaseTo = request('purchase_to', now()->format('Y-m-d'));
                    $totalPurchase = \App\Models\PhieuNhap::whereDate('ngay_nhap', '>=', $purchaseFrom)
                        ->whereDate('ngay_nhap', '<=', $purchaseTo)
                        ->sum('tong_tien');
                @endphp
                <div class="h5 mb-0 font-weight-bold text-gray-800">{{ number_format($totalPurchase, 0, ',', '.') }} VNĐ</div>
                <div class="text-xs text-muted mt-2">Tổng nhập hàng từ <b>{{ date('d/m/Y', strtotime($purchaseFrom)) }}</b> đến <b>{{ date('d/m/Y', strtotime($purchaseTo)) }}</b></div>
            </div>
        </div>
    </div>
</div>

<div class="row">
    <!-- Doanh số bán -->
    <div class="col-md-6 mb-4">
        <div class="card border-left-warning h-100 py-2">
            <div class="card-header py-3 d-flex flex-row align-items-center justify-content-between">
                <h6 class="m-0 font-weight-bold">Doanh số bán</h6>
            </div>
            <div class="card-body">
                <form method="GET" class="mb-3" id="sales-filter-form">
                    <div class="row g-2 align-items-end">
                        <div class="col-auto">
                            <label for="sales_from" class="form-label mb-0">Từ ngày</label>
                            <input type="date" class="form-control form-control-sm" name="sales_from" id="sales_from" value="{{ request('sales_from', now()->startOfMonth()->format('Y-m-d')) }}">
                        </div>
                        <div class="col-auto">
                            <label for="sales_to" class="form-label mb-0">Đến ngày</label>
                            <input type="date" class="form-control form-control-sm" name="sales_to" id="sales_to" value="{{ request('sales_to', now()->format('Y-m-d')) }}">
                        </div>
                        <div class="col-auto">
                            <button type="submit" class="btn btn-sm btn-primary">Lọc</button>
                        </div>
                    </div>
                </form>
                @php
                    $salesFrom = request('sales_from', now()->startOfMonth()->format('Y-m-d'));
                    $salesTo = request('sales_to', now()->format('Y-m-d'));
                    $totalSales = \App\Models\DonBanLe::whereDate('ngay_ban', '>=', $salesFrom)
                        ->whereDate('ngay_ban', '<=', $salesTo)
                        ->sum('tong_tien');
                @endphp
                <div class="h5 mb-0 font-weight-bold text-gray-800">{{ number_format($totalSales, 0, ',', '.') }} VNĐ</div>
                <div class="text-xs text-muted mt-2">Tổng doanh số bán từ <b>{{ date('d/m/Y', strtotime($salesFrom)) }}</b> đến <b>{{ date('d/m/Y', strtotime($salesTo)) }}</b></div>
            </div>
        </div>
    </div>
</div>

<div class="row">
    <!-- Top sản phẩm bán chạy / bán ế -->
    <div class="col-md-6 mb-4">
        <div class="card shadow mb-4">
            <div class="card-header py-3 d-flex flex-row align-items-center justify-content-between">
                <h6 class="m-0 font-weight-bold">Top sản phẩm bán chạy / bán ế</h6>
            </div>
            <div class="card-body">
                <form method="GET" class="mb-3" id="product-sales-filter-form">
                    <div class="row g-2 align-items-end">
                        <div class="col-auto">
                            <label for="product_month" class="form-label mb-0">Tháng</label>
                            <select class="form-select form-select-sm" name="product_month" id="product_month">
                                @for($m = 1; $m <= 12; $m++)
                                    <option value="{{ $m }}" {{ request('product_month', now()->month) == $m ? 'selected' : '' }}>Tháng {{ $m }}</option>
                                @endfor
                            </select>
                        </div>
                        <div class="col-auto">
                            <label for="product_year" class="form-label mb-0">Năm</label>
                            <select class="form-select form-select-sm" name="product_year" id="product_year">
                                @for($y = now()->year; $y >= now()->year - 5; $y--)
                                    <option value="{{ $y }}" {{ request('product_year', now()->year) == $y ? 'selected' : '' }}>{{ $y }}</option>
                                @endfor
                            </select>
                        </div>
                        <div class="col-auto">
                            <label for="product_top" class="form-label mb-0">Top</label>
                            <select class="form-select form-select-sm" name="product_top" id="product_top">
                                <option value="3" {{ request('product_top', 3) == 3 ? 'selected' : '' }}>3</option>
                                <option value="5" {{ request('product_top', 3) == 5 ? 'selected' : '' }}>5</option>
                                <option value="10" {{ request('product_top', 3) == 10 ? 'selected' : '' }}>10</option>
                            </select>
                        </div>
                        <div class="col-auto">
                            <button type="submit" class="btn btn-sm btn-primary">Lọc</button>
                        </div>
                    </div>
                </form>
                @php
                    $month = request('product_month', now()->month);
                    $year = request('product_year', now()->year);
                    $top = request('product_top', 3);

                    $startDate = now()->setDate($year, $month, 1)->startOfMonth()->toDateString();
                    $endDate = now()->setDate($year, $month, 1)->endOfMonth()->toDateString();

                    // Top bán chạy
                    $topSelling = \App\Models\ChiTietDonBanLe::select('lo_thuoc.thuoc_id', \DB::raw('SUM(so_luong) as total_sold'))
                        ->whereHas('donBanLe', function($q) use ($startDate, $endDate) {
                            $q->whereBetween('ngay_ban', [$startDate, $endDate]);
                        })
                        ->join('lo_thuoc', 'chi_tiet_don_ban_le.lo_id', '=', 'lo_thuoc.lo_id')
                        ->groupBy('lo_thuoc.thuoc_id')
                        ->orderByDesc('total_sold')
                        ->take($top)
                        ->with('loThuoc.thuoc') // eager load để lấy thông tin thuốc
                        ->get();

                    // Top bán ế
                    $leastSelling = \App\Models\ChiTietDonBanLe::select('lo_thuoc.thuoc_id', \DB::raw('SUM(so_luong) as total_sold'))
                        ->whereHas('donBanLe', function($q) use ($startDate, $endDate) {
                            $q->whereBetween('ngay_ban', [$startDate, $endDate]);
                        })
                        ->join('lo_thuoc', 'chi_tiet_don_ban_le.lo_id', '=', 'lo_thuoc.lo_id')
                        ->groupBy('lo_thuoc.thuoc_id')
                        ->orderBy('total_sold', 'asc')
                        ->take($top)
                        ->with('loThuoc.thuoc')
                        ->get();
                @endphp

                <div class="row">
                    <div class="col-md-6">
                        <h6 class="text-success">Bán chạy nhất</h6>
                        <table class="table table-sm">
                            <thead>
                                <tr>
                                    <th>Tên thuốc</th>
                                    <th>Số lượng bán</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($topSelling as $item)
                                <tr>
                                    <td>{{ optional(\App\Models\Thuoc::find($item->thuoc_id))->ten_thuoc }}</td>
                                    <td>{{ $item->total_sold }}</td>
                                </tr>
                                @empty
                                <tr><td colspan="2" class="text-center">Không có dữ liệu</td></tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                    <div class="col-md-6">
                        <h6 class="text-danger">Bán ế nhất</h6>
                        <table class="table table-sm">
                            <thead>
                                <tr>
                                    <th>Tên thuốc</th>
                                    <th>Số lượng bán</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($leastSelling as $item)
                                <tr>
                                    <td>{{ optional(\App\Models\Thuoc::find($item->thuoc_id))->ten_thuoc }}</td>
                                    <td>{{ $item->total_sold }}</td>
                                </tr>
                                @empty
                                <tr><td colspan="2" class="text-center">Không có dữ liệu</td></tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
    
    <div class="col-md-6 mb-4">
        <div class="card shadow mb-4">
            <div class="card-header py-3 d-flex flex-row align-items-center justify-content-between">
                <h6 class="m-0 font-weight-bold">Cảnh báo tồn kho & hạn dùng</h6>
            </div>
            <div class="card-body">
                <div class="table-responsive">
                    @php
                    use Illuminate\Support\Facades\DB;
                    use App\Models\LoThuoc;

                    $today = now()->toDateString();
                    $next30days = now()->addDays(30)->toDateString();

                    // Thuốc sắp hết hàng
                    $lowStock = LoThuoc::with('thuoc')
                        ->select('thuoc_id',
                            DB::raw('SUM(ton_kho_hien_tai) as total_stock'),
                            DB::raw('MIN(han_su_dung) as earliest_expiry'))
                        ->where('ton_kho_hien_tai', '>', 0)
                        ->groupBy('thuoc_id')
                        ->having('total_stock', '<=', 10)
                        ->get()
                        ->map(function($item) {
                            $item->status = $item->total_stock <= 5 ? 'critical_stock' : 'low_stock';
                            return $item;
                        });

                    // Thuốc sắp hết hạn (≤ 30 ngày nữa)
                    $nearlyExpired = LoThuoc::with('thuoc')
                        ->select('thuoc_id',
                            DB::raw('SUM(ton_kho_hien_tai) as total_stock'),
                            DB::raw('MIN(han_su_dung) as earliest_expiry'))
                        ->where('ton_kho_hien_tai', '>', 0)
                        ->whereBetween('han_su_dung', [$today, $next30days])
                        ->groupBy('thuoc_id')
                        ->get()
                        ->map(function($item) {
                            $item->status = 'nearly_expired';
                            return $item;
                        });

                    // Thuốc đã hết hạn
                    $expired = LoThuoc::with('thuoc')
                        ->select('thuoc_id',
                            DB::raw('SUM(ton_kho_hien_tai) as total_stock'),
                            DB::raw('MIN(han_su_dung) as earliest_expiry'))
                        ->where('ton_kho_hien_tai', '>', 0)
                        ->whereDate('han_su_dung', '<', $today)
                        ->groupBy('thuoc_id')
                        ->get()
                        ->map(function($item) {
                            $item->status = 'expired';
                            return $item;
                        });

                    // Gộp tất cả
                    $alerts = $lowStock->merge($nearlyExpired)->merge($expired);
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
                            @forelse($alerts as $item)
                                @php
                                    $rowClass = '';
                                    $badge = '';
                                    switch ($item->status) {
                                        case 'critical_stock':
                                            $rowClass = 'table-danger';
                                            $badge = '<span class="badge bg-danger">Cần nhập thêm gấp</span>';
                                            break;
                                        case 'low_stock':
                                            $rowClass = 'table-warning';
                                            $badge = '<span class="badge bg-warning text-dark">Sắp hết hàng</span>';
                                            break;
                                        case 'nearly_expired':
                                            $rowClass = 'table-info';
                                            $badge = '<span class="badge bg-info text-dark">Sắp hết hạn</span>';
                                            break;
                                        case 'expired':
                                            $rowClass = 'table-secondary';
                                            $badge = '<span class="badge bg-secondary">Đã hết hạn</span>';
                                            break;
                                    }
                                @endphp
                                <tr class="{{ $rowClass }}">
                                    <td>{{ $item->thuoc->ten_thuoc ?? 'Không rõ' }}</td>
                                    <td>{{ $item->total_stock }} {{ $item->thuoc->don_vi_goc ?? '' }}</td>
                                    <td>{{ $item->earliest_expiry ? date('d/m/Y', strtotime($item->earliest_expiry)) : '---' }}</td>
                                    <td>{!! $badge !!}</td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="4" class="text-center">Không có cảnh báo nào</td>
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
