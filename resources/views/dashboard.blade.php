@extends('layouts.app')

@section('title', 'Dashboard - Hệ Thống Quản Lý Hiệu Thuốc')

@section('styles')
<style>
    #sales_year {
        border: 1px solid #d1d3e2;
        border-radius: 0.2rem;
        padding: 0.25rem 1rem;
        font-size: 0.875rem;
        height: 32px;
        width: 120px;
        cursor: pointer;
        background-color: #fff;
    }
    #sales_year:focus {
        border-color: #bac8f3;
        box-shadow: 0 0 0 0.2rem rgba(78, 115, 223, 0.25);
    }
</style>
@endsection

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
                                <tr>
                                    <td colspan="2" class="text-center">Không có dữ liệu</td>
                                </tr>
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
                                <input type="date" class="form-control form-control-sm" name="purchase_from"
                                    id="purchase_from"
                                    value="{{ request('purchase_from', now()->startOfMonth()->format('Y-m-d')) }}">
                            </div>
                            <div class="col-auto">
                                <label for="purchase_to" class="form-label mb-0">Đến ngày</label>
                                <input type="date" class="form-control form-control-sm" name="purchase_to" id="purchase_to"
                                    value="{{ request('purchase_to', now()->format('Y-m-d')) }}">
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
                    <div class="h5 mb-0 font-weight-bold text-gray-800">{{ number_format($totalPurchase, 0, ',', '.') }} VNĐ
                    </div>
                    <div class="text-xs text-muted mt-2">Tổng nhập hàng từ
                        <b>{{ date('d/m/Y', strtotime($purchaseFrom)) }}</b> đến
                        <b>{{ date('d/m/Y', strtotime($purchaseTo)) }}</b></div>
                </div>
            </div>
        </div>
    </div>

    <div class="row">
        <!-- Doanh số bán -->
        <div class="col-md-12 mb-4">
            <div class="card border-left-warning h-100 py-2">
                <div class="card-header py-3 d-flex flex-row align-items-center justify-content-between">
                                        <h6 class="m-0 font-weight-bold">Doanh số bán theo tháng</h6>
                    <form id="yearFilterForm" class="d-flex align-items-center">
                        <select class="form-select form-select-sm" id="sales_year" name="sales_year" onchange="this.form.submit()">
                            @for($y = now()->year; $y >= now()->year - 5; $y--)
                                <option value="{{ $y }}" {{ request('sales_year', now()->year) == $y ? 'selected' : '' }}>
                                    Năm {{ $y }}
                                </option>
                            @endfor
                        </select>
                    </form>
                </div>
                <div class="card-body">
                    @php
                        // Lấy năm được chọn từ request hoặc mặc định là năm hiện tại
                        $selectedYear = request('sales_year', now()->year);
                        $monthlyData = [];
                        $maxValue = 0;

                        // Kiểm tra xem năm được chọn có dữ liệu không
                        $hasData = \App\Models\DonBanLe::whereYear('ngay_ban', $selectedYear)->exists();

                        // Lặp qua từng tháng trong năm được chọn
                        for ($month = 1; $month <= 12; $month++) {
                            if ($hasData) {
                                $total = \App\Models\DonBanLe::whereYear('ngay_ban', $selectedYear)
                                    ->whereMonth('ngay_ban', $month)
                                    ->sum('tong_tien');
                            } else {
                                $total = 0;
                            }

                            $monthlyData[$month] = $total;
                            $maxValue = max($maxValue, $total); 
                        }
                            
                    @endphp
                </div>
                <div class="card-body">
                    <style>
                        .simple-bar-chart {
                            --line-count: 5;
                            --line-color: currentcolor;
                            --line-opacity: 0.25;
                            --item-gap: 2%;
                            --item-default-color: #060606;

                            height: 15rem;
                            display: grid;
                            grid-auto-flow: column;
                            gap: var(--item-gap);
                            align-items: end;
                            padding-inline: var(--item-gap);
                            --padding-block: 2rem;
                            padding-block: var(--padding-block);
                            position: relative;
                            isolation: isolate;
                            padding-left: 4rem;
                            /* Thêm khoảng trống cho trục y */
                        }

                        .simple-bar-chart::after {
                            content: "";
                            position: absolute;
                            inset: var(--padding-block) 0;
                            left: 4rem;
                            /* Căn chỉnh các line từ sau trục y */
                            z-index: -1;
                            --line-width: 1px;
                            --line-spacing: calc(100% / var(--line-count));
                            background-image: repeating-linear-gradient(to top, transparent 0 calc(var(--line-spacing) - var(--line-width)), var(--line-color) 0 var(--line-spacing));
                            box-shadow: 0 var(--line-width) 0 var(--line-color);
                            opacity: var(--line-opacity);
                        }

                        /* Thêm style cho trục y */
                        .chart-y-axis {
                            position: absolute;
                            left: 0;
                            top: var(--padding-block);
                            bottom: var(--padding-block);
                            width: 4rem;
                            display: flex;
                            flex-direction: column-reverse;
                            justify-content: space-between;
                            font-size: 0.75rem;
                            color: #666;
                        }

                        .chart-y-axis span {
                            padding-right: 0.5rem;
                            text-align: right;
                            transform: translateY(50%);
                        }

                        .simple-bar-chart>.item {
                            height: calc(1% * var(--val));
                            background-color: var(--clr, var(--item-default-color));
                            position: relative;
                            animation: item-height 1s ease forwards;
                            min-width: 30px;
                        }

                        @keyframes item-height {
                            from {
                                height: 0
                            }
                        }

                        .simple-bar-chart>.item>* {
                            position: absolute;
                            text-align: center;
                            width: 100%;
                        }

                        .simple-bar-chart>.item>.label {
                            inset: 100% 0 auto 0;
                            padding-top: 0.25rem;
                        }

                        .simple-bar-chart>.item>.value {
                            bottom: 100%;
                            left: 50%;
                            transform: translateX(-50%);
                            padding-bottom: 0.25rem;
                            white-space: nowrap;
                            color: var(--clr);
                            font-weight: bold;
                        }
                    </style>

                    @php
                        $currentYear = now()->year;
                        $monthlyData = [];
                        $maxValue = 0;

                        // Tính doanh số cho mỗi tháng
                        for ($month = 1; $month <= 12; $month++) {
                            $startDate = "{$currentYear}-{$month}-01";
                            $endDate = date('Y-m-t', strtotime($startDate));

                            $total = \App\Models\DonBanLe::whereYear('ngay_ban', $currentYear)
                                ->whereMonth('ngay_ban', $month)
                                ->sum('tong_tien');

                            $monthlyData[$month] = $total;
                            $maxValue = max($maxValue, $total);
                        }

                        // Chuyển đổi giá trị thành phần trăm dựa trên giá trị cao nhất
                        $monthlyPercentages = array_map(function ($value) use ($maxValue) {
                            return $maxValue > 0 ? round(($value / $maxValue) * 100) : 0;
                        }, $monthlyData);

                        // Mảng màu cho các cột
                        $colors = [
                            '#5EB344',
                            '#FCB72A',
                            '#F8821A',
                            '#E0393E',
                            '#963D97',
                            '#069CDB',
                            '#5EB344',
                            '#FCB72A',
                            '#F8821A',
                            '#E0393E',
                            '#963D97',
                            '#069CDB'
                        ];
                    @endphp

                    <div style="position: relative;">
                        @if($hasData)
                            <!-- Trục Y với các mốc giá trị -->
                            <div class="chart-y-axis">
                                @php
                                    $steps = 5; // Số mốc giá trị trên trục Y
                                    for ($i = 0; $i <= $steps; $i++) {
                                        $value = ($maxValue / $steps) * $i;
                                        echo "<span>" . number_format($value / 1000000, 1, ',', '.') . "tr</span>";
                                    }
                                @endphp
                            </div>

                            <div class="simple-bar-chart">
                                @foreach($monthlyPercentages as $month => $percentage)
                                    <div class="item" style="--clr: {{ $colors[$month - 1] }}; --val: {{ $percentage }}">
                                        <div class="label">T.{{ $month }}</div>
                                        <div class="value">{{ number_format($monthlyData[$month], 0, ',', '.') }}</div>
                                    </div>
                                @endforeach
                            </div>
                        @else
                            <div class="alert alert-info text-center my-4">
                                Không có dữ liệu doanh số cho năm {{ $selectedYear }}
                            </div>
                        @endif
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="row">

        <!-- Biểu đồ tròn phân bố thuốc theo kho -->
