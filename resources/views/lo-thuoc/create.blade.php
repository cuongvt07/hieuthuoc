@extends('layouts.app')

@section('title', 'Thêm Lô Thuốc Mới - Hệ Thống Quản Lý Hiệu Thuốc')

@section('page-title', 'Thêm Lô Thuốc Mới')

@section('content')
<div class="row mb-3">
    <div class="col-12">
        <a href="{{ route('lo-thuoc.index') }}" class="btn btn-secondary">
            <i class="bi bi-arrow-left me-1"></i> Quay Lại
        </a>
    </div>
</div>

<div class="row">
    <div class="col-md-12">
        <div class="card">
            <div class="card-header bg-primary text-white">
                <h6 class="mb-0">Thêm Lô Thuốc Mới</h6>
            </div>
            <div class="card-body">
                <div class="alert alert-info">
                    <i class="bi bi-info-circle me-2"></i> Lưu ý: Cách thông thường để tạo lô thuốc là thông qua phiếu nhập. Chức năng này chỉ dành cho trường hợp đặc biệt khi cần thêm lô thuốc trực tiếp mà không thông qua phiếu nhập.
                </div>
                
                <form action="{{ route('lo-thuoc.store') }}" method="POST">
                    @csrf
                    
                    <div class="row mb-3">
                        <div class="col-md-6">
                            <h5 class="card-title">Thông Tin Cơ Bản</h5>
                            <p class="text-muted small">Nhập thông tin cơ bản của lô thuốc</p>
                            
                            <div class="mb-3">
                                <label for="thuoc_id" class="form-label">Thuốc <span class="text-danger">*</span></label>
                                <select class="form-select @error('thuoc_id') is-invalid @enderror" id="thuoc_id" name="thuoc_id" requiredmsg="Trường này yêu cầu bắt buộc">
                                    <option value="">-- Chọn thuốc --</option>
                                    @foreach($thuocs as $thuoc)
                                        <option value="{{ $thuoc->thuoc_id }}" {{ old('thuoc_id') == $thuoc->thuoc_id ? 'selected' : '' }} 
                                                data-don-vi-goc="{{ $thuoc->don_vi_goc }}">
                                            {{ $thuoc->ten_thuoc }} - {{ $thuoc->hoat_chat }} - {{ $thuoc->ham_luong }}
                                        </option>
                                    @endforeach
                                </select>
                                @error('thuoc_id')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                            
                            <div class="mb-3">
                                <label for="ma_lo" class="form-label">Mã lô</label>
                                <input type="text" class="form-control @error('ma_lo') is-invalid @enderror" id="ma_lo" name="ma_lo" value="{{ old('ma_lo') }}">
                                @error('ma_lo')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                                <div class="form-text">Để trống nếu sử dụng mã tự động</div>
                            </div>
                            
                            <div class="mb-3">
                                <label for="so_lo_nha_san_xuat" class="form-label">Số lô nhà sản xuất</label>
                                <input type="text" class="form-control @error('so_lo_nha_san_xuat') is-invalid @enderror" id="so_lo_nha_san_xuat" name="so_lo_nha_san_xuat" value="{{ old('so_lo_nha_san_xuat') }}">
                                @error('so_lo_nha_san_xuat')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                            
                            <div class="mb-3">
                                <label for="ngay_san_xuat" class="form-label">Ngày sản xuất</label>
                                <input type="date" class="form-control @error('ngay_san_xuat') is-invalid @enderror" id="ngay_san_xuat" name="ngay_san_xuat" value="{{ old('ngay_san_xuat') }}">
                                @error('ngay_san_xuat')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                            
                            <div class="mb-3">
                                <label for="han_su_dung" class="form-label">Hạn sử dụng <span class="text-danger">*</span></label>
                                <input type="date" class="form-control @error('han_su_dung') is-invalid @enderror" id="han_su_dung" name="han_su_dung" value="{{ old('han_su_dung') }}" requiredmsg="Trường này yêu cầu bắt buộc">
                                @error('han_su_dung')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>
                        
                        <div class="col-md-6">
                            <h5 class="card-title">Thông Tin Bổ Sung</h5>
                            <p class="text-muted small">Thông tin kho và số lượng ban đầu</p>
                            
                            <div class="mb-3">
                                <label for="kho_id" class="form-label">Kho lưu trữ <span class="text-danger">*</span></label>
                                <select class="form-select @error('kho_id') is-invalid @enderror" id="kho_id" name="kho_id" requiredmsg="Trường này yêu cầu bắt buộc">
                                    <option value="">-- Chọn kho --</option>
                                    @foreach($khos as $kho)
                                        <option value="{{ $kho->kho_id }}" {{ old('kho_id') == $kho->kho_id ? 'selected' : '' }}>
                                            {{ $kho->ten_kho }}
                                        </option>
                                    @endforeach
                                </select>
                                @error('kho_id')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                            
                            <div class="mb-3">
                                <label for="so_luong" class="form-label">Số lượng ban đầu <span class="text-danger">*</span></label>
                                <div class="input-group">
                                    <input type="number" step="0.01" min="0.01" class="form-control @error('so_luong') is-invalid @enderror" id="so_luong" name="so_luong" value="{{ old('so_luong') }}" requiredmsg="Trường này yêu cầu bắt buộc">
                                    <span class="input-group-text" id="don-vi-text">Đơn vị</span>
                                </div>
                                @error('so_luong')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                            
                            <div class="mb-3">
                                <label for="gia_nhap" class="form-label">Giá nhập <span class="text-danger">*</span></label>
                                <div class="input-group">
                                    <input type="number" step="1" min="0" class="form-control @error('gia_nhap') is-invalid @enderror" id="gia_nhap" name="gia_nhap" value="{{ old('gia_nhap') }}" requiredmsg="Trường này yêu cầu bắt buộc">
                                    <span class="input-group-text">VNĐ</span>
                                </div>
                                @error('gia_nhap')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                            
                            <div class="mb-3">
                                <label for="ghi_chu" class="form-label">Ghi chú</label>
                                <textarea class="form-control @error('ghi_chu') is-invalid @enderror" id="ghi_chu" name="ghi_chu" rows="4">{{ old('ghi_chu') }}</textarea>
                                @error('ghi_chu')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>
                    </div>
                    
                    <div class="row">
                        <div class="col-12 text-center">
                            <button type="submit" class="btn btn-primary px-4">
                                <i class="bi bi-save me-1"></i> Tạo Lô Thuốc
                            </button>
                            <a href="{{ route('lo-thuoc.index') }}" class="btn btn-secondary px-4 ms-2">
                                <i class="bi bi-x-circle me-1"></i> Hủy
                            </a>
                        </div>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>
@endsection

@section('scripts')
<script>
    $(document).ready(function() {
        // Cập nhật đơn vị theo thuốc được chọn
        $('#thuoc_id').on('change', function() {
            var selectedOption = $(this).find('option:selected');
            var donViGoc = selectedOption.data('don-vi-goc') || 'Đơn vị';
            $('#don-vi-text').text(donViGoc);
        });
        
        // Hiển thị đơn vị khi trang được tải
        var selectedOption = $('#thuoc_id').find('option:selected');
        var donViGoc = selectedOption.data('don-vi-goc') || 'Đơn vị';
        $('#don-vi-text').text(donViGoc);
        
        // Hiển thị cảnh báo khi ngày sản xuất sau hạn sử dụng
        $('#ngay_san_xuat, #han_su_dung').on('change', function() {
            var ngaySX = new Date($('#ngay_san_xuat').val());
            var hanSD = new Date($('#han_su_dung').val());
            
            if (ngaySX > hanSD) {
                alert('Cảnh báo: Ngày sản xuất không thể sau hạn sử dụng!');
            }
        });
    });
</script>
@endsection
