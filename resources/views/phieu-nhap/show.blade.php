@extends('layouts.app')

@section('title', 'Chi Tiết Phiếu Nhập - Hệ Thống Quản Lý Hiệu Thuốc')

@section('page-title', 'Chi Tiết Phiếu Nhập')

@section('styles')
<style>
    .status-badge {
        font-size: 0.85rem;
        font-weight: 500;
    }
    .status-draft {
        background-color: #6c757d;
    }
    .status-completed {
        background-color: #28a745;
    }
    .status-cancelled {
        background-color: #dc3545;
    }
    
    .detail-section {
        background-color: #f8f9fa;
        border-radius: 0.25rem;
        padding: 1rem;
        margin-bottom: 1rem;
    }
    
    .detail-item {
        display: flex;
        margin-bottom: 0.5rem;
    }
    
    .detail-label {
        width: 140px;
        font-weight: 500;
    }
    
    .detail-value {
        flex: 1;
    }
    
    .summary-item {
        display: flex;
        justify-content: space-between;
        padding: 0.5rem 0;
    }
    
    .summary-total {
        font-weight: bold;
        font-size: 1.1rem;
    }
    
    .line-item {
        border-bottom: 1px solid #dee2e6;
        padding: 0.75rem 0;
    }
    
    .line-item:last-child {
        border-bottom: none;
    }
    
    .print-section {
        max-width: 800px;
        margin: 0 auto;
    }
    
    @media print {
        body * {
            visibility: hidden;
        }
        .print-section, .print-section * {
            visibility: visible;
        }
        .print-section {
            position: absolute;
            left: 0;
            top: 0;
            width: 100%;
            height: 100%;
        }
        .no-print {
            display: none !important;
        }
    }
</style>
@endsection

@section('content')
<div class="row mb-3">
    <div class="col-md-6">
        <a href="{{ route('phieu-nhap.index') }}" class="btn btn-secondary no-print">
            <i class="bi bi-arrow-left me-1"></i> Quay Lại
        </a>
    </div>
    <div class="col-md-6 text-end">
        <button type="button" class="btn btn-primary no-print" onclick="window.print()">
            <i class="bi bi-printer me-1"></i> In Phiếu Nhập
        </button>
    </div>
</div>

