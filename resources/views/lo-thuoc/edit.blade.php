@extends('layouts.app')

@section('title', 'Hủy Tồn Hết Hạn - Hệ Thống Quản Lý Hiệu Thuốc')

@section('page-title', 'Hủy Tồn Hết Hạn')

@section('content')
<div class="row mb-3">
    <div class="col-12">
        <a href="{{ route('lo-thuoc.show', $loThuoc->lo_id) }}" class="btn btn-secondary">
            <i class="bi bi-arrow-left me-1"></i> Quay Lại
        </a>
    </div>
</div>

<div class="row">
    <div class="col-md-12">
        <div class="card">
            <div class="card-header bg-danger text-white">
                <h6 class="mb-0">Hủy Tồn Đối Với Lô Thuốc Hết Hạn</h6>
            </div>
            <div class="card-body">
                <form action="{{ route('lo-thuoc.processDispose', $loThuoc->lo_id) }}" method="POST">
                    @csrf
                    
                    <div class="alert alert-warning mb-4">
                        <i class="bi bi-exclamation-triangle me-2"></i> 
                        <strong>Cảnh báo:</strong> Hành động này sẽ hủy toàn bộ số lượng tồn kho hiện tại của lô thuốc. Vui lòng kiểm tra kỹ thông tin trước khi thực hiện.
                    </div>

                    <div class="row mb-3">
                        <div class="col-md-6">
                            <h5 class="card-title">Thông Tin Lô Thuốc</h5>
                            <div class="mb-3">
                                <label for="ma_lo" class="form-label">Mã lô</label>
                                <input type="text" class="form-control bg-light" id="ma_lo" value="{{ $loThuoc->ma_lo }}" readonly>
                            </div>
                            
                            <div class="mb-3">
                                <label for="ten_thuoc" class="form-label">Tên thuốc</label>
                                <input type="text" class="form-control bg-light" id="ten_thuoc" value="{{ $loThuoc->thuoc->ten_thuoc }}" readonly>
                            </div>
                            
                            <div class="mb-3">
                                <label for="so_lo_nha_san_xuat" class="form-label">Số lô nhà sản xuất</label>
                                <input type="text" class="form-control bg-light" id="so_lo_nha_san_xuat" value="{{ $loThuoc->so_lo_nha_san_xuat ?? 'Không có' }}" readonly>
                            </div>
                            
                            <div class="mb-3">
                                <label for="ngay_san_xuat" class="form-label">Ngày sản xuất</label>
                                <input type="text" class="form-control bg-light" id="ngay_san_xuat" value="{{ $loThuoc->ngay_san_xuat ? \Carbon\Carbon::parse($loThuoc->ngay_san_xuat)->format('d/m/Y') : 'Không có' }}" readonly>
                            </div>
                            
                            <div class="mb-3">
                                <label for="han_su_dung" class="form-label">Hạn sử dụng</label>
                                <input type="text" class="form-control bg-light" id="han_su_dung" value="{{ \Carbon\Carbon::parse($loThuoc->han_su_dung)->format('d/m/Y') }}" readonly>
                            </div>

                            <div class="mb-3">
                                <label for="kho" class="form-label">Kho lưu trữ</label>
                                <input type="text" class="form-control bg-light" id="kho" value="{{ $loThuoc->kho->ten_kho ?? 'Không xác định' }}" readonly>
                            </div>
                        </div>
                        
                        <div class="col-md-6">
                            <h5 class="card-title">Thông Tin Hủy Tồn</h5>
                            <div class="mb-3">
                                <label for="so_luong_huy" class="form-label">Số lượng tồn cần hủy <span class="text-danger">*</span></label>
                                <input type="number" step="0.01" class="form-control bg-light" id="so_luong_huy" name="so_luong_huy" value="{{ $loThuoc->ton_kho_hien_tai }}" readonly>
                                <div class="form-text">Đơn vị: {{ $loThuoc->thuoc->don_vi_goc }}</div>
                            </div>
                            
                            <div class="mb-3">
                                <label for="ngay_huy" class="form-label">Ngày hủy <span class="text-danger">*</span></label>
                                <input type="date" class="form-control bg-light @error('ngay_huy') is-invalid @enderror" id="ngay_huy" name="ngay_huy" value="{{ old('ngay_huy', date('Y-m-d')) }}" required>
                                @error('ngay_huy')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                            
                            <div class="mb-3">
                                <label for="ly_do_huy" class="form-label">Lý do hủy <span class="text-danger">*</span></label>
                                <textarea class="form-control @error('ly_do_huy') is-invalid @enderror" id="ly_do_huy" name="ly_do_huy" rows="4" required placeholder="Nhập lý do hủy tồn...">{{ old('ly_do_huy', 'Hủy tồn do thuốc hết hạn sử dụng') }}</textarea>
                                @error('ly_do_huy')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                            
                            @if($loThuoc->ghi_chu)
                            <div class="mb-3">
                                <label for="ghi_chu" class="form-label">Ghi chú lô thuốc</label>
                                <textarea class="form-control bg-light" id="ghi_chu" rows="3" readonly>{{ $loThuoc->ghi_chu }}</textarea>
                            </div>
                            @endif
                        </div>
                    </div>
                    
                    <div class="row">
                        <div class="col-12 text-center">
                            <button type="submit" class="btn btn-danger px-4" onclick="return confirm('Bạn có chắc chắn muốn hủy tồn lô thuốc này không? Hành động này không thể hoàn tác!')">
                                <i class="bi bi-trash me-1"></i> Hủy Tồn
                            </button>
                            <a href="{{ route('lo-thuoc.show', $loThuoc->lo_id) }}" class="btn btn-secondary px-4 ms-2">
                                <i class="bi bi-x-circle me-1"></i> Hủy Bỏ
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
        // Đặt ngày hủy tối đa là hôm nay
        var today = new Date().toISOString().split('T')[0];
        $('#ngay_huy').attr('max', today);
    });
</script>
@endsection
