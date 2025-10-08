@extends('layouts.app')

@section('title', 'Chi Tiết Phiếu Nhập - Hiệu Thuốc An Tây')

@section('page-title', 'Chi Tiết Phiếu Nhập')

@section('styles')
<style>
    body {
        font-family: "Times New Roman", serif;
    }

    .invoice-container {
        max-width: 900px;
        margin: auto;
        background: #fff;
        padding: 30px;
        border: 1px solid #dee2e6;
        border-radius: 8px;
    }

    .invoice-header {
        border-bottom: 2px solid #000;
        margin-bottom: 20px;
        padding-bottom: 10px;
    }

    .invoice-header h4 {
        font-weight: bold;
        text-transform: uppercase;
    }

    .invoice-meta {
        font-size: 14px;
    }

    .info-block {
        margin-bottom: 20px;
    }

    .info-block h6 {
        font-weight: bold;
        margin-bottom: 10px;
        text-transform: uppercase;
    }

    .table th, .table td {
        vertical-align: middle;
    }

    .summary-box {
        background: #f8f9fa;
        border-radius: 6px;
        padding: 15px;
    }

    .summary-box .row div {
        margin-bottom: 6px;
    }

    .signature-block {
        margin-top: 50px;
    }

    .signature-block .col {
        text-align: center;
        font-size: 14px;
    }

    .signature-block .col p {
        margin-bottom: 80px;
    }

    @media print {
        .no-print {
            display: none !important;
        }
        .invoice-container {
            border: none;
        }
    }
</style>
@endsection

@section('content')
<div class="row mb-3 no-print">
    <div class="col-md-6">
        <a href="{{ route('phieu-nhap.index') }}" class="btn btn-secondary">
            <i class="bi bi-arrow-left me-1"></i> Quay Lại
        </a>
    </div>
    <div class="col-md-6 text-end">
        <button type="button" class="btn btn-primary" id="print-invoice-btn">
            <i class="bi bi-printer me-1"></i> In Phiếu Nhập
        </button>
    </div>
</div>

<div class="invoice-container" id="invoice-container">
    {{-- Header --}}
    <div class="invoice-header text-center">
        <h4>PHIẾU NHẬP</h4>
        <div class="invoice-meta row text-center fw-bold fs-5">
            <div class="col-md-4">
                Mã phiếu: {{ $phieuNhap->ma_phieu }}
            </div>
            <div class="col-md-4">
                Ngày nhập: {{ \Carbon\Carbon::parse($phieuNhap->ngay_nhap)->format('d/m/Y') }}
            </div>
            <div class="col-md-4">
                Ngày chứng từ: {{ \Carbon\Carbon::parse($phieuNhap->ngay_chung_tu)->format('d/m/Y') }}
            </div>
        </div>
    </div>

    {{-- Thông tin NCC + Phiếu --}}
    <div class="row">
        <div class="col-md-6">
            <div class="info-block">
                <h6>Nhà Cung Cấp</h6>
                <p><strong>{{ $phieuNhap->nhaCungCap->ten_ncc }}</strong></p>
                <p>Địa chỉ: {{ $phieuNhap->nhaCungCap->dia_chi }}</p>
                <p>MST: {{ $phieuNhap->nhaCungCap->ma_so_thue ?? 'Không có' }}</p>
                <p>SĐT: {{ $phieuNhap->nhaCungCap->sdt ?? 'Không có' }}</p>
            </div>
        </div>
        <div class="col-md-6">
            <div class="info-block">
                <h6>Thông Tin Phiếu</h6>
                <p>Người tạo: {{ $phieuNhap->nguoiDung->ho_ten ?? 'N/A' }}</p>
                <p>Trạng thái: 
                    @if($phieuNhap->trang_thai === 'hoan_tat')
                        <span class="badge bg-success">Hoàn thành</span>
                    @elseif($phieuNhap->trang_thai === 'cho_xu_ly')
                        <span class="badge bg-secondary">Chờ xử lý</span>
                    @else
                        <span class="badge bg-danger">Đã hủy</span>
                    @endif
                </p>
            </div>
        </div>
    </div>

    {{-- Bảng chi tiết --}}
    <h6 class="mb-2">Chi Tiết Lô Hàng : </h6>
    <div class="table-responsive">
        <table class="table table-bordered text-center">
            <thead class="table-light">
                <tr>
                    <th>STT</th>
                    <th>Tên thuốc</th>
                    <th>Số lô</th>
                    <th>HSD</th>
                    <th>SL</th>
                    <th>Đơn giá (VNĐ)</th>
                    <th>Thành tiền (VNĐ)</th>
                </tr>
            </thead>
            <tbody>
                @foreach($phieuNhap->chiTietLoNhaps as $index => $chiTiet)
                <tr>
                    <td>{{ $index + 1 }}</td>
                    <td class="text-start">
                        <strong>{{ $chiTiet->loThuoc->thuoc->ten_thuoc }}</strong><br>
                        @if($chiTiet->loThuoc->ghi_chu)
                            <small class="text-muted">({{ $chiTiet->loThuoc->ghi_chu }})</small>
                        @endif
                    </td>
                    <td>{{ $chiTiet->loThuoc->ma_lo ?? $chiTiet->loThuoc->so_lo_nha_san_xuat ?? 'N/A' }}</td>
                    <td>{{ \Carbon\Carbon::parse($chiTiet->han_su_dung)->format('d/m/Y') }}</td>
                    <td>{{ number_format(10, 2) }}</td>
                    <td class="text-end">{{ number_format($chiTiet->gia_nhap) }}</td>
                    <td class="text-end">{{ number_format($chiTiet->thanh_tien) }}</td>
                </tr>
                @endforeach
            </tbody>
        </table>
    </div>

    {{-- Tổng kết --}}
    <div class="row mt-4">
        <div class="col-md-6">
            <div class="info-block">
                <h6>Ghi Chú</h6>
                <p>{{ $phieuNhap->ghi_chu ?: 'Không có ghi chú' }}</p>
            </div>
        </div>
        <div class="col-md-6">
            <div class="summary-box">
                <div class="row">
                    <div class="col-6 text-start">Tổng tiền hàng:</div>
                    <div class="col-6 text-end">{{ number_format($phieuNhap->tong_tien) }} VNĐ</div>
                </div>
                <div class="row">
                    <div class="col-6 text-start">Thuế VAT:</div>
                    <div class="col-6 text-end">{{ number_format($phieuNhap->vat) }} VNĐ</div>
                </div>
                <div class="row fw-bold border-top pt-2">
                    <div class="col-6 text-start">Tổng cộng:</div>
                    <div class="col-6 text-end">{{ number_format($phieuNhap->tong_cong) }} VNĐ</div>
                </div>
            </div>
        </div>
    </div>

    {{-- Chữ ký --}}
    <div class="row signature-block">
        <div class="col text-center">
            <p><strong>Người lập phiếu</strong></p>
            <span>{{ $phieuNhap->nguoiDung->ho_ten ?? 'N/A' }}</span>
        </div>
    </div>
</div>
<script>
    document.getElementById('print-invoice-btn').addEventListener('click', function() {
        var printContents = document.getElementById('invoice-container').innerHTML;
        var originalContents = document.body.innerHTML;
        document.body.innerHTML = printContents;
        window.print();
        document.body.innerHTML = originalContents;
        location.reload();
    });
</script>
@endsection
