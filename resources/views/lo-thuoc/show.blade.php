@extends('layouts.app')

@section('title', 'Chi Tiết Lô Thuốc - Hệ Thống Quản Lý Hiệu Thuốc')

@section('page-title', 'Chi Tiết Lô Thuốc')

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
    
    .info-section {
        background-color: #f8f9fa;
        border-radius: 0.25rem;
        padding: 1rem;
        margin-bottom: 1.5rem;
    }
    
    .info-item {
        display: flex;
        margin-bottom: 0.5rem;
    }
    
    .info-label {
        width: 150px;
        font-weight: 500;
    }
    
    .info-value {
        flex: 1;
    }
    
    .history-item {
        border-left: 3px solid #4e73df;
        padding-left: 1rem;
        margin-bottom: 1rem;
        position: relative;
    }
    
    .history-item:before {
        content: "";
        position: absolute;
        width: 12px;
        height: 12px;
        border-radius: 50%;
        background-color: #4e73df;
        left: -7.5px;
        top: 0;
    }
    
    .history-header {
        display: flex;
        justify-content: space-between;
        margin-bottom: 0.5rem;
    }
    
    .history-date {
        font-size: 0.85rem;
        color: #6c757d;
    }
    
    .history-action {
        font-weight: 500;
    }
    
    .history-content {
        padding: 0.5rem 0;
    }
    
    .tab-content {
        padding-top: 1.5rem;
    }
    
    .stock-action-panel {
        border: 1px solid #dee2e6;
        border-radius: 0.25rem;
        padding: 1rem;
        margin-bottom: 1.5rem;
    }
</style>
@endsection

@section('content')
<div class="row mb-3">
    <div class="col-12">
        @if(Auth::user() && Auth::user()->vai_tro === 'admin')
            <button class="btn btn-secondary" onclick="window.history.back();">
                <i class="bi bi-arrow-left me-1"></i> Quay Lại
            </button>
        @endif
        @php
            $today = \Carbon\Carbon::today();
            $expiry = \Carbon\Carbon::parse($loThuoc->han_su_dung);
            $diffDays = $today->diffInDays($expiry, false);
        @endphp
        @if(!(Auth::user() && Auth::user()->vai_tro === 'duoc_si') && $loThuoc->ton_kho_hien_tai > 0 && $diffDays < 0)
        <a href="{{ route('lo-thuoc.dispose', $loThuoc->lo_id) }}" class="btn btn-danger ms-2">
            <i class="bi bi-trash me-1"></i> Hủy Tồn Hết Hạn
        </a>
        @endif
    </div>
</div>