<div class="print-section">
    <div class="card">
        <div class="card-body">
            <div class="text-center mb-4">
                <h4 class="mb-0">PHIẾU NHẬP KHO</h4>
                <p class="small text-muted mb-0">Mã phiếu: {{ $phieuNhap->ma_phieu }}</p>
                <p class="small text-muted">Ngày tạo: {{ \Carbon\Carbon::parse($phieuNhap->ngay_nhap)->format('d/m/Y') }}</p>
            </div>
            
            <div class="row">
                <div class="col-md-6">
                    <div class="detail-section">
                        <h6 class="mb-3">Thông Tin Nhà Cung Cấp</h6>
                        <div class="detail-item">
                            <div class="detail-label">Nhà cung cấp:</div>
                            <div class="detail-value">{{ $phieuNhap->nhaCungCap->ten_ncc }}</div>
                        </div>
                        <div class="detail-item">
                            <div class="detail-label">Địa chỉ:</div>
                            <div class="detail-value">{{ $phieuNhap->nhaCungCap->dia_chi }}</div>
                        </div>
                        <div class="detail-item">
                            <div class="detail-label">Mã số thuế:</div>
                            <div class="detail-value">{{ $phieuNhap->nhaCungCap->ma_so_thue ?? 'Không có' }}</div>
                        </div>
                        <div class="detail-item">
                            <div class="detail-label">Số điện thoại:</div>
                            <div class="detail-value">{{ $phieuNhap->nhaCungCap->sdt ?? 'Không có' }}</div>
                        </div>
                    </div>
                </div>
                <div class="col-md-6">
                    <div class="detail-section">
                        <h6 class="mb-3">Thông Tin Phiếu Nhập</h6>
                        <div class="detail-item">
                            <div class="detail-label">Mã phiếu:</div>
                            <div class="detail-value">{{ $phieuNhap->ma_phieu }}</div>
                        </div>
                        <div class="detail-item">
                            <div class="detail-label">Ngày nhập:</div>
                            <div class="detail-value">{{ \Carbon\Carbon::parse($phieuNhap->ngay_nhap)->format('d/m/Y') }}</div>
                        </div>
                        <div class="detail-item">
                            <div class="detail-label">Ngày chứng từ:</div>
                            <div class="detail-value">{{ \Carbon\Carbon::parse($phieuNhap->ngay_chung_tu)->format('d/m/Y') }}</div>
                        </div>
                        <div class="detail-item">
                            <div class="detail-label">Người tạo:</div>
                            <div class="detail-value">{{ $phieuNhap->nguoiDung->ho_ten ?? 'N/A' }}</div>
                        </div>
                        <div class="detail-item">
                            <div class="detail-label">Trạng thái:</div>
                            <div class="detail-value">
                                @if($phieuNhap->trang_thai === 'hoàn_thành')
                                    <span class="badge status-badge status-completed">Hoàn thành</span>
                                @elseif($phieuNhap->trang_thai === 'nháp')
                                    <span class="badge status-badge status-draft">Nháp</span>
                                @else
                                    <span class="badge status-badge status-cancelled">Đã hủy</span>
                                @endif
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            
            <h6 class="mb-3">Chi Tiết Phiếu Nhập</h6>
            <div class="table-responsive">
                <table class="table table-bordered table-striped">
                    <thead class="table-light">
                        <tr>
                            <th style="width: 5%">#</th>
                            <th style="width: 35%">Thuốc</th>
                            <th style="width: 10%">Số lô</th>
                            <th style="width: 15%">HSD</th>
                            <th style="width: 10%">SL</th>
                            <th style="width: 12%">Đơn giá</th>
                            <th style="width: 13%">Thành tiền</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($phieuNhap->chiTietLoNhap as $index => $chiTiet)
                        <tr>
                            <td class="text-center">{{ $index + 1 }}</td>
                            <td>
                                <strong>{{ $chiTiet->loThuoc->thuoc->ten_thuoc }}</strong>
                                @if($chiTiet->loThuoc->ghi_chu)
                                <div class="small text-muted">Ghi chú: {{ $chiTiet->loThuoc->ghi_chu }}</div>
                                @endif
                            </td>
                            <td>{{ $chiTiet->loThuoc->ma_lo ?? $chiTiet->loThuoc->so_lo_nha_san_xuat ?? 'N/A' }}</td>
                            <td>{{ \Carbon\Carbon::parse($chiTiet->han_su_dung)->format('d/m/Y') }}</td>
                            <td>{{ $chiTiet->so_luong }} {{ $chiTiet->don_vi }}</td>
                            <td class="text-end">{{ number_format($chiTiet->gia_nhap) }}</td>
                            <td class="text-end">{{ number_format($chiTiet->thanh_tien) }}</td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
            
            <div class="row mt-4">
                <div class="col-md-6">
                    <div class="detail-section">
                        <h6 class="mb-3">Ghi Chú</h6>
                        <p class="mb-0">{{ $phieuNhap->ghi_chu ?: 'Không có ghi chú' }}</p>
                    </div>
                </div>
                <div class="col-md-6">
                    <div class="detail-section">
                        <h6 class="mb-3">Tổng Kết Chi Phí</h6>
                        <div class="summary-item">
                            <div>Tổng tiền hàng:</div>
                            <div>{{ number_format($phieuNhap->tong_tien) }} VNĐ</div>
                        </div>
                        <div class="summary-item">
                            <div>Thuế VAT:</div>
                            <div>{{ number_format($phieuNhap->vat) }} VNĐ</div>
                        </div>
                        <div class="summary-item summary-total">
                            <div>Tổng cộng:</div>
                            <div>{{ number_format($phieuNhap->tong_cong) }} VNĐ</div>
                        </div>
                    </div>
                </div>
            </div>
            
            <div class="row mt-5 print-only">
                <div class="col-md-4 text-center">
                    <p><strong>Người lập phiếu</strong></p>
                    <p class="mt-5">(Ký và ghi rõ họ tên)</p>
                </div>
                <div class="col-md-4 text-center">
                    <p><strong>Người giao hàng</strong></p>
                    <p class="mt-5">(Ký và ghi rõ họ tên)</p>
                </div>
                <div class="col-md-4 text-center">
                    <p><strong>Thủ kho</strong></p>
                    <p class="mt-5">(Ký và ghi rõ họ tên)</p>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@section('scripts')
<script>
    $(document).ready(function() {
        // Không có xử lý JavaScript đặc biệt cho trang chi tiết
    });
</script>
@endsection