<div class="col-md-12 mb-4">
    <div class="card shadow mb-4">
        <div class="card-header py-3 d-flex flex-row align-items-center justify-content-between">
            <h6 class="m-0 font-weight-bold">Phân bố thuốc theo kho</h6>
        </div>
        <div class="card-body">
            <style>
                .storage-layout {
                    display: flex;
                    gap: 2rem;
                }

                .storage-left {
                    flex: 1;
                    text-align: center;
                }

                .storage-right {
                    flex: 2;
                }

                .storage-pie-chart {
                    position: relative;
                    width: 260px;
                    height: 260px;
                    border-radius: 50%;
                    margin: 1rem auto;
                    overflow: hidden;
                }

                .storage-pie-center {
                    position: absolute;
                    top: 50%;
                    left: 50%;
                    transform: translate(-50%, -50%);
                    text-align: center;
                    font-weight: bold;
                    font-size: 1.2rem;
                    z-index: 2;
                    background: white;
                    border-radius: 50%;
                    width: 100px;
                    height: 100px;
                    display: flex;
                    flex-direction: column;
                    align-items: center;
                    justify-content: center;
                }

                .storage-pie-legend {
                    display: flex;
                    flex-direction: column;
                    align-items: flex-start;
                    gap: 0.5rem;
                    margin-top: 0.5rem;
                }

                .storage-pie-legend-item {
                    display: flex;
                    align-items: center;
                    gap: 0.5rem;
                }

                .storage-pie-legend-color {
                    width: 14px;
                    height: 14px;
                    border-radius: 2px;
                }

                .medicine-list {
                    max-height: 400px;
                    overflow-y: auto;
                    border: 1px solid #ddd;
                    border-radius: 8px;
                    padding: 1rem;
                }
            </style>

            @php
                // Sử dụng relationship đã định nghĩa trong model
                $khoData = \App\Models\Kho::with('thuoc')->get();
                
                // Hoặc nếu muốn thêm điều kiện filter chỉ lấy tồn kho > 0:
                // $khoData = \App\Models\Kho::with(['thuoc' => function($query) {
                //     $query->wherePivot('ton_kho_hien_tai', '>', 0);
                // }])->get();
                
                $colors = [
                    '#4e73df','#1cc88a','#36b9cc','#f6c23e','#e74a3b',
                    '#858796','#5a5c69','#f8f9fc','#d1d3e2','#ff7f50',
                    '#8a2be2','#00ced1','#ff1493','#7fff00','#ff6347',
                    '#2e8b57','#daa520','#ff4500','#20b2aa','#778899',
                    '#6a5acd','#cd5c5c','#ff69b4','#b8860b','#4682b4',
                    '#9acd32','#9932cc','#00fa9a','#ff8c00','#4169e1'
                ];

                $defaultKho = $khoData->first();
            @endphp

            <div class="mb-3">
                <label for="khoSelect" class="form-label"><strong>Chọn kho:</strong></label>
                <select id="khoSelect" class="form-select" style="width:auto; display:inline-block;">
                    <option value="">-- Chọn kho --</option>
                    @foreach($khoData as $index => $kho)
                        <option value="{{ $kho->id ?? $kho->kho_id ?? $index }}" data-kho-id="{{ $kho->id ?? $kho->kho_id ?? $index }}">{{ $kho->ten_kho }}</option>
                    @endforeach
                </select>
            </div>

            <div class="storage-layout">
                <div class="storage-left" id="chartArea"></div>
                <div class="storage-right">
                    <h6>Danh sách thuốc</h6>
                    <div class="medicine-list" id="medicineList"></div>
                </div>
            </div>

            <script>
                const khoData = @json($khoData);
                const colors = @json($colors);

                // Debug dữ liệu ban đầu
                console.log('khoData:', khoData);
                console.log('colors:', colors);

                function renderKho(khoId) {
                    console.log('Rendering kho ID:', khoId, 'Type:', typeof khoId);

                    if (!khoId || khoId === '' || isNaN(khoId)) {
                        console.log('Invalid khoId provided');
                        document.getElementById("chartArea").innerHTML = 
                            `<p class="text-warning"><em>Vui lòng chọn kho.</em></p>`;
                        document.getElementById("medicineList").innerHTML = 
                            `<p class="text-warning"><em>Vui lòng chọn kho.</em></p>`;
                        return;
                    }

                    // Tìm kho theo kho_id
                    const kho = khoData.find(k => k.kho_id == khoId);
                    
                    if (!kho) {
                        console.log('Kho not found:', khoId);
                        console.log('Available khos:', khoData.map(k => ({ kho_id: k.kho_id, name: k.ten_kho })));
                        document.getElementById("chartArea").innerHTML = 
                            `<p class="text-danger"><em>Không tìm thấy kho với ID: ${khoId}</em></p>`;
                        document.getElementById("medicineList").innerHTML = 
                            `<p class="text-danger"><em>Không tìm thấy kho.</em></p>`;
                        return;
                    }

                    console.log('Found kho:', kho.ten_kho);

                    // Gộp thuốc theo thuoc_id và tính tổng ton_kho_hien_tai
                    let totalQuantity = 0;
                    const medicineMap = {};
                    
                    if (kho.thuoc && kho.thuoc.length > 0) {
                        kho.thuoc.forEach(thuoc => {
                            const thuocId = thuoc.thuoc_id;
                            const tonKho = parseFloat(thuoc.pivot.ton_kho_hien_tai) || 0;

                            if (!medicineMap[thuocId]) {
                                medicineMap[thuocId] = {
                                    name: thuoc.ten_thuoc,
                                    quantity: 0
                                };
                            }
                            medicineMap[thuocId].quantity += tonKho;
                            totalQuantity += tonKho;
                        });
                    }

                    // Chuyển medicineMap thành mảng medicines
                    const medicines = Object.values(medicineMap).filter(med => med.quantity > 0);

                    console.log('Total medicines:', medicines.length);
                    console.log('Total quantity:', totalQuantity);
                    console.log('Medicines data:', medicines);

                    // Kiểm tra nếu không có thuốc tồn kho
                    if (medicines.length === 0 || totalQuantity === 0) {
                        document.getElementById("chartArea").innerHTML = 
                            `<p class="text-warning"><em>Kho "${kho.ten_kho}" chưa có thuốc tồn.</em></p>`;
                        document.getElementById("medicineList").innerHTML = 
                            `<p class="text-warning"><em>Không có thuốc trong kho này.</em></p>`;
                        return;
                    }

                    // Sắp xếp thuốc theo số lượng giảm dần
                    medicines.sort((a, b) => b.quantity - a.quantity);

                    // Tính gradient cho biểu đồ tròn
                    let cumulative = 0;
                    const gradientParts = medicines.map((med, i) => {
                        const percent = (med.quantity / totalQuantity) * 100;
                        const color = colors[i % colors.length];
                        const part = `${color} ${cumulative}% ${cumulative + percent}%`;
                        cumulative += percent;
                        return part;
                    }).join(", ");

                    console.log('Gradient parts:', gradientParts);

                    // Render biểu đồ
                    document.getElementById("chartArea").innerHTML = `
                        <div class="storage-pie-chart" style="background: conic-gradient(${gradientParts});">
                            <div class="storage-pie-center">
                                <div>${totalQuantity.toFixed(2)}</div>
                                <small>đơn vị</small>
                            </div>
                        </div>
                    `;

                    // Render storage-pie-legend trong storage-right
                    document.getElementById("medicineList").innerHTML = `
                        <div class="storage-pie-legend">
                            ${medicines.map((med, i) => {
                                const percent = ((med.quantity / totalQuantity) * 100).toFixed(1);
                                return `
                                    <div class="storage-pie-legend-item">
                                        <div class="storage-pie-legend-color" style="background:${colors[i % colors.length]}"></div>
                                        <span><strong>${med.name}:</strong> ${med.quantity.toFixed(2)} – ${percent}%</span>
                                    </div>`;
                            }).join("")}
                        </div>
                    `;
                    
                    console.log('Chart and list rendered successfully');
                }

                // Load kho mặc định khi trang được tải
                document.addEventListener('DOMContentLoaded', function() {
                    console.log('DOM loaded, available khos:', khoData.length);
                    console.log('Full khoData structure:', khoData);
                    
                    const selectElement = document.getElementById("khoSelect");
                    
                    if (khoData.length > 0) {
                        const firstKho = khoData[0];
                        const khoId = firstKho.kho_id;
                        console.log('Detected kho ID:', khoId);
                        
                        if (khoId) {
                            selectElement.value = khoId;
                            renderKho(khoId);
                            console.log('Set default kho:', khoId, firstKho.ten_kho);
                        } else {
                            console.log('Could not find kho_id in kho object');
                            console.log('Available fields:', Object.keys(firstKho));
                            document.getElementById("chartArea").innerHTML = 
                                `<p class="text-danger"><em>Không có dữ liệu kho.</em></p>`;
                            document.getElementById("medicineList").innerHTML = 
                                `<p class="text-danger"><em>Không có dữ liệu kho.</em></p>`;
                        }
                    } else {
                        console.log('No kho data available');
                        document.getElementById("chartArea").innerHTML = 
                            `<p class="text-danger"><em>Không có dữ liệu kho.</em></p>`;
                        document.getElementById("medicineList").innerHTML = 
                            `<p class="text-danger"><em>Không có dữ liệu kho.</em></p>`;
                    }
                });

                // Xử lý sự kiện thay đổi kho
                document.getElementById("khoSelect").addEventListener("change", function() {
                    const selectedKhoId = this.value;
                    console.log('Selected kho ID:', selectedKhoId);
                    
                    if (selectedKhoId && selectedKhoId !== '') {
                        renderKho(parseInt(selectedKhoId));
                    } else {
                        console.log('Invalid kho ID selected:', selectedKhoId);
                        document.getElementById("chartArea").innerHTML = 
                            `<p class="text-warning"><em>Vui lòng chọn kho.</em></p>`;
                        document.getElementById("medicineList").innerHTML = 
                            `<p class="text-warning"><em>Vui lòng chọn kho.</em></p>`;
                    }
                });

                // Debug button để kiểm tra select
                document.getElementById("debugBtn").addEventListener("click", function() {
                    const select = document.getElementById("khoSelect");
                    console.log('=== SELECT DEBUG ===');
                    console.log('Select element:', select);
                    console.log('Current value:', select.value);
                    console.log('Selected index:', select.selectedIndex);
                    console.log('All options:');
                    
                    for (let i = 0; i < select.options.length; i++) {
                        const option = select.options[i];
                        console.log(`Option ${i}:`, {
                            text: option.text,
                            value: option.value,
                            dataKhoId: option.getAttribute('data-kho-id'),
                            selected: option.selected
                        });
                    }
                    console.log('=== END DEBUG ===');
                });
            </script>
        </div>
    </div>
