@extends('layouts.app')

@section('title', 'Quản Lý Lô Thuốc - Hệ Thống Quản Lý Hiệu Thuốc')

@section('page-title', 'Quản Lý Lô Thuốc')

@section('styles')
<style>
    .status-badge {
        font-size: 0.85rem;
        font-weight: 500;
    }
    .expired {
        background-color: #dc3545;
    }
    .near-expiry {
        background-color: #ffc107;
    }
    .normal {
        background-color: #28a745;
    }
    .out-of-stock {
        background-color: #6c757d;
    }
    
    .table-filter {
        background-color: #f8f9fa;
        padding: 1rem;
        border-radius: 0.25rem;
        margin-bottom: 1rem;
    }
    
    .lot-card {
        transition: all 0.3s;
    }
    
    .lot-card:hover {
        transform: translateY(-5px);
        box-shadow: 0 0.5rem 1rem rgba(0, 0, 0, 0.15);
    }
    
    .lot-info {
        display: flex;
        justify-content: space-between;
        margin-bottom: 0.5rem;
    }
    
    .view-mode-toggle {
        margin-bottom: 1rem;
    }
</style>
@endsection

@section('content')
<div class="row mb-4">
    <div class="col-md-12">
        <div class="card table-filter">
            <div class="card-body">
                <form id="filterForm" method="GET" action="{{ route('lo-thuoc.index') }}">
                    <div class="row g-2">
                        <div class="col-md-4">
                            <label for="thuoc_id" class="form-label small">Thuốc</label>
                            <select class="form-select form-select-sm" id="thuoc_id" name="thuoc_id">
                                <option value="">Tất cả thuốc</option>
                                @foreach($thuocs as $thuoc)
                                    <option value="{{ $thuoc->thuoc_id }}" @if(request('thuoc_id') == $thuoc->thuoc_id) selected @endif>{{ $thuoc->ten_thuoc }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-md-4">
                            <label for="kho_id" class="form-label small">Kho</label>
                            <select class="form-select form-select-sm" id="kho_id" name="kho_id">
                                <option value="">Tất cả kho</option>
                                @foreach($khos as $kho)
                                    <option value="{{ $kho->kho_id }}" @if(request('kho_id') == $kho->kho_id) selected @endif>{{ $kho->ten_kho }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-md-4">
                            <label for="sort_by" class="form-label small">Sắp xếp theo</label>
                            <div class="input-group input-group-sm">
                                <select class="form-select" id="sort_by" name="sort_by">
                                    <option value="han_su_dung" @if(request('sort_by', 'han_su_dung') == 'han_su_dung') selected @endif>Hạn sử dụng</option>
                                    <option value="ton_kho_hien_tai" @if(request('sort_by') == 'ton_kho_hien_tai') selected @endif>Tồn kho</option>
                                    <option value="ma_lo" @if(request('sort_by') == 'ma_lo') selected @endif>Mã lô</option>
                                </select>
                                <select class="form-select" id="sort_direction" name="sort_direction">
                                    <option value="asc" @if(request('sort_direction', 'asc') == 'asc') selected @endif>Tăng dần</option>
                                    <option value="desc" @if(request('sort_direction') == 'desc') selected @endif>Giảm dần</option>
                                </select>
                            </div>
                        </div>
                        <div class="col-md-8">
                            <div class="form-check form-check-inline">
                                <input class="form-check-input" type="checkbox" id="con_ton_kho" name="con_ton_kho" value="1" @if(request('con_ton_kho')) checked @endif>
                                <label class="form-check-label small" for="con_ton_kho">Còn tồn kho</label>
                            </div>
                            <div class="form-check form-check-inline">
                                <input class="form-check-input" type="checkbox" id="sap_het_han" name="sap_het_han" value="1" @if(request('sap_het_han')) checked @endif>
                                <label class="form-check-label small" for="sap_het_han">Sắp hết hạn (&le; 30 ngày)</label>
                            </div>
                            <div class="form-check form-check-inline">
                                <input class="form-check-input" type="checkbox" id="het_han_chua_huy" name="het_han_chua_huy" value="1" @if(request('het_han_chua_huy')) checked @endif>
                                <label class="form-check-label small" for="het_han_chua_huy">Hết hạn (chưa hủy)</label>
                            </div>
                            <div class="form-check form-check-inline">
                                <input class="form-check-input" type="checkbox" id="het_han_da_huy" name="het_han_da_huy" value="1" @if(request('het_han_da_huy')) checked @endif>
                                <label class="form-check-label small" for="het_han_da_huy">Hết hạn (đã hủy)</label>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <label for="keyword" class="form-label small">Tìm kiếm</label>
                            <div class="input-group input-group-sm">
                                <input type="text" class="form-control" id="keyword" name="keyword" placeholder="Mã lô, số lô NSX, ghi chú..." value="{{ request('keyword') }}">
                                <button class="btn btn-primary" type="submit">
                                    <i class="bi bi-search"></i>
                                </button>
                                <button class="btn btn-outline-secondary" type="button" id="resetFilter">
                                    <i class="bi bi-x-circle"></i> Reset
                                </button>
                            </div>
                        </div>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

<!-- Nút chuyển đổi chế độ xem -->
<div class="view-mode-toggle mb-3">
    <div class="btn-group" role="group">
        <button type="button" class="btn btn-outline-primary active" id="tableViewBtn">
            <i class="bi bi-table me-1"></i> Dạng bảng
        </button>
        <button type="button" class="btn btn-outline-primary" id="cardViewBtn">
            <i class="bi bi-grid-3x3-gap me-1"></i> Dạng thẻ
        </button>
    </div>
</div>

<!-- Chế độ xem bảng -->
<div id="tableView">
    <div class="card">
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-hover table-striped">
                    <thead class="table-light">
                        <tr>
                            <th>Mã lô</th>
                            <th>Thuốc</th>
                            <th>Kho</th>
                            <th>Tồn kho</th>
                            <th>NSX</th>
                            <th>HSD</th>
                            <th>Trạng thái</th>
                            <th class="text-center">Thao tác</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($loThuocs as $lo)
                        <tr>
                            <td>
                                {{ $lo->ma_lo ?? 'Chưa nhập' }}
                                @if($lo->so_lo_nha_san_xuat)
                                <div class="small text-muted">NSX: {{ $lo->so_lo_nha_san_xuat }}</div>
                                @endif
                            </td>
                            <td>{{ $lo->thuoc->ten_thuoc }}</td>
                            <td>{{ $lo->kho->ten_kho ?? 'Không xác định' }}</td>
                            <td>
                                {{ number_format($lo->ton_kho_hien_tai, 2) }} {{ $lo->thuoc->don_vi_goc }}
                                <div class="small text-muted">Tổng nhập: {{ number_format($lo->tong_so_luong, 2) }}</div>
                            </td>
                            <td>{{ $lo->ngay_san_xuat ? \Carbon\Carbon::parse($lo->ngay_san_xuat)->format('d/m/Y') : 'Chưa nhập' }}</td>
                            <td>{{ \Carbon\Carbon::parse($lo->han_su_dung)->format('d/m/Y') }}</td>
                            <td>
                                @php
                                    $today = \Carbon\Carbon::today();
                                    $expiry = \Carbon\Carbon::parse($lo->han_su_dung);
                                    $diffDays = $today->diffInDays($expiry, false);
                                    // Xác định đã hủy tồn hay chưa
                                    $daHuyTon = ($lo->ton_kho_hien_tai <= 0 && $expiry < $today);
                                    if ($lo->ton_kho_hien_tai <= 0 && $expiry < $today) {
                                        $status = 'out-of-stock expired';
                                        $statusText = 'Hết hạn (đã hủy)';
                                    } elseif ($diffDays < 0) {
                                        $status = 'expired';
                                        $statusText = 'Hết hạn (chưa hủy)';
                                    } elseif ($diffDays <= 30) {
                                        $status = 'near-expiry';
                                        $statusText = 'Sắp hết hạn (còn ' . max(0, $diffDays) . ' ngày)';
                                    } elseif ($lo->ton_kho_hien_tai <= 0) {
                                        $status = 'out-of-stock';
                                        $statusText = 'Hết hàng';
                                    } else {
                                        $status = 'normal';
                                        $statusText = 'Bình thường';
                                    }
                                @endphp
                                <span class="badge status-badge {{ $status }}">{{ $statusText }}</span>
                            </td>
                            <td class="text-center">
                                <div class="btn-group" role="group">
                                    <a href="{{ route('lo-thuoc.show', $lo->lo_id) }}" class="btn btn-info">
                                        <i class="bi bi-eye"></i>
                                    </a>
                                    @if($lo->ton_kho_hien_tai > 0 && $diffDays < 0)
                                    <a href="{{ route('lo-thuoc.dispose', $lo->lo_id) }}" class="btn btn-danger">
                                        <i class="bi bi-trash"></i>
                                    </a>
                                    @endif
                                </div>
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="8" class="text-center py-4">
                                <div class="alert alert-info mb-0">
                                    <i class="bi bi-info-circle me-2"></i> Không tìm thấy lô thuốc nào.
                                </div>
                            </td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
            
            {{ $loThuocs->onEachSide(1)->links('vendor.pagination.custom') }}
        </div>
    </div>
</div>

<!-- Chế độ xem thẻ -->
<div id="cardView" style="display: none;">
    <div class="row">
        @forelse($loThuocs as $lo)
            <div class="col-md-4 mb-4">
                <div class="card lot-card h-100">
                    @php
                        $today = \Carbon\Carbon::today();
                        $expiry = \Carbon\Carbon::parse($lo->han_su_dung);
                        $diffDays = $today->diffInDays($expiry, false);
                        
                        if ($lo->ton_kho_hien_tai <= 0) {
                            $cardClass = 'border-secondary';
                            $headerClass = 'bg-secondary text-white';
                        } elseif ($diffDays < 0) {
                            $cardClass = 'border-danger';
                            $headerClass = 'bg-danger text-white';
                        } elseif ($diffDays <= 30) {
                            $cardClass = 'border-warning';
                            $headerClass = 'bg-warning';
                        } else {
                            $cardClass = 'border-success';
                            $headerClass = 'bg-success text-white';
                        }
                    @endphp
                    
                    <div class="card-header {{ $headerClass }}">
                        <div class="d-flex justify-content-between align-items-center">
                            <h6 class="mb-0">{{ $lo->thuoc->ten_thuoc }}</h6>
                            @if($lo->ton_kho_hien_tai <= 0)
                                <span class="badge bg-secondary">Hết hàng</span>
                            @elseif($diffDays < 0)
                                <span class="badge bg-danger">Hết hạn</span>
                            @elseif($diffDays <= 30)
                                <span class="badge bg-warning text-dark">Còn {{ $diffDays }} ngày</span>
                            @else
                                <span class="badge bg-success">Còn hạn</span>
                            @endif
                        </div>
                    </div>
                    <div class="card-body">
                        <div class="lot-info">
                            <span class="fw-bold">Mã lô:</span>
                            <span>{{ $lo->ma_lo ?? 'Chưa nhập' }}</span>
                        </div>
                        @if($lo->so_lo_nha_san_xuat)
                        <div class="lot-info">
                            <span class="fw-bold">Số lô NSX:</span>
                            <span>{{ $lo->so_lo_nha_san_xuat }}</span>
                        </div>
                        @endif
                        <div class="lot-info">
                            <span class="fw-bold">Kho:</span>
                            <span>{{ $lo->kho->ten_kho ?? 'Không xác định' }}</span>
                        </div>
                        <div class="lot-info">
                            <span class="fw-bold">Tồn kho:</span>
                            <span>{{ number_format($lo->ton_kho_hien_tai, 2) }} {{ $lo->thuoc->don_vi_goc }}</span>
                        </div>
                        <div class="lot-info">
                            <span class="fw-bold">NSX:</span>
                            <span>{{ $lo->ngay_san_xuat ? \Carbon\Carbon::parse($lo->ngay_san_xuat)->format('d/m/Y') : 'Chưa nhập' }}</span>
                        </div>
                        <div class="lot-info">
                            <span class="fw-bold">HSD:</span>
                            <span>{{ \Carbon\Carbon::parse($lo->han_su_dung)->format('d/m/Y') }}</span>
                        </div>
                        @if($lo->ghi_chu)
                        <div class="mt-2">
                            <span class="fw-bold">Ghi chú:</span>
                            <p class="small mb-0">{{ $lo->ghi_chu }}</p>
                        </div>
                        @endif
                    </div>
                    <div class="card-footer">
                        <div class="d-flex justify-content-between">
                            <a href="{{ route('lo-thuoc.show', $lo->lo_id) }}" class="btn btn-sm btn-info">
                                <i class="bi bi-eye me-1"></i> Chi tiết
                            </a>
                            @if($lo->ton_kho_hien_tai > 0 && $diffDays < 0)
                            <a href="{{ route('lo-thuoc.dispose', $lo->lo_id) }}" class="btn btn-sm btn-danger">
                                <i class="bi bi-trash me-1"></i> Hủy tồn
                            </a>
                            @endif
                        </div>
                    </div>
                </div>
            </div>
        @empty
            <div class="col-12">
                <div class="alert alert-info text-center">
                    <i class="bi bi-info-circle me-2"></i> Không tìm thấy lô thuốc nào.
                </div>
            </div>
        @endforelse
    </div>
    
    <div class="d-flex justify-content-end mt-3">
        {{ $loThuocs->appends(request()->query())->links() }}
    </div>
</div>

@endsection

@section('scripts')
<script>
    $(document).ready(function() {
        // Xử lý form lọc
        $('#thuoc_id, #kho_id, #sort_by, #sort_direction').change(function() {
            $('#filterForm').submit();
        });
        
        $('#con_ton_kho, #sap_het_han, #het_han, #het_han_chua_huy, #het_han_da_huy').change(function() {
            $('#filterForm').submit();
        });
        
        // Reset filter
        $('#resetFilter').click(function() {
            window.location.href = '{{ route("lo-thuoc.index") }}';
        });
        
        // Chuyển đổi chế độ xem
        $('#tableViewBtn').click(function() {
            $('#tableView').show();
            $('#cardView').hide();
            $('#tableViewBtn').addClass('active');
            $('#cardViewBtn').removeClass('active');
            localStorage.setItem('lotViewMode', 'table');
        });
        
        $('#cardViewBtn').click(function() {
            $('#tableView').hide();
            $('#cardView').show();
            $('#cardViewBtn').addClass('active');
            $('#tableViewBtn').removeClass('active');
            localStorage.setItem('lotViewMode', 'card');
        });
        
        // Khôi phục chế độ xem từ localStorage
        const viewMode = localStorage.getItem('lotViewMode');
        if (viewMode === 'card') {
            $('#cardViewBtn').click();
        }
    });
</script>
@endsection