<div class="row">
    <div class="col-md-4">
        <div class="card mb-4">
            <div class="card-header bg-primary text-white">
                <h6 class="mb-0">Thông Tin Lô Thuốc</h6>
            </div>
            <div class="card-body">
                <div class="info-section">
                    <div class="info-item">
                        <div class="info-label">Mã lô:</div>
                        <div class="info-value">{{ $loThuoc->ma_lo ?? 'Chưa có mã lô' }}</div>
                    </div>
                    <div class="info-item">
                        <div class="info-label">Số lô NSX:</div>
                        <div class="info-value">{{ $loThuoc->so_lo_nha_san_xuat ?? 'Không có' }}</div>
                    </div>
                    <div class="info-item">
                        <div class="info-label">Thuốc:</div>
                        <div class="info-value">{{ $loThuoc->thuoc->ten_thuoc }}</div>
                    </div>
                    <div class="info-item">
                        <div class="info-label">Kho lưu trữ:</div>
                        <div class="info-value">{{ $loThuoc->kho->ten_kho ?? 'Không xác định' }}</div>
                    </div>
                </div>
                
                <div class="info-section">
                    <div class="info-item">
                        <div class="info-label">Ngày sản xuất:</div>
                        <div class="info-value">{{ $loThuoc->ngay_san_xuat ? \Carbon\Carbon::parse($loThuoc->ngay_san_xuat)->format('d/m/Y') : 'Không có' }}</div>
                    </div>
                    <div class="info-item">
                        <div class="info-label">Hạn sử dụng:</div>
                        <div class="info-value">{{ \Carbon\Carbon::parse($loThuoc->han_su_dung)->format('d/m/Y') }}</div>
                    </div>
                    <div class="info-item">
                        <div class="info-label">Trạng thái:</div>
                        <div class="info-value">
                            @php
                                $today = \Carbon\Carbon::today();
                                $expiry = \Carbon\Carbon::parse($loThuoc->han_su_dung);
                                $diffDays = $today->diffInDays($expiry, false);
                                
                                if ($loThuoc->ton_kho_hien_tai <= 0) {
                                    $status = 'out-of-stock';
                                    $statusText = 'Hết hàng';
                                } elseif ($diffDays < 0) {
                                    $status = 'expired';
                                    $statusText = 'Hết hạn';
                                } elseif ($diffDays <= 30) {
                                    $status = 'near-expiry';
                                    $statusText = 'Sắp hết hạn';
                                } else {
                                    $status = 'normal';
                                    $statusText = 'Bình thường';
                                }
                            @endphp
                            <span class="badge status-badge {{ $status }}">{{ $statusText }}</span>
                            @if($diffDays > 0 && $diffDays <= 30)
                                <span class="ms-2">(Còn {{ $diffDays }} ngày)</span>
                            @endif
                        </div>
                    </div>
                </div>
                
                <div class="info-section">
                    <div class="info-item">
                        <div class="info-label">Tổng nhập:</div>
                        <div class="info-value">{{ number_format($loThuoc->tong_so_luong, 2) }} {{ $loThuoc->thuoc->don_vi_goc }}</div>
                    </div>
                    <div class="info-item">
                        <div class="info-label">Tồn kho hiện tại:</div>
                        <div class="info-value">{{ number_format($loThuoc->ton_kho_hien_tai, 2) }} {{ $loThuoc->thuoc->don_vi_goc }}</div>
                    </div>
                    <div class="info-item">
                        <div class="info-label">Đã xuất:</div>
                        <div class="info-value">
                            {{ number_format($loThuoc->tong_so_luong - $loThuoc->ton_kho_hien_tai, 2) }} {{ $loThuoc->thuoc->don_vi_goc }}
                            @if($loThuoc->tong_so_luong > $loThuoc->ton_kho_hien_tai)
                                <a href="#export-tab" class="ms-2 badge bg-info text-decoration-none" onclick="showExportHistory()">
                                    <i class="bi bi-list-ul"></i> Xem chi tiết
                                </a>
                            @endif
                        </div>
                    </div>
                    <div class="info-item">
                        <div class="info-label">Giá nhập TB:</div>
                        <div class="info-value">{{ number_format($loThuoc->gia_nhap_tb) }} VNĐ</div>
                    </div>
                </div>
                
                @if($loThuoc->ghi_chu)
                <div class="info-section">
                    <div class="info-item">
                        <div class="info-label">Ghi chú:</div>
                        <div class="info-value">{{ $loThuoc->ghi_chu }}</div>
                    </div>
                </div>
                @endif
            </div>
        </div>
    </div>
    
    <div class="col-md-8">
        <ul class="nav nav-tabs" id="lotTab" role="tablist">
            <li class="nav-item" role="presentation">
                <button class="nav-link active" id="import-tab" data-bs-toggle="tab" data-bs-target="#import-tab-pane" type="button" role="tab">Lịch Sử Nhập</button>
            </li>
            <li class="nav-item" role="presentation">
                <button class="nav-link" id="export-tab" data-bs-toggle="tab" data-bs-target="#export-tab-pane" type="button" role="tab">Lịch Sử Xuất</button>
            </li>
        </ul>
        <div class="tab-content" id="lotTabContent">
            <!-- Tab Lịch sử nhập -->
            <div class="tab-pane fade show active" id="import-tab-pane" role="tabpanel" aria-labelledby="import-tab" tabindex="0">
                <div class="card">
                    <div class="card-header bg-primary text-white">
                        <h6 class="mb-0">Lịch Sử Nhập Hàng</h6>
                    </div>
                    <div class="card-body">
                        @if($phieuNhaps->count() > 0)
                            @foreach($phieuNhaps as $phieuNhap)
                                @php
                                    $chiTiet = $loThuoc->chiTietLoNhap->where('phieu_id', $phieuNhap->phieu_id)->first();
                                @endphp
                                <div class="history-item">
                                    <div class="history-header">
                                        <div class="history-action">Phiếu nhập #{{ $phieuNhap->ma_phieu }}</div>
                                        <div class="history-date">{{ \Carbon\Carbon::parse($phieuNhap->ngay_nhap)->format('d/m/Y') }}</div>
                                    </div>
                                    <div class="history-content">
                                        <div class="row">
                                            <div class="col-md-6">
                                                <p class="mb-1"><strong>Nhà cung cấp:</strong> {{ $phieuNhap->nhaCungCap->ten_ncc }}</p>
                                                @php
                                                    // Map unit flag to actual unit text: 1 => don_vi_ban, 0 => don_vi_goc
                                                    $importUnit = '';
                                                    if (isset($chiTiet->don_vi)) {
                                                        $importUnit = ($chiTiet->don_vi == 1 || $chiTiet->don_vi === '1')
                                                            ? ($loThuoc->thuoc->don_vi_ban ?? '')
                                                            : ($loThuoc->thuoc->don_vi_goc ?? '');
                                                    }
                                                @endphp
                                                <p class="mb-1"><strong>Số lượng:</strong> {{ number_format($chiTiet->so_luong, 2) }} {{ $importUnit }}</p>
                                                <p class="mb-1"><strong>Giá nhập:</strong> {{ number_format($chiTiet->gia_nhap) }} VNĐ</p>
                                            </div>
                                            <div class="col-md-6">
                                                <p class="mb-1"><strong>Thuế suất:</strong> {{ $chiTiet->thue_suat }}%</p>
                                                <p class="mb-1"><strong>Tiền thuế:</strong> {{ number_format($chiTiet->tien_thue) }} VNĐ</p>
                                                <p class="mb-1"><strong>Thành tiền:</strong> {{ number_format($chiTiet->thanh_tien) }} VNĐ</p>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            @endforeach
                        @else
                            <div class="alert alert-info">
                                <i class="bi bi-info-circle me-2"></i> Không tìm thấy lịch sử nhập hàng cho lô thuốc này.
                            </div>
                        @endif
                    </div>
                </div>
            </div>
            
            <!-- Tab Lịch sử xuất -->
            <div class="tab-pane fade" id="export-tab-pane" role="tabpanel" aria-labelledby="export-tab" tabindex="0">
                <div class="card">
                    <div class="card-header bg-primary text-white">
                        <h6 class="mb-0">Lịch Sử Xuất Thuốc</h6>
                    </div>
                    <div class="card-body">
                        @php
                            $lichSuXuat = \App\Models\LichSuTonKho::where('lo_id', $loThuoc->lo_id)
                                ->where('loai_thay_doi', 'ban')
                                ->with(['donBanLe', 'chiTietDonBanLe', 'nguoiDung'])
                                ->orderBy('created_at', 'desc')
                                ->get();
                        @endphp
                        
                        @if($lichSuXuat->count() > 0)
                            @foreach($lichSuXuat as $lichSu)
                                <div class="history-item">
                                    <div class="history-header">
                                        <div class="history-action">
                                            @if($lichSu->donBanLe)
                                                Hóa đơn#{{ $lichSu->donBanLe->ma_don }}
                                            @else
                                                Xuất kho
                                            @endif
                                        </div>
                                        <div class="history-date">{{ $lichSu->created_at->format('d/m/Y H:i:s') }}</div>
                                    </div>
                                    <div class="history-content">
                                        <div class="row">
                                            <div class="col-md-6">
                                                <p class="mb-1"><strong>Số lượng:</strong> {{ number_format(abs($lichSu->so_luong_thay_doi), 2) }} {{ $loThuoc->thuoc->don_vi_goc }}</p>
                                                <p class="mb-1"><strong>Tồn kho sau xuất:</strong> {{ number_format($lichSu->ton_kho_moi, 2) }} {{ $loThuoc->thuoc->don_vi_goc }}</p>
                                                @if($lichSu->nguoiDung)
                                                <p class="mb-1"><strong>Người thực hiện:</strong> {{ $lichSu->nguoiDung->ho_ten }}</p>
                                                @endif
                                            </div>
                                            <div class="col-md-6">
                                                @if($lichSu->donBanLe && $lichSu->chiTietDonBanLe)
                                                    @if($lichSu->donBanLe->khachHang)
                                                    <p class="mb-1"><strong>Khách hàng:</strong> {{ $lichSu->donBanLe->khachHang->ho_ten }}</p>
                                                    @endif
                                                    @php
                                                        $sellUnit = '';
                                                        if (isset($lichSu->chiTietDonBanLe->don_vi)) {
                                                            $sellUnit = ($lichSu->chiTietDonBanLe->don_vi == 1 || $lichSu->chiTietDonBanLe->don_vi === '1')
                                                                ? ($loThuoc->thuoc->don_vi_ban ?? '')
                                                                : ($loThuoc->thuoc->don_vi_goc ?? '');
                                                        }
                                                    @endphp
                                                    <p class="mb-1"><strong>Đơn vị bán:</strong> {{ $sellUnit }}</p>
                                                    <p class="mb-1"><strong>Giá bán:</strong> {{ number_format($lichSu->chiTietDonBanLe->gia_ban) }} VNĐ</p>
                                                @endif
                                                <p class="mb-1"><strong>Ghi chú:</strong> {{ $lichSu->mo_ta }}</p>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            @endforeach
                        @else
                            <div class="alert alert-info">
                                <i class="bi bi-info-circle me-2"></i> Chưa có lịch sử xuất hàng cho lô thuốc này.
                            </div>
                        @endif
                    </div>
                </div>
            </div>
            
            <!-- Tab Điều chỉnh tồn kho -->
            <div class="tab-pane fade" id="adjustment-tab-pane" role="tabpanel" aria-labelledby="adjustment-tab" tabindex="0">
                <div class="card">
                    <div class="card-header bg-primary text-white">
                        <h6 class="mb-0">Điều Chỉnh Tồn Kho</h6>
                    </div>
                    <div class="card-body">
                                @if($loThuoc->ton_kho_hien_tai > 0)
                                    @if(Auth::user() && Auth::user()->vai_tro === 'duoc_si')
                                        <div class="alert alert-info">
                                            <i class="bi bi-info-circle me-2"></i> Bạn không có quyền điều chỉnh tồn kho (chỉ admin có quyền này).
                                        </div>
                                    @else
                                        <div class="stock-action-panel">
                                            <h6 class="mb-3">Thực Hiện Điều Chỉnh</h6>
                                            <form action="{{ route('lo-thuoc.adjust-stock', $loThuoc->lo_id) }}" method="POST" id="adjustmentForm">
                                                @csrf
                                                <div class="row mb-3">
                                                    <div class="col-md-4">
                                                        <label for="adjustment_type" class="form-label">Loại điều chỉnh</label>
                                                        <select class="form-select @error('adjustment_type') is-invalid @enderror" id="adjustment_type" name="adjustment_type" requiredmsg="Trường này yêu cầu bắt buộc">
                                                            <option value="increase">Tăng</option>
                                                            <option value="decrease">Giảm</option>
                                                        </select>
                                                        @error('adjustment_type')
                                                            <div class="invalid-feedback">{{ $message }}</div>
                                                        @enderror
                                                    </div>
                                                    <div class="col-md-4">
                                                        <label for="adjustment_amount" class="form-label">Số lượng</label>
                                                        <div class="input-group">
                                                            <input type="number" step="0.01" min="0.01" class="form-control @error('adjustment_amount') is-invalid @enderror" id="adjustment_amount" name="adjustment_amount" requiredmsg="Trường này yêu cầu bắt buộc">
                                                            <span class="input-group-text">{{ $loThuoc->thuoc->don_vi_goc }}</span>
                                                            @error('adjustment_amount')
                                                                <div class="invalid-feedback">{{ $message }}</div>
                                                            @enderror
                                                        </div>
                                                    </div>
                                                    <div class="col-md-4">
                                                        <label for="reason" class="form-label">Lý do</label>
                                                        <input type="text" class="form-control @error('reason') is-invalid @enderror" id="reason" name="reason" requiredmsg="Trường này yêu cầu bắt buộc">
                                                        @error('reason')
                                                            <div class="invalid-feedback">{{ $message }}</div>
                                                        @enderror
                                                    </div>
                                                </div>
                                        
                                                <div class="alert alert-info small mb-3">
                                                    <i class="bi bi-info-circle me-2"></i> Khi giảm số lượng, số lượng giảm không được lớn hơn tồn kho hiện tại.
                                                    <br>Tồn kho hiện tại: <strong>{{ number_format($loThuoc->ton_kho_hien_tai, 2) }} {{ $loThuoc->thuoc->don_vi_goc }}</strong>
                                                </div>
                                        
                                                <div class="text-end">
                                                    <button type="submit" class="btn btn-primary">Thực hiện điều chỉnh</button>
                                                </div>
                                            </form>
                                        </div>

                                        <div class="history-list">
                                            <h6 class="mb-3">Lịch Sử Điều Chỉnh</h6>
                                    
                                            <!-- Hiển thị lịch sử điều chỉnh nếu có dữ liệu -->
                                            <div class="alert alert-info">
                                                <i class="bi bi-info-circle me-2"></i> Không có lịch sử điều chỉnh tồn kho.
                                            </div>
                                        </div>
                                    @endif
                                @else
                                    <div class="alert alert-warning">
                                        <i class="bi bi-exclamation-triangle me-2"></i> Không thể điều chỉnh do lô thuốc này không còn tồn kho.
                                    </div>
                                @endif
                    </div>
                </div>
            </div>
            
            <!-- Tab Chuyển kho -->
            <div class="tab-pane fade" id="transfer-tab-pane" role="tabpanel" aria-labelledby="transfer-tab" tabindex="0">
                <div class="card">
                    <div class="card-header bg-primary text-white">
                        <h6 class="mb-0">Chuyển Lô Thuốc Sang Kho Khác</h6>
                    </div>
                    <div class="card-body">
                        @if($loThuoc->ton_kho_hien_tai > 0)
                            @if(Auth::user() && Auth::user()->vai_tro === 'duoc_si')
                                <div class="alert alert-info">
                                    <i class="bi bi-info-circle me-2"></i> Bạn không có quyền chuyển kho (chỉ admin có quyền này).
                                </div>
                            @else
                                <div class="stock-action-panel">
                                    <h6 class="mb-3">Thực Hiện Chuyển Kho</h6>
                                    <form action="{{ route('lo-thuoc.transfer', $loThuoc->lo_id) }}" method="POST" id="transferForm">
                                        @csrf
                                        <input type="hidden" name="source_kho_id" value="{{ $loThuoc->kho_id }}">
                                        
                                        <div class="row mb-3">
                                            <div class="col-md-6">
                                                <label for="target_kho_id" class="form-label">Kho đích</label>
                                                <select class="form-select @error('target_kho_id') is-invalid @enderror" id="target_kho_id" name="target_kho_id" requiredmsg="Trường này yêu cầu bắt buộc">
                                                    <option value="">-- Chọn kho đích --</option>
                                                    @foreach($khos as $kho)
                                                        @if($kho->kho_id != $loThuoc->kho_id)
                                                            <option value="{{ $kho->kho_id }}">{{ $kho->ten_kho }}</option>
                                                        @endif
                                                    @endforeach
                                                </select>
                                                @error('target_kho_id')
                                                    <div class="invalid-feedback">{{ $message }}</div>
                                                @enderror
                                            </div>
                                            <div class="col-md-6">
                                                <label for="transfer_amount" class="form-label">Số lượng chuyển</label>
                                                <div class="input-group">
                                                    <input type="number" step="0.01" min="0.01" max="{{ $loThuoc->ton_kho_hien_tai }}" class="form-control @error('transfer_amount') is-invalid @enderror" id="transfer_amount" name="transfer_amount" requiredmsg="Trường này yêu cầu bắt buộc">
                                                    <span class="input-group-text">{{ $loThuoc->thuoc->don_vi_goc }}</span>
                                                    @error('transfer_amount')
                                                        <div class="invalid-feedback">{{ $message }}</div>
                                                    @enderror
                                                </div>
                                                <div class="form-text">Tồn kho hiện tại: {{ number_format($loThuoc->ton_kho_hien_tai, 2) }} {{ $loThuoc->thuoc->don_vi_goc }}</div>
                                            </div>
                                        </div>
                                        
                                        <div class="alert alert-info small mb-3">
                                            <i class="bi bi-info-circle me-2"></i> Khi chuyển toàn bộ số lượng tồn kho, lô sẽ được chuyển hoàn toàn sang kho mới.
                                            <br>Khi chuyển một phần, hệ thống sẽ tạo/cập nhật lô tương ứng ở kho đích.
                                        </div>
                                        
                                        <div class="text-end">
                                            <button type="submit" class="btn btn-primary">Thực hiện chuyển kho</button>
                                        </div>
                                    </form>
                                </div>

                                <div class="history-list">
                                    <h6 class="mb-3">Lịch Sử Chuyển Kho</h6>
                                    
                                    <!-- Hiển thị lịch sử chuyển kho nếu có dữ liệu -->
                                    <div class="alert alert-info">
                                        <i class="bi bi-info-circle me-2"></i> Không có lịch sử chuyển kho.
                                    </div>
                                </div>
                            @endif
                        @else
                            <div class="alert alert-warning">
                                <i class="bi bi-exclamation-triangle me-2"></i> Không thể chuyển kho do lô thuốc này không còn tồn kho.
                            </div>
                        @endif
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
<!-- Page data container to safely expose PHP values to JS -->
<div id="page-data-lo" data-max-amount="{{ $loThuoc->ton_kho_hien_tai }}" style="display:none"></div>
@endsection

