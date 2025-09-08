@extends('layouts.app')

@section('title', 'Tạo Phiếu Nhập Mới - Hệ Thống Quản Lý Hiệu Thuốc')

@section('page-title', 'Tạo Phiếu Nhập Mới')

@section('styles')
<style>
    .required-field::after {
        content: " *";
        color: red;
    }
    
    .detail-row {
        transition: all 0.3s;
    }
    
    .detail-row:hover {
        background-color: #f8f9fa;
    }
    
    .product-image {
        max-height: 40px;
        border: 1px solid #ddd;
        border-radius: 3px;
    }
    
    .summary-section {
        background-color: #f8f9fa;
        border-radius: 0.25rem;
        padding: 1rem;
    }
    
    .summary-item {
        display: flex;
        justify-content: space-between;
        padding: 0.5rem 0;
        border-bottom: 1px solid #dee2e6;
    }
    
    .summary-item:last-child {
        border-bottom: none;
    }
    
    .summary-total {
        font-weight: bold;
        font-size: 1.1rem;
    }
    
    .lot-info {
        background-color: #e8f4ff;
        border-radius: 4px;
        padding: 10px;
        margin-top: 10px;
    }
    
    .lot-info h6 {
        margin-bottom: 10px;
        border-bottom: 1px solid #cce5ff;
        padding-bottom: 5px;
    }
    
    .inventory-item {
        padding: 8px;
        margin-bottom: 8px;
        border-left: 3px solid #36b9cc;
        background-color: #f8f9fa;
    }
    
    .expiry-warning {
        border-left-color: #f6c23e;
    }
    
    .expiry-danger {
        border-left-color: #e74a3b;
    }
    
    .btn-remove {
        display: flex;
        align-items: center;
        justify-content: center;
        width: 28px;
        height: 28px;
        padding: 0;
    }
</style>
@endsection

