@extends('layouts.app')

@section('title', 'Chỉnh Sửa Phiếu Nhập - Hệ Thống Quản Lý Hiệu Thuốc')

@section('page-title', 'Chỉnh Sửa Phiếu Nhập')

@section('styles')
<style>
    .required-field::after {
        content: " *";
        color: red;
    }
</style>
@endsection

@section('content')
<div class="card">
    <div class="card-header bg-primary text-white">
        <h5 class="mb-0">Chỉnh Sửa Thông Tin Phiếu Nhập</h5>
    </div>
    <div class="card-body">
        <form action="{{ route('phieu-nhap.update', $phieuNhap->phieu_id) }}" method="POST" id="editPhieuNhapForm">
            @csrf
            @method('PUT')
            
            <div class="row mb-3">
                <div class="col-md-6">
                    <label for="ma_phieu" class="form-label required-field">Mã phiếu</label>
                    <input type="text" class="form-control @error('ma_phieu') is-invalid @enderror" id="ma_phieu" name="ma_phieu" value="{{ old('ma_phieu', $phieuNhap->ma_phieu) }}" requiredmsg="Trường này yêu cầu bắt buộc">
                    @error('ma_phieu')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>
                <div class="col-md-6">
                    <label for="ncc_id" class="form-label required-field">Nhà cung cấp</label>
                    <select class="form-select @error('ncc_id') is-invalid @enderror" id="ncc_id" name="ncc_id" requiredmsg="Trường này yêu cầu bắt buộc">
                        <option value="">-- Chọn nhà cung cấp --</option>
                        @foreach($nhaCungCaps as $ncc)
                            <option value="{{ $ncc->ncc_id }}" {{ old('ncc_id', $phieuNhap->ncc_id) == $ncc->ncc_id ? 'selected' : '' }}>{{ $ncc->ten_ncc }}</option>
                        @endforeach
                    </select>
                    @error('ncc_id')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>
            </div>
            
            <div class="row mb-3">
                <div class="col-md-6">
                    <label for="ngay_nhap" class="form-label required-field">Ngày nhập</label>
                    <input type="date" class="form-control @error('ngay_nhap') is-invalid @enderror" id="ngay_nhap" name="ngay_nhap" value="{{ old('ngay_nhap', $phieuNhap->ngay_nhap) }}" requiredmsg="Trường này yêu cầu bắt buộc">
                    @error('ngay_nhap')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>
                <div class="col-md-6">
                    <label for="ngay_chung_tu" class="form-label required-field">Ngày chứng từ</label>
                    <input type="date" class="form-control @error('ngay_chung_tu') is-invalid @enderror" id="ngay_chung_tu" name="ngay_chung_tu" value="{{ old('ngay_chung_tu', $phieuNhap->ngay_chung_tu) }}" requiredmsg="Trường này yêu cầu bắt buộc">
                    @error('ngay_chung_tu')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>
            </div>
            
            <div class="mb-3">
                <label for="ghi_chu" class="form-label">Ghi chú</label>
                <textarea class="form-control" id="ghi_chu" name="ghi_chu" rows="3">{{ old('ghi_chu', $phieuNhap->ghi_chu) }}</textarea>
            </div>
            
            <div class="alert alert-info">
                <i class="bi bi-info-circle me-2"></i> Chỉ có thể chỉnh sửa thông tin cơ bản của phiếu nhập.
                Chi tiết lô nhập không thể chỉnh sửa sau khi đã tạo.
            </div>
            
            <div class="d-flex justify-content-between mt-4">
                <a href="{{ route('phieu-nhap.show', $phieuNhap->phieu_id) }}" class="btn btn-secondary">
                    <i class="bi bi-arrow-left me-1"></i> Quay Lại
                </a>
                <button type="submit" class="btn btn-primary">
                    <i class="bi bi-save me-1"></i> Lưu Thay Đổi
                </button>
            </div>
        </form>
    </div>
</div>
@endsection

@section('scripts')
<script>
    $(document).ready(function() {
        // Không cần xử lý JavaScript đặc biệt cho trang này
    });
</script>
@endsection