</div>

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
                            ->whereHas('donBanLe', function ($q) use ($startDate, $endDate) {
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
                            ->whereHas('donBanLe', function ($q) use ($startDate, $endDate) {
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
                                        <tr>
                                            <td colspan="2" class="text-center">Không có dữ liệu</td>
                                        </tr>
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
                                        <tr>
                                            <td colspan="2" class="text-center">Không có dữ liệu</td>
                                        </tr>
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
                                ->select(
                                    'thuoc_id',
                                    DB::raw('SUM(ton_kho_hien_tai) as total_stock'),
                                    DB::raw('MIN(han_su_dung) as earliest_expiry')
                                )
                                ->where('ton_kho_hien_tai', '>', 0)
                                ->groupBy('thuoc_id')
                                ->having('total_stock', '<=', 10)
                                ->get()
                                ->map(function ($item) {
                                    $item->status = $item->total_stock <= 5 ? 'critical_stock' : 'low_stock';
                                    return $item;
                                });

                            // Thuốc sắp hết hạn (≤ 30 ngày nữa)
                            $nearlyExpired = LoThuoc::with('thuoc')
                                ->select(
                                    'thuoc_id',
                                    DB::raw('SUM(ton_kho_hien_tai) as total_stock'),
                                    DB::raw('MIN(han_su_dung) as earliest_expiry')
                                )
                                ->where('ton_kho_hien_tai', '>', 0)
                                ->whereBetween('han_su_dung', [$today, $next30days])
                                ->groupBy('thuoc_id')
                                ->get()
                                ->map(function ($item) {
                                    $item->status = 'nearly_expired';
                                    return $item;
                                });

                            // Thuốc đã hết hạn
                            $expired = LoThuoc::with('thuoc')
                                ->select(
                                    'thuoc_id',
                                    DB::raw('SUM(ton_kho_hien_tai) as total_stock'),
                                    DB::raw('MIN(han_su_dung) as earliest_expiry')
                                )
                                ->where('ton_kho_hien_tai', '>', 0)
                                ->whereDate('han_su_dung', '<', $today)
                                ->groupBy('thuoc_id')
                                ->get()
                                ->map(function ($item) {
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
                                        <td>{{ $item->earliest_expiry ? date('d/m/Y', strtotime($item->earliest_expiry)) : '---' }}
                                        </td>
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