@section('content')
<form id="createPhieuNhapForm" action="{{ route('phieu-nhap.store') }}" method="POST">
    @csrf
    
    <div class="row">
        <!-- Thông tin phiếu nhập -->
        <div class="col-md-4">
            <div class="card mb-4">
                <div class="card-header bg-primary text-white">
                    <h6 class="mb-0">Thông Tin Phiếu Nhập</h6>
                </div>
                <div class="card-body">
                    <div class="mb-3">
                        <label for="ma_phieu" class="form-label required-field">Mã phiếu</label>
                        <input type="text" class="form-control @error('ma_phieu') is-invalid @enderror" id="ma_phieu" name="ma_phieu" value="{{ old('ma_phieu', $maPhieu) }}" required>
                        @error('ma_phieu')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>
                    
                    <div class="mb-3">
                        <label for="ncc_id" class="form-label required-field">Nhà cung cấp</label>
                        <select class="form-select @error('ncc_id') is-invalid @enderror" id="ncc_id" name="ncc_id" required>
                            <option value="">-- Chọn nhà cung cấp --</option>
                            @foreach($nhaCungCaps as $ncc)
                                <option value="{{ $ncc->ncc_id }}" {{ old('ncc_id') == $ncc->ncc_id ? 'selected' : '' }}>{{ $ncc->ten_ncc }}</option>
                            @endforeach
                        </select>
                        @error('ncc_id')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>
                    
                    <div class="row mb-3">
                        <div class="col-md-6">
                            <label for="ngay_nhap" class="form-label required-field">Ngày nhập</label>
                            <input type="date" class="form-control @error('ngay_nhap') is-invalid @enderror" id="ngay_nhap" name="ngay_nhap" value="{{ old('ngay_nhap', date('Y-m-d')) }}" required>
                            @error('ngay_nhap')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                        <div class="col-md-6">
                            <label for="ngay_chung_tu" class="form-label required-field">Ngày chứng từ</label>
                            <input type="date" class="form-control @error('ngay_chung_tu') is-invalid @enderror" id="ngay_chung_tu" name="ngay_chung_tu" value="{{ old('ngay_chung_tu', date('Y-m-d')) }}" required>
                            @error('ngay_chung_tu')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>
                    
                    <div class="mb-3">
                        <label for="ghi_chu" class="form-label">Ghi chú</label>
                        <textarea class="form-control" id="ghi_chu" name="ghi_chu" rows="3">{{ old('ghi_chu') }}</textarea>
                    </div>
                </div>
            </div>
            
            <!-- Tóm tắt chi phí -->
            <div class="card mb-4">
                <div class="card-header bg-primary text-white">
                    <h6 class="mb-0">Tóm Tắt Chi Phí</h6>
                </div>
                <div class="card-body">
                    <div class="summary-section">
                        <div class="summary-item">
                            <span>Tổng tiền hàng:</span>
                            <span id="summary-subtotal">0 đ</span>
                        </div>
                        <div class="summary-item">
                            <span>Thuế VAT:</span>
                            <span id="summary-vat">0 đ</span>
                        </div>
                        <div class="summary-item summary-total">
                            <span>Tổng cộng:</span>
                            <span id="summary-total">0 đ</span>
                        </div>
                    </div>
                    
                    <input type="hidden" id="tong_tien" name="tong_tien" value="0">
                    <input type="hidden" id="vat" name="vat" value="0">
                    <input type="hidden" id="tong_cong" name="tong_cong" value="0">
                </div>
            </div>
        </div>
        
        <!-- Chi tiết lô nhập -->
        <div class="col-md-8">
            <div class="card">
                <div class="card-header d-flex justify-content-between align-items-center bg-primary text-white">
                    <h6 class="mb-0">Chi Tiết Lô Nhập</h6>
                </div>
                
                <!-- Phần tìm kiếm và thêm thuốc nhanh -->
                <div class="card-body border-bottom pb-0">
                    <div class="row align-items-end g-3 mb-3">
                        <div class="col-md-5">
                            <label for="quick_thuoc_id" class="form-label required-field">Chọn thuốc</label>
                            <select class="form-select" id="quick_thuoc_id" required>
                                <option value="">-- Chọn thuốc --</option>
                                @foreach($thuocs as $thuoc)
                                    <option value="{{ $thuoc->thuoc_id }}" 
                                        data-don-vi-goc="{{ $thuoc->don_vi_goc }}"
                                        data-don-vi-ban="{{ $thuoc->don_vi_ban }}"
                                        data-ti-le="{{ $thuoc->ti_le_quy_doi }}">
                                        {{ $thuoc->ten_thuoc }}
                                    </option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-md-3">
                            <label for="quick_kho_id" class="form-label required-field">Chọn kho</label>
                            <select class="form-select" id="quick_kho_id" required>
                                <option value="">-- Chọn kho --</option>
                                @foreach($khos as $kho)
                                    <option value="{{ $kho->kho_id }}">{{ $kho->ten_kho }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-md-2">
                            <button type="button" class="btn btn-sm btn-primary w-100" id="addItemBtn">
                                <i class="bi bi-plus-circle me-1"></i> Thêm Mới
                            </button>
                        </div>
                        <div class="col-md-2">
                            <button type="button" class="btn btn-sm btn-outline-info w-100" id="quickInventoryBtn">
                                <i class="bi bi-box-seam me-1"></i> Xem Tồn
                            </button>
                        </div>
                    </div>
                    
                    <!-- Khu vực hiển thị thông tin thuốc đã chọn -->
                    <div id="selected-product-info" class="border rounded p-3 mb-3 bg-light" style="display: none;">
                        <div class="row g-2">
                            <div class="col-md-12">
                                <div class="d-flex justify-content-between mb-2">
                                    <div>
                                        <strong id="quick-product-name">Tên thuốc</strong>
                                        <span class="badge bg-info ms-2" id="quick-product-total-stock">Tồn: 0</span>
                                    </div>
                                    <div>
                                        <button class="btn btn-sm btn-success" id="quick-add-to-list-btn">
                                            <i class="bi bi-plus-circle"></i> Thêm vào phiếu
                                        </button>
                                    </div>
                                </div>
                                <div class="row g-2">
                                    <div class="col-md-4">
                                        <div class="form-floating">
                                            <select class="form-select form-select-sm" id="quick_unit_type">
                                                <option value="goc">Đơn vị gốc</option>
                                                <option value="ban">Đơn vị bán</option>
                                            </select>
                                            <label>Loại đơn vị</label>
                                        </div>
                                    </div>
                                    <div class="col-md-4">
                                        <div class="input-group input-group-sm">
                                            <span class="input-group-text">Số lượng</span>
                                            <input type="number" class="form-control" id="quick_so_luong" value="1" min="0.01" step="0.01">
                                            <span class="input-group-text" id="quick_don_vi_display"></span>
                                        </div>
                                    </div>
                                    <div class="col-md-4">
                                        <div class="input-group input-group-sm">
                                            <span class="input-group-text">Giá nhập</span>
                                            <input type="number" class="form-control" id="quick_gia_nhap" min="0">
                                            <span class="input-group-text">đ</span>
                                        </div>
                                    </div>
                                </div>
                                <div class="row g-2 mt-2">
                                    <div class="col-md-4">
                                        <div class="input-group input-group-sm">
                                            <span class="input-group-text">VAT</span>
                                            <input type="number" class="form-control" id="quick_thue_suat" value="10" min="0" max="100">
                                            <span class="input-group-text">%</span>
                                        </div>
                                    </div>
                                    <div class="col-md-8">
                                        <div class="form-floating">
                                            <input type="date" class="form-control form-control-sm" id="quick_han_su_dung" required>
                                            <label>Hạn sử dụng</label>
                                        </div>
                                    </div>
                                </div>
                                <input type="hidden" id="quick_don_vi_goc">
                                <input type="hidden" id="quick_don_vi_ban">
                                <input type="hidden" id="quick_ti_le_quy_doi">
                            </div>
                        </div>
                    </div>
                </div>
                
                <!-- Bảng chi tiết lô nhập -->
                <div class="card-body pt-3">
                    <div class="table-responsive">
                        <table class="table table-bordered" id="detailsTable">
                            <thead class="table-light">
                                <tr>
                                    <th style="width: 25%;">Thuốc</th>
                                    <th style="width: 15%;">Kho</th>
                                    <th style="width: 12%;">SL</th>
                                    <th style="width: 12%;">Đơn giá</th>
                                    <th style="width: 8%;">VAT %</th>
                                    <th style="width: 15%;">Thành tiền</th>
                                    <th style="width: 5%;">Xóa</th>
                                </tr>
                            </thead>
                            <tbody>
                                <!-- Các hàng chi tiết sẽ được thêm vào đây -->
                                <tr id="empty-row">
                                    <td colspan="7" class="text-center py-3">
                                        <div class="alert alert-info mb-0">
                                            <i class="bi bi-info-circle me-1"></i> Chưa có thuốc nào được thêm vào phiếu nhập.
                                            <br>Chọn thuốc và kho phía trên để thêm thuốc vào phiếu.
                                        </div>
                                    </td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </div>
                <div class="card-footer">
                    <div class="d-flex justify-content-between">
                        <a href="{{ route('phieu-nhap.index') }}" class="btn btn-secondary">
                            <i class="bi bi-arrow-left me-1"></i> Quay Lại
                        </a>
                        <button type="submit" class="btn btn-success" id="submitBtn">
                            <i class="bi bi-check-circle me-1"></i> Tạo Phiếu Nhập
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </div>
</form>

<!-- Modal thêm thuốc -->
<div class="modal fade" id="addItemModal" tabindex="-1" data-bs-backdrop="static">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Thêm Thuốc Vào Phiếu Nhập</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <div class="row mb-3">
                    <div class="col-md-6">
                        <label for="modal_thuoc_id" class="form-label required-field">Chọn thuốc</label>
                        <select class="form-select" id="modal_thuoc_id" required>
                            <option value="">-- Chọn thuốc --</option>
                            @foreach($thuocs as $thuoc)
                                <option value="{{ $thuoc->thuoc_id }}" data-don-vi="{{ $thuoc->don_vi_goc }}">{{ $thuoc->ten_thuoc }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-md-6">
                        <label for="modal_kho_id" class="form-label required-field">Chọn kho</label>
                        <select class="form-select" id="modal_kho_id" required>
                            <option value="">-- Chọn kho --</option>
                            @foreach($khos as $kho)
                                <option value="{{ $kho->kho_id }}">{{ $kho->ten_kho }}</option>
                            @endforeach
                        </select>
                    </div>
                </div>

                <!-- Thêm điều khiển lựa chọn lô -->
                <div class="mb-3 border p-3 rounded bg-light">
                    <div class="form-check form-check-inline mb-2">
                        <input class="form-check-input" type="radio" name="lot_option" id="new_lot" value="new" checked>
                        <label class="form-check-label" for="new_lot">Tạo lô mới</label>
                    </div>
                    <div class="form-check form-check-inline">
                        <input class="form-check-input" type="radio" name="lot_option" id="existing_lot" value="existing">
                        <label class="form-check-label" for="existing_lot">Sử dụng lô hiện có</label>
                    </div>

                    <div id="existing_lot_container" class="mt-2" style="display: none;">
                        <label for="modal_existing_lot_id" class="form-label">Chọn lô hiện có</label>
                        <select class="form-select" id="modal_existing_lot_id">
                            <option value="">-- Chọn lô hiện có --</option>
                            <!-- Các lô sẽ được điền động bằng Ajax -->
                        </select>
                        <div class="mt-2">
                            <div id="selected_lot_info" class="small"></div>
                        </div>
                        <input type="hidden" id="modal_existing_lot_ma_lo" value="">
                        <input type="hidden" id="modal_existing_lot_so_lo_nsx" value="">
                        <input type="hidden" id="modal_existing_lot_ngay_sx" value="">
                        <input type="hidden" id="modal_existing_lot_han_sd" value="">
                        <input type="hidden" id="modal_existing_lot_ghi_chu" value="">
                        <input type="hidden" id="modal_existing_lot_kho_id" value="">
                        <input type="hidden" id="modal_existing_lot_kho_text" value="">
                        <input type="hidden" id="modal_existing_lot_thuoc_id" value="">
                        <input type="hidden" id="modal_existing_lot_thuoc_text" value="">
                        <input type="hidden" id="modal_existing_lot_don_vi" value="">
                    </div>
                </div>

                <!-- Thông tin lô mới - sẽ ẩn khi chọn lô hiện có -->
                <div id="new_lot_container">
                    <div class="row mb-3">
                        <div class="col-md-6">
                            <label for="modal_so_lo" class="form-label">Số lô</label>
                            <input type="text" class="form-control" id="modal_so_lo" placeholder="Tự động tạo nếu để trống">
                            <small class="text-muted">Nếu để trống, hệ thống sẽ tạo số lô tự động</small>
                        </div>
                        <div class="col-md-6">
                            <label for="modal_so_lo_nha_san_xuat" class="form-label">Số lô nhà sản xuất</label>
                            <input type="text" class="form-control" id="modal_so_lo_nha_san_xuat">
                        </div>
                    </div>

                    <div class="row mb-3">
                        <div class="col-md-6">
                            <label for="modal_ngay_san_xuat" class="form-label">Ngày sản xuất</label>
                            <input type="date" class="form-control" id="modal_ngay_san_xuat">
                        </div>
                        <div class="col-md-6">
                            <label for="modal_han_su_dung" class="form-label required-field">Hạn sử dụng</label>
                            <input type="date" class="form-control" id="modal_han_su_dung" required>
                        </div>
                    </div>
                </div>

                <div class="row mb-3">
                    <div class="col-md-6">
                        <label for="modal_so_luong" class="form-label required-field">Số lượng</label>
                        <div class="input-group">
                            <input type="number" step="0.01" min="0.01" class="form-control" id="modal_so_luong" required>
                            <span class="input-group-text" id="modal_don_vi_display"></span>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <label for="modal_don_vi" class="form-label required-field">Đơn vị</label>
                        <input type="text" class="form-control" id="modal_don_vi" required>
                    </div>
                </div>

                <div class="row mb-3">
                    <div class="col-md-4">
                        <label for="modal_gia_nhap" class="form-label required-field">Giá nhập</label>
                        <div class="input-group">
                            <input type="number" step="1" min="0" class="form-control" id="modal_gia_nhap" required>
                            <span class="input-group-text">VNĐ</span>
                        </div>
                    </div>
                    <div class="col-md-4">
                        <label for="modal_thue_suat" class="form-label">Thuế suất VAT (%)</label>
                        <input type="number" step="0.1" min="0" class="form-control" id="modal_thue_suat" value="10">
                    </div>
                    <div class="col-md-4">
                        <label for="modal_thanh_tien" class="form-label">Thành tiền</label>
                        <div class="input-group">
                            <input type="number" step="1" min="0" class="form-control" id="modal_thanh_tien" readonly>
                            <span class="input-group-text">VNĐ</span>
                        </div>
                    </div>
                </div>

                <div class="mb-3">
                    <label for="modal_ghi_chu" class="form-label">Ghi chú lô</label>
                    <textarea class="form-control" id="modal_ghi_chu" rows="2"></textarea>
                </div>

                <div class="d-flex justify-content-between align-items-center">
                    <button type="button" class="btn btn-outline-primary" id="showInventoryBtn">
                        <i class="bi bi-box me-1"></i> Kiểm Tra Tồn Kho
                    </button>
                    <span class="text-danger">* Thông tin bắt buộc</span>
                </div>
                
                <div id="inventory-container" class="mt-3" style="display: none;">
                    <div class="lot-info">
                        <h6>Thông Tin Tồn Kho</h6>
                        <div id="inventory-details" class="small">
                            <!-- Thông tin tồn kho sẽ được hiển thị ở đây -->
                        </div>
                    </div>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Đóng</button>
                <button type="button" class="btn btn-primary" id="addToListBtn">Thêm Vào Phiếu</button>
            </div>
        </div>
    </div>
</div>
@endsection

@section('scripts')
<script>
    $(document).ready(function() {
        // Biến toàn cục để lưu index của các chi tiết
        let rowIndex = 0;
        let selectedThuocData = null;
        
        // Format số thành định dạng tiền tệ
        function formatCurrency(value) {
            return new Intl.NumberFormat('vi-VN').format(value) + ' đ';
        }
        
        // Format date từ YYYY-MM-DD sang DD/MM/YYYY
        function formatDate(dateString) {
            if (!dateString) return '';
            const date = new Date(dateString);
            return date.toLocaleDateString('vi-VN');
        }
        
        // Hiện toast thông báo
        function showToast(message, type = 'info') {
            // Tạo toast nếu không tồn tại
            if ($('#toastContainer').length === 0) {
                $('body').append(`
                    <div id="toastContainer" style="position: fixed; top: 20px; right: 20px; z-index: 9999;"></div>
                `);
            }
            
            // Tạo mã ngẫu nhiên cho toast
            const toastId = 'toast-' + Math.random().toString(36).substr(2, 9);
            
            // Xác định class theo loại
            let bgClass;
            let icon;
            switch (type) {
                case 'success':
                    bgClass = 'bg-success';
                    icon = '<i class="bi bi-check-circle-fill me-2"></i>';
                    break;
                case 'warning':
                    bgClass = 'bg-warning';
                    icon = '<i class="bi bi-exclamation-triangle-fill me-2"></i>';
                    break;
                case 'danger':
                    bgClass = 'bg-danger';
                    icon = '<i class="bi bi-x-circle-fill me-2"></i>';
                    break;
                default:
                    bgClass = 'bg-info';
                    icon = '<i class="bi bi-info-circle-fill me-2"></i>';
            }
            
            // Tạo toast
            const toastHtml = `
                <div id="${toastId}" class="toast align-items-center ${bgClass} text-white border-0 mb-2" role="alert" aria-live="assertive" aria-atomic="true">
                    <div class="d-flex">
                        <div class="toast-body">
                            ${icon} ${message}
                        </div>
                        <button type="button" class="btn-close btn-close-white me-2 m-auto" data-bs-dismiss="toast" aria-label="Close"></button>
                    </div>
                </div>
            `;
            
            // Thêm toast vào container
            $('#toastContainer').append(toastHtml);
            
            // Khởi tạo toast
            const toastElement = new bootstrap.Toast(document.getElementById(toastId), {
                delay: 3000
            });
            
            // Hiển thị toast
            toastElement.show();
            
            // Xóa toast sau khi ẩn
            $(`#${toastId}`).on('hidden.bs.toast', function() {
                $(this).remove();
            });
        }
        
        // Tính tổng tiền
        function calculateTotals() {
            let tongTien = 0;
            let tongVAT = 0;
            
            $('.detail-row').each(function() {
                const thanhTien = parseFloat($(this).find('.thanh-tien-value').val()) || 0;
                const tienThue = parseFloat($(this).find('.tien-thue-value').val()) || 0;
                
                tongTien += thanhTien - tienThue;
                tongVAT += tienThue;
            });
            
            const tongCong = tongTien + tongVAT;
            
            // Cập nhật hiển thị
            $('#summary-subtotal').text(formatCurrency(tongTien));
            $('#summary-vat').text(formatCurrency(tongVAT));
            $('#summary-total').text(formatCurrency(tongCong));
            
            // Cập nhật giá trị form
            $('#tong_tien').val(tongTien);
            $('#vat').val(tongVAT);
            $('#tong_cong').val(tongCong);
        }
        
        // Hiển thị đơn vị khi chọn thuốc
        $('#modal_thuoc_id').change(function() {
            const selectedOption = $(this).find('option:selected');
            const donVi = selectedOption.data('don-vi') || '';
            
            $('#modal_don_vi').val(donVi);
            $('#modal_don_vi_display').text(donVi);
        });
        
        // Xử lý khi chọn thuốc ở phần thêm nhanh
        $('#quick_thuoc_id').change(function() {
            const thuocId = $(this).val();
            if (!thuocId) {
                $('#selected-product-info').hide();
                selectedThuocData = null;
                return;
            }
            
            const selectedOption = $(this).find('option:selected');
            const donViGoc = selectedOption.data('don-vi-goc');
            const donViBan = selectedOption.data('don-vi-ban');
            const tiLeQuyDoi = selectedOption.data('ti-le');
            const thuocName = selectedOption.text();
            
            // Lưu thông tin thuốc đã chọn
            selectedThuocData = {
                thuoc_id: thuocId,
                ten_thuoc: thuocName,
                don_vi_goc: donViGoc,
                don_vi_ban: donViBan,
                ti_le_quy_doi: tiLeQuyDoi
            };
            
            // Hiển thị thông tin thuốc
            $('#quick-product-name').text(thuocName);
            $('#quick_don_vi_goc').val(donViGoc);
            $('#quick_don_vi_ban').val(donViBan);
            $('#quick_ti_le_quy_doi').val(tiLeQuyDoi);
            $('#quick_don_vi_display').text(donViGoc);
            
            // Đặt ngày hạn sử dụng mặc định là 1 năm sau
            const oneYearLater = new Date();
            oneYearLater.setFullYear(oneYearLater.getFullYear() + 1);
            $('#quick_han_su_dung').val(oneYearLater.toISOString().split('T')[0]);
            
            // Kiểm tra tồn kho nếu đã chọn kho
            checkInventory();
            
            // Hiển thị phần thông tin chi tiết
            $('#selected-product-info').show();
        });
        
        // Khi thay đổi kho, kiểm tra tồn kho nếu đã chọn thuốc
        $('#quick_kho_id').change(function() {
            if (selectedThuocData) {
                checkInventory();
            }
        });
        
        // Khi chọn loại đơn vị, cập nhật hiển thị
        $('#quick_unit_type').change(function() {
            if (!selectedThuocData) return;
            
            const unitType = $(this).val();
            if (unitType === 'goc') {
                $('#quick_don_vi_display').text(selectedThuocData.don_vi_goc);
            } else {
                $('#quick_don_vi_display').text(selectedThuocData.don_vi_ban);
            }
        });
        
        // Kiểm tra tồn kho của thuốc trong kho đã chọn
        function checkInventory() {
            const thuocId = $('#quick_thuoc_id').val();
            const khoId = $('#quick_kho_id').val();
            
            if (!thuocId || !khoId) {
                return;
            }
            
            // Gọi API để lấy thông tin tồn kho
            $.ajax({
                url: "{{ route('phieu-nhap.get-ton-kho') }}",
                type: "GET",
                data: {
                    thuoc_id: thuocId,
                    kho_id: khoId
                },
                success: function(response) {
                    const tonKho = response.tonKho;
                    const thuoc = response.thuoc;
                    
                    if (tonKho && tonKho.length > 0) {
                        let totalStock = 0;
                        tonKho.forEach(lo => {
                            totalStock += parseFloat(lo.ton_kho_hien_tai);
                        });
                        
                        $('#quick-product-total-stock').text(`Tồn: ${totalStock} ${thuoc.don_vi_goc}`).show();
                    } else {
                        $('#quick-product-total-stock').text('Tồn: 0').show();
                    }
                },
                error: function() {
                    $('#quick-product-total-stock').text('Lỗi tồn kho').show();
                }
            });
        }
        
        // Hàm tải danh sách lô hiện có của thuốc và kho đã chọn
        function loadExistingLots(thuocId, khoId) {
            // Hiển thị loading
            $('#modal_existing_lot_id').html('<option value="">Đang tải...</option>');
            
            // Gọi API để lấy thông tin lô hiện có
            $.ajax({
                url: "{{ route('phieu-nhap.get-ton-kho') }}",
                type: "GET",
                data: {
                    all_lots: true // Lấy tất cả các lô, không lọc theo thuốc và kho
                },
                success: function(response) {
                    const lots = response.tonKho;
                    let options = '<option value="">-- Chọn lô hiện có --</option>';
                    
                    if (lots && lots.length > 0) {
                        $.each(lots, function(index, lot) {
                            // Format ngày hết hạn
                            const expDate = new Date(lot.han_su_dung).toLocaleDateString('vi-VN');
                            const thuocTen = lot.thuoc ? lot.thuoc.ten_thuoc : 'Không xác định';
                            const khoTen = lot.kho ? lot.kho.ten_kho : 'Không xác định';
                            const donVi = lot.thuoc ? lot.thuoc.don_vi_goc : '';
                            const lotLabel = `${thuocTen} - ${lot.ma_lo || 'Không có mã lô'} - ${khoTen} - HSD: ${expDate}`;
                            
                            options += `<option value="${lot.lo_id}" 
                                data-ma-lo="${lot.ma_lo || ''}" 
                                data-so-lo-nsx="${lot.so_lo_nha_san_xuat || ''}"
                                data-ngay-sx="${lot.ngay_san_xuat || ''}" 
                                data-han-sd="${lot.han_su_dung || ''}"
                                data-ghi-chu="${lot.ghi_chu || ''}"
                                data-ton-kho="${lot.ton_kho_hien_tai}"
                                data-gia-nhap="${lot.gia_nhap_tb}"
                                data-kho-id="${lot.kho_id}"
                                data-kho-ten="${khoTen}"
                                data-thuoc-id="${lot.thuoc_id}"
                                data-thuoc-ten="${thuocTen}"
                                data-don-vi="${donVi}">
                                ${lotLabel}
                            </option>`;
                        });
                    }
                    
                    $('#modal_existing_lot_id').html(options);
                },
                error: function() {
                    $('#modal_existing_lot_id').html('<option value="">-- Lỗi tải dữ liệu --</option>');
                }
            });
        }
        
        // Xử lý khi chọn lô hiện có
        $('#modal_existing_lot_id').change(function() {
            const selectedOption = $(this).find('option:selected');
            
            if (selectedOption.val()) {
                // Lấy thông tin lô từ data attributes
                const maLo = selectedOption.data('ma-lo');
                const soLoNSX = selectedOption.data('so-lo-nsx');
                const ngaySX = selectedOption.data('ngay-sx');
                const hanSD = selectedOption.data('han-sd');
                const ghiChu = selectedOption.data('ghi-chu');
                const tonKho = selectedOption.data('ton-kho');
                const giaNhap = selectedOption.data('gia-nhap');
                const khoId = selectedOption.data('kho-id');
                const khoTen = selectedOption.data('kho-ten');
                const thuocId = selectedOption.data('thuoc-id');
                const thuocTen = selectedOption.data('thuoc-ten');
                const donVi = selectedOption.data('don-vi');
                
                // Lưu các thông tin này vào hidden inputs để sử dụng khi thêm vào bảng
                $('#modal_existing_lot_ma_lo').val(maLo);
                $('#modal_existing_lot_so_lo_nsx').val(soLoNSX);
                $('#modal_existing_lot_ngay_sx').val(ngaySX);
                $('#modal_existing_lot_han_sd').val(hanSD);
                $('#modal_existing_lot_ghi_chu').val(ghiChu);
                $('#modal_existing_lot_kho_id').val(khoId);
                $('#modal_existing_lot_kho_text').val(khoTen);
                $('#modal_existing_lot_thuoc_id').val(thuocId);
                $('#modal_existing_lot_thuoc_text').val(thuocTen);
                $('#modal_existing_lot_don_vi').val(donVi);
                
                // Hiển thị thông tin lô đã chọn
                let infoHTML = `
                    <div class="p-2 border-start border-info border-3">
                        <div><strong>Thuốc:</strong> ${thuocTen}</div>
                        <div><strong>Kho:</strong> ${khoTen}</div>
                        <div><strong>Mã lô:</strong> ${maLo || 'Không có mã lô'}</div>
                        ${soLoNSX ? '<div><strong>Số lô NSX:</strong> ' + soLoNSX + '</div>' : ''}
                        ${ngaySX ? '<div><strong>Ngày SX:</strong> ' + formatDate(ngaySX) + '</div>' : ''}
                        <div><strong>Hạn SD:</strong> ${formatDate(hanSD)}</div>
                        <div><strong>Tồn kho hiện tại:</strong> ${tonKho} ${donVi}</div>
                        <div><strong>Giá nhập TB hiện tại:</strong> ${formatCurrency(giaNhap)}</div>
                        ${ghiChu ? '<div><strong>Ghi chú:</strong> ' + ghiChu + '</div>' : ''}
                    </div>
                `;
                
                // Lấy lịch sử nhập của lô này
                $.ajax({
                    url: "{{ route('phieu-nhap.get-lot-history') }}",
                    type: "GET",
                    data: {
                        lo_id: selectedOption.val()
                    },
                    success: function(response) {
                        if (response.history && response.history.length > 0) {
                            let historyHTML = `
                                <div class="mt-3 p-2 border-start border-warning border-3">
                                    <div class="fw-bold mb-2">Lịch sử nhập của lô:</div>
                                    <div class="table-responsive">
                                        <table class="table table-sm table-bordered">
                                            <thead class="table-light">
                                                <tr>
                                                    <th>Phiếu nhập</th>
                                                    <th>Ngày nhập</th>
                                                    <th>SL nhập</th>
                                                    <th>Giá nhập</th>
                                                    <th>Thuế</th>
                                                    <th>Thành tiền</th>
                                                </tr>
                                            </thead>
                                            <tbody>
                            `;
                            
                            $.each(response.history, function(index, item) {
                                historyHTML += `
                                    <tr>
                                        <td>${item.phieu_nhap.ma_phieu}</td>
                                        <td>${formatDate(item.phieu_nhap.ngay_nhap)}</td>
                                        <td>${item.so_luong} ${item.don_vi}</td>
                                        <td>${formatCurrency(item.gia_nhap)}</td>
                                        <td>${item.thue_suat}%</td>
                                        <td>${formatCurrency(item.thanh_tien)}</td>
                                    </tr>
                                `;
                            });
                            
                            historyHTML += `
                                            </tbody>
                                        </table>
                                    </div>
                                </div>
                            `;
                            
                            infoHTML += historyHTML;
                        }
                        
                        // Hiển thị thông tin
                        $('#selected_lot_info').html(infoHTML);
                    },
                    error: function() {
                        // Vẫn hiển thị thông tin cơ bản nếu không lấy được lịch sử
                        $('#selected_lot_info').html(infoHTML);
                    }
                });
                
                $('#selected_lot_info').html(infoHTML);
                
                // Đặt giá nhập mặc định từ lô hiện có (có thể sửa đổi)
                $('#modal_gia_nhap').val(giaNhap);
                
                // Cập nhật và vô hiệu hóa trường thuốc, kho và đơn vị
                $('#modal_thuoc_id').val(thuocId).prop('disabled', true);
                $('#modal_kho_id').val(khoId).prop('disabled', true);
                $('#modal_don_vi').val(donVi);
                $('#modal_don_vi_display').text(donVi);
            } else {
                // Nếu không chọn lô nào, kích hoạt lại các trường
                $('#modal_thuoc_id').prop('disabled', false);
                $('#modal_kho_id').prop('disabled', false);
                $('#selected_lot_info').html('');
            }
        });
        
        // Chuyển đổi giữa lô mới và lô hiện có
        $('input[name="lot_option"]').change(function() {
            if ($(this).val() === 'new') {
                $('#new_lot_container').show();
                $('#existing_lot_container').hide();
                // Kích hoạt lại các trường chọn thuốc và kho
                $('#modal_thuoc_id').prop('disabled', false);
                $('#modal_kho_id').prop('disabled', false);
                // Reset giá trị lô đã chọn
                $('#modal_existing_lot_id').val('');
                $('#selected_lot_info').html('');
            } else {
                $('#new_lot_container').hide();
                $('#existing_lot_container').show();
                // Nếu đã chọn lô, vô hiệu hóa trường thuốc và kho
                if ($('#modal_existing_lot_id').val()) {
                    $('#modal_thuoc_id').prop('disabled', true);
                    $('#modal_kho_id').prop('disabled', true);
                }
            }
        });
        
        // Tính tiền khi thay đổi số lượng hoặc giá
        $('#modal_so_luong, #modal_gia_nhap, #modal_thue_suat').on('input', function() {
            const soLuong = parseFloat($('#modal_so_luong').val()) || 0;
            const giaNhap = parseFloat($('#modal_gia_nhap').val()) || 0;
            const thueSuat = parseFloat($('#modal_thue_suat').val()) || 0;
            
            const tienHang = soLuong * giaNhap;
            const tienThue = tienHang * (thueSuat / 100);
            const thanhTien = tienHang + tienThue;
            
            $('#modal_tien_thue').val(tienThue);
            $('#modal_thanh_tien').val(thanhTien.toFixed(0));
        });
        
        // Mở modal thêm thuốc
        $('#addItemBtn').click(function() {
            // Reset form
            $('#modal_thuoc_id').val('').prop('disabled', false);
            $('#modal_kho_id').val('').prop('disabled', false);
            $('#modal_so_lo').val('');
            $('#modal_so_lo_nha_san_xuat').val('');
            $('#modal_ngay_san_xuat').val('');
            $('#modal_han_su_dung').val('');
            $('#modal_so_luong').val('');
            $('#modal_don_vi').val('');
            $('#modal_gia_nhap').val('');
            $('#modal_thue_suat').val('10');
            $('#modal_thanh_tien').val('');
            $('#modal_ghi_chu').val('');
            $('#modal_don_vi_display').text('');
            
            // Reset các trường cho lô hiện có
            $('#modal_existing_lot_id').html('<option value="">-- Chọn lô hiện có --</option>');
            $('#selected_lot_info').html('');
            $('#modal_existing_lot_ma_lo').val('');
            $('#modal_existing_lot_so_lo_nsx').val('');
            $('#modal_existing_lot_ngay_sx').val('');
            $('#modal_existing_lot_han_sd').val('');
            $('#modal_existing_lot_ghi_chu').val('');
            $('#modal_existing_lot_kho_id').val('');
            $('#modal_existing_lot_kho_text').val('');
            $('#modal_existing_lot_thuoc_id').val('');
            $('#modal_existing_lot_thuoc_text').val('');
            $('#modal_existing_lot_don_vi').val('');
            
            // Mặc định chọn lô mới
            $('#new_lot').prop('checked', true);
            $('#new_lot_container').show();
            $('#existing_lot_container').hide();
            
            // Ẩn thông tin tồn kho
            $('#inventory-container').hide();
            
            // Tải danh sách lô hiện có (để sẵn sàng khi người dùng chọn tab lô hiện có)
            loadExistingLots();
            
            // Hiển thị modal
            $('#addItemModal').modal('show');
        });
        
        // Kiểm tra tồn kho
        $('#showInventoryBtn').click(function() {
            const thuocId = $('#modal_thuoc_id').val();
            const khoId = $('#modal_kho_id').val();
            
            if (!thuocId || !khoId) {
                showToast('Vui lòng chọn thuốc và kho trước khi kiểm tra tồn kho', 'warning');
                return;
            }
            
            // Hiển thị loading
            $('#inventory-details').html(`
                <div class="text-center py-2">
                    <div class="spinner-border spinner-border-sm text-primary" role="status">
                        <span class="visually-hidden">Đang tải...</span>
                    </div>
                    <span class="ms-2">Đang tải thông tin tồn kho...</span>
                </div>
            `);
            
            $('#inventory-container').show();
            
            // Gọi API để lấy thông tin tồn kho
            $.ajax({
                url: "{{ route('phieu-nhap.get-ton-kho') }}",
                type: "GET",
                data: {
                    thuoc_id: thuocId,
                    kho_id: khoId
                },
                success: function(response) {
                    const tonKho = response.tonKho;
                    const thuoc = response.thuoc;
                    
                    let html = '';
                    
                    if (tonKho.length > 0) {
                        html += `<p><strong>${thuoc.ten_thuoc}</strong> hiện có ${tonKho.length} lô trong kho:</p>`;
                        html += '<div class="row">';
                        
                        $.each(tonKho, function(index, lo) {
                            // Tính số ngày còn lại đến hạn sử dụng
                            const today = new Date();
                            today.setHours(0, 0, 0, 0);
                            const expiry = new Date(lo.han_su_dung);
                            const diffTime = expiry - today;
                            const daysRemaining = Math.ceil(diffTime / (1000 * 60 * 60 * 24));
                            
                            let warningClass = '';
                            let warningText = '';
                            
                            if (daysRemaining <= 30 && daysRemaining > 0) {
                                warningClass = 'expiry-warning';
                                warningText = `<span class="text-warning"><i class="bi bi-exclamation-triangle-fill me-1"></i> Sắp hết hạn (còn ${daysRemaining} ngày)</span>`;
                            } else if (daysRemaining <= 0) {
                                warningClass = 'expiry-danger';
                                warningText = '<span class="text-danger"><i class="bi bi-exclamation-octagon-fill me-1"></i> Đã hết hạn</span>';
                            }
                            
                            html += `
                                <div class="col-md-6">
                                    <div class="inventory-item ${warningClass}">
                                        <div class="d-flex justify-content-between">
                                            <strong>Lô: ${lo.ma_lo || lo.so_lo_nha_san_xuat || 'Không có số lô'}</strong>
                                            ${warningText}
                                        </div>
                                        <div>Tồn kho: <strong>${lo.ton_kho_hien_tai}</strong> ${thuoc.don_vi_goc}</div>
                                        <div>HSD: ${new Date(lo.han_su_dung).toLocaleDateString('vi-VN')}</div>
                                    </div>
                                </div>
                            `;
                        });
                        
                        html += '</div>';
                    } else {
                        html = `<p>Hiện không có <strong>${thuoc.ten_thuoc}</strong> trong kho này.</p>`;
                    }
                    
                    $('#inventory-details').html(html);
                },
                error: function() {
                    $('#inventory-details').html(`
                        <div class="alert alert-danger py-2 mb-0">
                            <i class="bi bi-exclamation-triangle me-2"></i> Không thể tải thông tin tồn kho.
                        </div>
                    `);
                }
            });
        });
        
        // Thêm thuốc vào danh sách
        $('#addToListBtn').click(function() {
            // Xác định loại lô (mới hay hiện có)
            const isNewLot = $('#new_lot').prop('checked');
            
            // Validate và lấy thông tin dựa vào loại lô
            let thuocId, thuocText, khoId, khoText, soLuong, donVi, giaNhap, thueSuat;
            let loId, soLo, soLoNSX, ngaySX, hanSD, ghiChu;
            
            soLuong = $('#modal_so_luong').val();
            giaNhap = $('#modal_gia_nhap').val();
            thueSuat = $('#modal_thue_suat').val();
            
            if (isNewLot) {
                // Lô mới - lấy thông tin từ các trường nhập
                thuocId = $('#modal_thuoc_id').val();
                thuocText = $('#modal_thuoc_id option:selected').text();
                khoId = $('#modal_kho_id').val();
                khoText = $('#modal_kho_id option:selected').text();
                donVi = $('#modal_don_vi').val();
                
                loId = '';
                soLo = $('#modal_so_lo').val();
                soLoNSX = $('#modal_so_lo_nha_san_xuat').val();
                ngaySX = $('#modal_ngay_san_xuat').val();
                hanSD = $('#modal_han_su_dung').val();
                ghiChu = $('#modal_ghi_chu').val();
                
                // Validate thuốc, kho và hạn sử dụng cho lô mới
                if (!thuocId || !khoId) {
                    showToast('Vui lòng chọn thuốc và kho cho lô mới', 'warning');
                    return;
                }
                
                if (!hanSD) {
                    showToast('Vui lòng nhập hạn sử dụng cho lô mới', 'warning');
                    return;
                }
            } else {
                // Lô hiện có - lấy thông tin từ lô đã chọn
                const selectedLot = $('#modal_existing_lot_id').val();
                
                if (!selectedLot) {
                    showToast('Vui lòng chọn lô hiện có', 'warning');
                    return;
                }
                
                loId = selectedLot;
                thuocId = $('#modal_existing_lot_thuoc_id').val();
                thuocText = $('#modal_existing_lot_thuoc_text').val();
                khoId = $('#modal_existing_lot_kho_id').val();
                khoText = $('#modal_existing_lot_kho_text').val();
                donVi = $('#modal_existing_lot_don_vi').val();
                soLo = $('#modal_existing_lot_ma_lo').val();
                soLoNSX = $('#modal_existing_lot_so_lo_nsx').val();
                ngaySX = $('#modal_existing_lot_ngay_sx').val();
                hanSD = $('#modal_existing_lot_han_sd').val();
                ghiChu = $('#modal_existing_lot_ghi_chu').val();
            }
            
            // Validate các trường bắt buộc chung
            if (!thuocId || !khoId || !soLuong || !donVi || !giaNhap) {
                showToast('Vui lòng điền đầy đủ các thông tin bắt buộc', 'warning');
                return;
            }
            
            // Tính tiền
            const tienHang = parseFloat(soLuong) * parseFloat(giaNhap);
            const tienThue = tienHang * (parseFloat(thueSuat) / 100);
            const thanhTien = tienHang + tienThue;
            
            // Thêm hàng mới vào bảng
            const newRow = `
                <tr class="detail-row">
                    <td>
                        <div><strong>${thuocText}</strong></div>
                        <div class="small text-muted">
                            ${isNewLot ? 'Lô mới: ' + (soLo || 'Tạo tự động') : 'Lô hiện có: ' + soLo} ${soLoNSX ? '/ NSX: ' + soLoNSX : ''}
                        </div>
                        <div class="small text-muted">
                            ${ngaySX ? 'NSX: ' + formatDate(ngaySX) : ''} | HSD: ${formatDate(hanSD)}
                        </div>
                        <input type="hidden" name="thuoc_id[]" value="${thuocId}">
                        <input type="hidden" name="lo_id[]" value="${loId}">
                        <input type="hidden" name="is_new_lot[]" value="${isNewLot ? '1' : '0'}">
                        <input type="hidden" name="so_lo[]" value="${soLo}">
                        <input type="hidden" name="so_lo_nha_san_xuat[]" value="${soLoNSX}">
                        <input type="hidden" name="ngay_san_xuat[]" value="${ngaySX}">
                        <input type="hidden" name="han_su_dung[]" value="${hanSD}">
                        <input type="hidden" name="ghi_chu_lo[]" value="${ghiChu}">
                    </td>
                    <td>
                        ${khoText}
                        <input type="hidden" name="kho_id[]" value="${khoId}">
                    </td>
                    <td>
                        ${soLuong} ${donVi}
                        <input type="hidden" name="so_luong[]" value="${soLuong}">
                        <input type="hidden" name="don_vi[]" value="${donVi}">
                    </td>
                    <td>
                        ${formatCurrency(giaNhap)}
                        <input type="hidden" name="gia_nhap[]" value="${giaNhap}">
                    </td>
                    <td>
                        ${thueSuat}%
                        <input type="hidden" name="thue_suat[]" value="${thueSuat}">
                        <input type="hidden" name="tien_thue[]" class="tien-thue-value" value="${tienThue.toFixed(0)}">
                    </td>
                    <td>
                        ${formatCurrency(thanhTien.toFixed(0))}
                        <input type="hidden" name="thanh_tien[]" class="thanh-tien-value" value="${thanhTien.toFixed(0)}">
                    </td>
                    <td>
                        <button type="button" class="btn btn-sm btn-danger btn-remove">
                            <i class="bi bi-trash-fill"></i>
                        </button>
                    </td>
                </tr>
            `;
            
            // Xóa hàng trống nếu có
            $('#empty-row').remove();
            
            // Thêm hàng mới vào bảng
            $('#detailsTable tbody').append(newRow);
            
            // Tăng index
            rowIndex++;
            
            // Tính lại tổng tiền
            calculateTotals();
            
            // Đóng modal
            $('#addItemModal').modal('hide');
            
            showToast(`Đã thêm ${thuocText} vào phiếu nhập`, 'success');
        });
        
        // Xem thông tin tồn kho nhanh
        $('#quickInventoryBtn').click(function() {
            const thuocId = $('#quick_thuoc_id').val();
            const khoId = $('#quick_kho_id').val();
            
            if (!thuocId || !khoId) {
                showToast('Vui lòng chọn thuốc và kho trước khi kiểm tra tồn kho', 'warning');
                return;
            }
            
            // Hiển thị modal tồn kho (sử dụng lại modal có sẵn)
            $('#inventory-details').html(`
                <div class="text-center py-2">
                    <div class="spinner-border spinner-border-sm text-primary" role="status">
                        <span class="visually-hidden">Đang tải...</span>
                    </div>
                    <span class="ms-2">Đang tải thông tin tồn kho...</span>
                </div>
            `);
            
            $('#inventory-container').show();
            $('#addItemModal').modal('show');
            
            // Gọi API để lấy thông tin tồn kho
            $.ajax({
                url: "{{ route('phieu-nhap.get-ton-kho') }}",
                type: "GET",
                data: {
                    thuoc_id: thuocId,
                    kho_id: khoId
                },
                success: function(response) {
                    const tonKho = response.tonKho;
                    const thuoc = response.thuoc;
                    
                    let html = '';
                    
                    if (tonKho.length > 0) {
                        html += `<p><strong>${thuoc.ten_thuoc}</strong> hiện có ${tonKho.length} lô trong kho:</p>`;
                        html += '<div class="row">';
                        
                        $.each(tonKho, function(index, lo) {
                            // Tính số ngày còn lại đến hạn sử dụng
                            const today = new Date();
                            today.setHours(0, 0, 0, 0);
                            const expiry = new Date(lo.han_su_dung);
                            const diffTime = expiry - today;
                            const daysRemaining = Math.ceil(diffTime / (1000 * 60 * 60 * 24));
                            
                            let warningClass = '';
                            let warningText = '';
                            
                            if (daysRemaining <= 30 && daysRemaining > 0) {
                                warningClass = 'expiry-warning';
                                warningText = `<span class="text-warning"><i class="bi bi-exclamation-triangle-fill me-1"></i> Sắp hết hạn (còn ${daysRemaining} ngày)</span>`;
                            } else if (daysRemaining <= 0) {
                                warningClass = 'expiry-danger';
                                warningText = '<span class="text-danger"><i class="bi bi-exclamation-octagon-fill me-1"></i> Đã hết hạn</span>';
                            }
                            
                            html += `
                                <div class="col-md-6">
                                    <div class="inventory-item ${warningClass}">
                                        <div class="d-flex justify-content-between">
                                            <strong>Lô: ${lo.ma_lo || lo.so_lo_nha_san_xuat || 'Không có số lô'}</strong>
                                            ${warningText}
                                        </div>
                                        <div>Tồn kho: <strong>${lo.ton_kho_hien_tai}</strong> ${thuoc.don_vi_goc}</div>
                                        <div>HSD: ${new Date(lo.han_su_dung).toLocaleDateString('vi-VN')}</div>
                                    </div>
                                </div>
                            `;
                        });
                        
                        html += '</div>';
                    } else {
                        html = `<p>Hiện không có <strong>${thuoc.ten_thuoc}</strong> trong kho này.</p>`;
                    }
                    
                    $('#inventory-details').html(html);
                },
                error: function() {
                    $('#inventory-details').html(`
                        <div class="alert alert-danger py-2 mb-0">
                            <i class="bi bi-exclamation-triangle-fill me-2"></i> Không thể tải thông tin tồn kho.
                        </div>
                    `);
                }
            });
        });

        // Thêm nhanh thuốc vào danh sách
        $('#quick-add-to-list-btn').click(function() {
            if (!selectedThuocData) {
                showToast('Vui lòng chọn thuốc', 'warning');
                return;
            }
            
            const thuocId = $('#quick_thuoc_id').val();
            const thuocText = $('#quick_thuoc_id option:selected').text();
            const khoId = $('#quick_kho_id').val();
            const khoText = $('#quick_kho_id option:selected').text();
            
            if (!khoId) {
                showToast('Vui lòng chọn kho', 'warning');
                return;
            }
            
            const soLuong = $('#quick_so_luong').val();
            const giaNhap = $('#quick_gia_nhap').val();
            const thueSuat = $('#quick_thue_suat').val();
            const hanSD = $('#quick_han_su_dung').val();
            
            // Xác định đơn vị dựa trên loại đã chọn
            const unitType = $('#quick_unit_type').val();
            const donVi = unitType === 'goc' ? selectedThuocData.don_vi_goc : selectedThuocData.don_vi_ban;
            
            // Validate các trường bắt buộc
            if (!soLuong || !giaNhap || !hanSD) {
                showToast('Vui lòng điền đầy đủ thông tin số lượng, giá nhập và hạn sử dụng', 'warning');
                return;
            }
            
            // Tính tiền
            const tienHang = parseFloat(soLuong) * parseFloat(giaNhap);
            const tienThue = tienHang * (parseFloat(thueSuat) / 100);
            const thanhTien = tienHang + tienThue;
            
            // Thêm hàng mới vào bảng
            const newRow = `
                <tr class="detail-row" data-row-index="${rowIndex}">
                    <td>
                        <div><strong>${thuocText}</strong></div>
                        <div class="small text-muted">
                            Lô mới: Tạo tự động
                        </div>
                        <div class="small text-muted">
                            HSD: ${formatDate(hanSD)}
                        </div>
                        <input type="hidden" name="thuoc_id[]" value="${thuocId}">
                        <input type="hidden" name="lo_id[]" value="">
                        <input type="hidden" name="is_new_lot[]" value="1">
                        <input type="hidden" name="so_lo[]" value="">
                        <input type="hidden" name="so_lo_nha_san_xuat[]" value="">
                        <input type="hidden" name="ngay_san_xuat[]" value="">
                        <input type="hidden" name="han_su_dung[]" value="${hanSD}">
                        <input type="hidden" name="ghi_chu_lo[]" value="">
                    </td>
                    <td>
                        ${khoText}
                        <input type="hidden" name="kho_id[]" value="${khoId}">
                    </td>
                    <td class="editable-qty" data-row-index="${rowIndex}">
                        <div class="d-flex align-items-center">
                            <input type="number" class="form-control form-control-sm qty-input" 
                                   value="${soLuong}" min="0.01" step="0.01" 
                                   style="width: 70px" data-original="${soLuong}">
                            <span class="ms-2">${donVi}</span>
                        </div>
                        <input type="hidden" name="so_luong[]" class="so-luong-input" value="${soLuong}">
                        <input type="hidden" name="don_vi[]" value="${donVi}">
                    </td>
                    <td class="editable-price" data-row-index="${rowIndex}">
                        <div class="d-flex align-items-center">
                            <input type="number" class="form-control form-control-sm price-input" 
                                   value="${giaNhap}" min="0" 
                                   style="width: 100px" data-original="${giaNhap}">
                            <span class="ms-2">đ</span>
                        </div>
                        <input type="hidden" name="gia_nhap[]" class="gia-nhap-input" value="${giaNhap}">
                    </td>
                    <td class="editable-vat" data-row-index="${rowIndex}">
                        <div class="d-flex align-items-center">
                            <input type="number" class="form-control form-control-sm vat-input" 
                                   value="${thueSuat}" min="0" max="100" 
                                   style="width: 60px" data-original="${thueSuat}">
                            <span class="ms-2">%</span>
                        </div>
                        <input type="hidden" name="thue_suat[]" class="thue-suat-input" value="${thueSuat}">
                        <input type="hidden" name="tien_thue[]" class="tien-thue-value" value="${tienThue.toFixed(0)}">
                    </td>
                    <td class="total-price-cell" data-row-index="${rowIndex}">
                        ${formatCurrency(thanhTien.toFixed(0))}
                        <input type="hidden" name="thanh_tien[]" class="thanh-tien-value" value="${thanhTien.toFixed(0)}">
                    </td>
                    <td>
                        <button type="button" class="btn btn-sm btn-danger btn-remove">
                            <i class="bi bi-trash-fill"></i>
                        </button>
                    </td>
                </tr>
            `;
            
            // Xóa hàng trống nếu có
            $('#empty-row').remove();
            
            // Thêm hàng mới vào bảng
            $('#detailsTable tbody').append(newRow);
            
            // Tăng index
            rowIndex++;
            
            // Tính lại tổng tiền
            calculateTotals();
            
            // Reset form thêm nhanh
            $('#quick_so_luong').val('1');
            $('#quick_gia_nhap').val('');
            
            // Giữ focus ở trường số lượng
            $('#quick_so_luong').focus();
            
            showToast(`Đã thêm ${thuocText} vào phiếu nhập`, 'success');
        });
        
        // Xóa dòng chi tiết
        $(document).on('click', '.btn-remove', function() {
            $(this).closest('tr').remove();
            
            // Kiểm tra xem còn dòng nào không
            if ($('.detail-row').length === 0) {
                $('#detailsTable tbody').html(`
                    <tr id="empty-row">
                        <td colspan="7" class="text-center py-3">
                            <div class="alert alert-info mb-0">
                                <i class="bi bi-info-circle me-1"></i> Chưa có thuốc nào được thêm vào phiếu nhập.
                                <br>Chọn thuốc và kho phía trên để thêm thuốc vào phiếu.
                            </div>
                        </td>
                    </tr>
                `);
            }
            
            // Tính lại tổng tiền
            calculateTotals();
        });
        
        // Xử lý khi thay đổi số lượng, giá nhập hoặc thuế suất
        $(document).on('input', '.qty-input, .price-input, .vat-input', function() {
            const row = $(this).closest('.detail-row');
            const rowIndex = row.data('row-index');
            
            // Lấy giá trị
            const soLuong = parseFloat(row.find('.qty-input').val()) || 0;
            const giaNhap = parseFloat(row.find('.price-input').val()) || 0;
            const thueSuat = parseFloat(row.find('.vat-input').val()) || 0;
            
            // Tính tiền
            const tienHang = soLuong * giaNhap;
            const tienThue = tienHang * (thueSuat / 100);
            const thanhTien = tienHang + tienThue;
            
            // Cập nhật giá trị hidden inputs
            row.find('.so-luong-input').val(soLuong);
            row.find('.gia-nhap-input').val(giaNhap);
            row.find('.thue-suat-input').val(thueSuat);
            row.find('.tien-thue-value').val(tienThue.toFixed(0));
            row.find('.thanh-tien-value').val(thanhTien.toFixed(0));
            
            // Cập nhật hiển thị thành tiền
            row.find('.total-price-cell').html(formatCurrency(thanhTien.toFixed(0)) + 
                `<input type="hidden" name="thanh_tien[]" class="thanh-tien-value" value="${thanhTien.toFixed(0)}">`);
            
            // Tính lại tổng tiền
            calculateTotals();
        });

        // Kiểm tra trước khi submit form
        $('#createPhieuNhapForm').submit(function(e) {
            if ($('.detail-row').length === 0) {
                e.preventDefault();
                showToast('Vui lòng thêm ít nhất một thuốc vào phiếu nhập', 'warning');
                return false;
            }
            
            // Hiển thị loading trên nút submit
            $('#submitBtn').html('<span class="spinner-border spinner-border-sm me-1" role="status" aria-hidden="true"></span> Đang xử lý...');
            $('#submitBtn').prop('disabled', true);
            
            return true;
        });
    });
</script>
@endsection