@section('scripts')
<script>
    $(document).ready(function() {
        // Hiển thị tab được chọn khi tải lại trang
        var hash = window.location.hash;
        if (hash) {
            $('#lotTab a[href="' + hash + '"]').tab('show');
        }
        
        // Cập nhật hash URL khi chuyển tab
        $('#lotTab a').on('click', function(e) {
            window.location.hash = $(this).attr('href');
        });
        
        // Kiểm tra giá trị số lượng điều chỉnh
        $('#adjustment_type, #adjustment_amount').on('change input', function() {
            var type = $('#adjustment_type').val();
            var amount = parseFloat($('#adjustment_amount').val()) || 0;
            var maxAmount = parseFloat($('#page-data-lo').data('max-amount')) || 0;
            
            if (type === 'decrease' && amount > maxAmount) {
                $('#adjustment_amount').val(maxAmount);
            }
        });
        
        // Kiểm tra số lượng chuyển kho
        $('#transfer_amount').on('input', function() {
            var amount = parseFloat($(this).val()) || 0;
            var maxAmount = parseFloat($('#page-data-lo').data('max-amount')) || 0;
            
            if (amount > maxAmount) {
                $(this).val(maxAmount);
            }
        });
    });
    
    // Hàm hiển thị tab lịch sử xuất
    function showExportHistory() {
        $('#export-tab').tab('show');
        
        // Cuộn trang đến vị trí tab sau khi chuyển tab
        setTimeout(function() {
            $('html, body').animate({
                scrollTop: $("#export-tab").offset().top - 100
            }, 500);
        }, 300);
    }
</script>
@endsection
