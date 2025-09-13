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

    #modal_ton_kho_info {
        min-width: 120px;
        text-align: center;
        border-left: 0;
    }

    #modal_ton_kho_detail {
        margin-top: 0.25rem;
        display: block;
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
                        <div class="col-md-2">
                            <button type="button" class="btn btn-sm btn-primary w-100" id="addItemBtn">
                                <i class="bi bi-plus-circle me-1"></i> Thêm Mới
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
                                            <!-- *** BỎ thuộc tính required khỏi quick_han_su_dung *** -->
                                            <input type="date" class="form-control form-control-sm" id="quick_han_su_dung" name="quick_han_su_dung">
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
            <!-- Thay thế phần modal body để cải thiện validation -->
            <div class="modal-body">
                <div class="row mb-3">
                    <div class="col-md-6">
                        <label for="modal_thuoc_id" class="form-label required-field">Chọn thuốc</label>
                        <select class="form-select" id="modal_thuoc_id" name="modal_thuoc_id">
                            <option value="">-- Chọn thuốc --</option>
                            @foreach($thuocs as $thuoc)
                            <option value="{{ $thuoc->thuoc_id }}" 
                                data-don-vi-goc="{{ $thuoc->don_vi_goc }}"
                                data-ten-thuoc="{{ $thuoc->ten_thuoc }}">{{ $thuoc->ten_thuoc }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-md-6">
                        <label for="modal_kho_id" class="form-label required-field">Chọn kho</label>
                        <select class="form-select" id="modal_kho_id" name="modal_kho_id">
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
                        <div class="mt-2" style="display: none;">
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
                            <label for="modal_so_lo" class="form-label required-field">Số lô</label>
                            <input type="text" class="form-control" id="modal_so_lo" placeholder="Yêu cầu nhập mã lô" required>
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
                            <!-- *** Chỉ set required khi đang ở chế độ lô mới *** -->
                            <input type="date" class="form-control" id="modal_han_su_dung" name="modal_han_su_dung">
                        </div>
                    </div>
                </div>

                <div class="row mb-3">
                    <div class="col-md-6">
                        <label for="modal_so_luong" class="form-label required-field">Số lượng</label>
                        <div class="input-group">
                            <input type="number" step="0.01" min="0.01" class="form-control" id="modal_so_luong" name="modal_so_luong">
                            <span class="input-group-text" id="modal_don_vi_display"></span>
                            <span class="input-group-text bg-light text-secondary" id="modal_ton_kho_info">
                                <small>Tồn: 0 | Sau: 0</small>
                            </span>
                        </div>
                        <small class="text-muted" id="modal_ton_kho_detail"></small>
                    </div>
                    <div class="col-md-6">
                        <label for="modal_don_vi" class="form-label required-field">Đơn vị</label>
                        <input type="text" class="form-control bg-light" id="modal_don_vi" name="modal_don_vi" readonly>
                    </div>
                </div>

                <div class="row mb-3">
                    <div class="col-md-4">
                        <label for="modal_gia_nhap" class="form-label required-field">Giá nhập</label>
                        <div class="input-group">
                            <input type="number" step="1" min="0" class="form-control" id="modal_gia_nhap" name="modal_gia_nhap">
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
        console.log('Document ready initialized');
        // Biến toàn cục để lưu index của các chi tiết
        let rowIndex = 0;
        let selectedThuocData = null;

        // Xử lý sự kiện khi chọn kho
        $('#modal_kho_id').on('change', function() {
            console.log('Kho được chọn thay đổi');
            const khoId = $(this).val();
            const thuocId = $('#modal_thuoc_id').val();
            
            if (thuocId && khoId) {
                console.log('Cả thuốc và kho đã được chọn, kiểm tra tồn kho');
                checkInventory();
            } else {
                console.log('Chưa đủ thông tin thuốc và kho để kiểm tra tồn kho');
                $('#modal_ton_kho_info').html('<small>Tồn: 0 | Sau: 0</small>');
                $('#modal_ton_kho_detail').text('');
                currentTotalStock = 0;
            }
        });

        // Debug: Kiểm tra sự tồn tại của select thuốc
        console.log('Select thuốc exists:', $('#modal_thuoc_id').length);

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

        // Hàm xử lý khi chọn thuốc
        function handleThuocChange() {
            console.log('Thuốc được chọn thay đổi');
            const selectedOption = $(this).find('option:selected');
            const thuocId = $(this).val();
            const donViGoc = selectedOption.data('don-vi-goc') || '';

            console.log('Đơn vị gốc của thuốc được chọn:', donViGoc);
            $('#modal_don_vi').val(donViGoc);
            $('#modal_don_vi_display').text(donViGoc);
            
            // Reset và disable select kho
            $('#modal_kho_id').prop('disabled', true).html('<option value="">Đang tải...</option>');
            
            // Reset thông tin tồn kho khi chọn thuốc mới
            $('#modal_ton_kho_info').html('<small>Tồn: 0 | Sau: 0</small>');
            $('#modal_ton_kho_detail').text('');
            currentTotalStock = 0;
            
            if (thuocId) {
                // Lấy danh sách kho
                $.get(`{{ route('api.thuoc.kho', ['id' => '_ID_']) }}`.replace('_ID_', thuocId), function(response) {
                    console.log('Danh sách kho:', response);
                    let options = '<option value="">-- Chọn kho --</option>';
                    
                    if (response.success) {
                        const allKho = response.data.all_kho;
                        const existingKho = response.data.existing_kho;
                        
                        if (existingKho && existingKho.length > 0) {
                            // Nếu thuốc đã có trong một số kho, chỉ hiển thị những kho đó
                            console.log('Thuốc đã có trong các kho:', existingKho);
                            existingKho.forEach(kho => {
                                options += `<option value="${kho.kho_id}">${kho.ten_kho} (Đã có lô)</option>`;
                            });
                            showToast('Chỉ hiển thị các kho đã có lô của thuốc này', 'info');
                        } else {
                            // Nếu thuốc chưa có trong kho nào, hiển thị tất cả các kho
                            console.log('Thuốc chưa có trong kho nào, hiển thị tất cả kho');
                            allKho.forEach(kho => {
                                options += `<option value="${kho.kho_id}">${kho.ten_kho}</option>`;
                            });
                            showToast('Thuốc chưa có trong kho nào, bạn có thể chọn bất kỳ kho nào', 'info');
                        }
                        
                        $('#modal_kho_id').prop('disabled', false).html(options);
                    } else {
                        $('#modal_kho_id').prop('disabled', true)
                            .html('<option value="">-- Lỗi tải dữ liệu --</option>');
                        showToast('Không thể tải danh sách kho', 'error');
                    }
                });
            } else {
                // Reset select kho khi không chọn thuốc
                $('#modal_kho_id').prop('disabled', true)
                    .html('<option value="">-- Chọn thuốc trước --</option>');
            }
            
            // Tự động kiểm tra tồn kho
            checkInventory();
        }
        
        // Gắn sự kiện change cho select thuốc và kho
        $('#modal_thuoc_id').off('change').on('change', function() {
            console.log('Sự kiện change của thuốc được gọi');
            handleThuocChange.call(this);
        });

        // Gắn sự kiện change cho select kho
        $('#modal_kho_id').off('change').on('change', function() {
            console.log('Sự kiện change của kho được gọi');
            const khoId = $(this).val();
            const thuocId = $('#modal_thuoc_id').val();

            console.log('Kho được chọn:', khoId);
            console.log('Thuốc hiện tại:', thuocId);
            
            if (thuocId && khoId) {
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
            console.log('Kiểm tra tồn kho được gọi');
            const thuocId = $('#modal_thuoc_id').val();
            const khoId = $('#modal_kho_id').val();
            const donVi = $('#modal_don_vi').val() || '';

            // Reset giá trị tồn kho mặc định
            currentTotalStock = 0;
            $('#modal_ton_kho_info').html(`<small>Tồn: 0 | Sau: 0 ${donVi}</small>`);
            $('#modal_ton_kho_detail').text('');
            $('#quick-product-total-stock').text(`Tồn: 0 ${donVi}`);

            if (!thuocId || !khoId) {
                console.log('Thiếu thông tin thuốc hoặc kho, không thể kiểm tra tồn kho');
                return;
            }

            console.log('Đang kiểm tra tồn kho cho thuốc:', thuocId, 'tại kho:', khoId);

            // Gọi API để lấy thông tin tồn kho
            $.ajax({
                url: "{{ route('phieu-nhap.get-ton-kho') }}",
                type: "GET",
                data: {
                    thuoc_id: thuocId,
                    kho_id: khoId
                },
                success: function(response) {
                    console.log('Kết quả API tồn kho:', response);
                    const tonKho = response.tonKho;
                    const thuoc = response.thuoc;

                    if (tonKho && tonKho.length > 0) {
                        // Tính tổng tồn kho một lần duy nhất
                        const totalStock = tonKho.reduce((sum, item) => sum + parseFloat(item.ton_kho_hien_tai || 0), 0);
                        
                        // Tạo container cho danh sách lô với tiêu đề có thể click
                        const detailContainer = `
                            <div class="lot-list-container">
                                <div class="d-flex align-items-center mb-2" id="toggleLotList" style="cursor: pointer;">
                                    <i class="bi bi-caret-right-fill me-2"></i>
                                    <span class="fw-bold">Danh sách lô (${tonKho.length})</span>
                                </div>
                                <div id="lotListDetail" style="display: none; padding-left: 20px;">
                                    ${tonKho.map(item => 
                                        `<div class="lot-item">Lô ${item.ma_lo}: ${item.ton_kho_hien_tai} ${thuoc.don_vi_goc}</div>`
                                    ).join('')}
                                </div>
                            </div>
                        `;

                        // Cập nhật biến toàn cục và giao diện
                        currentTotalStock = totalStock;
                        const soLuong = parseFloat($('#modal_so_luong').val()) || 0;

                        console.log('Cập nhật thông tin tồn kho:', {
                            totalStock,
                            donViGoc: thuoc.don_vi_goc,
                        });

                        // Cập nhật giao diện
                        updateStockInfo(soLuong);
                        $('#modal_ton_kho_detail').html(detailContainer);
                        
                        // Thêm sự kiện click để toggle danh sách
                        $('#toggleLotList').off('click').on('click', function() {
                            const icon = $(this).find('i.bi');
                            const lotList = $('#lotListDetail');
                            
                            if (lotList.is(':visible')) {
                                icon.removeClass('bi-caret-down-fill').addClass('bi-caret-right-fill');
                                lotList.slideUp();
                            } else {
                                icon.removeClass('bi-caret-right-fill').addClass('bi-caret-down-fill');
                                lotList.slideDown();
                            }
                        });

                        // Sự kiện toggle chi tiết lô
                        $('#modal_ton_kho_detail').off('click', '.toggle-lot-detail').on('click', '.toggle-lot-detail', function() {
                            const idx = $(this).data('lot-index');
                            const detailDiv = $(this).closest('.lot-summary').find('.lot-detail');
                            if (detailDiv.is(':visible')) {
                                detailDiv.slideUp();
                                $(this).text('Xem chi tiết');
                            } else {
                                detailDiv.slideDown();
                                $(this).text('Thu gọn');
                            }
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

        // Xử lý khi chọn thuốc
        $('#modal_thuoc_id').change(function() {

            console.log('Thuốc được chọn thay đổi');
            const selectedOption = $(this).find('option:selected');
            const thuocId = $(this).val();
            
            if (thuocId) {
                const donViGoc = selectedOption.data('don-vi-goc');
                console.log('Selected thuoc:', {
                    thuocId: thuocId,
                    donViGoc: donViGoc
                });

                if (donViGoc) {
                    // Tự động điền đơn vị gốc
                    $('#modal_don_vi').val(donViGoc);
                    $('#modal_don_vi_display').text(donViGoc);
                } else {
                    // Nếu không có đơn vị gốc trong data attribute, gọi API để lấy thông tin
                    $.get(`{{ route('api.thuoc.info') }}/${thuocId}`, function(response) {
                        console.log('API response:', response);
                        if (response && response.don_vi_goc) {
                            $('#modal_don_vi').val(response.don_vi_goc);
                            $('#modal_don_vi_display').text(response.don_vi_goc);
                        }
                    });
                }
            } else {
                // Reset đơn vị nếu không chọn thuốc
                $('#modal_don_vi').val('');
                $('#modal_don_vi_display').text('');
            }
            
            // Tự động kiểm tra tồn kho
            checkInventory();
        });

        // Hàm tải danh sách lô hiện có của thuốc và kho đã chọn
        function loadExistingLots(thuocId, khoId) {
            // Hiển thị loading
            console.log('Tải lô hiện có cho thuốc:', thuocId, 'và kho:', khoId);
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
                    console.log('Lô hiện có:', lots);
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
                                data-don-vi-goc="${lot.thuoc ? lot.thuoc.don_vi_goc : donVi}">
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

                // *** QUAN TRỌNG: Điền thông tin từ lô đã chọn vào form ***
                $('#modal_thuoc_id').val(thuocId);
                $('#modal_kho_id').val(khoId);
                // Lấy đơn vị gốc từ option đã chọn hoặc từ lô hiện tại
                const donViGoc = selectedOption.data('don-vi-goc') || donVi;
                // Đảm bảo đơn vị luôn được điền
                $('#modal_don_vi').val(donViGoc);
                $('#modal_don_vi_display').text(donViGoc);
                $('#modal_gia_nhap').val(giaNhap);

                // *** Điền hạn sử dụng từ lô hiện có vào trường modal_han_su_dung ***
                $('#modal_han_su_dung').val(hanSD);
                
                // Gọi API để lấy thông tin tồn kho của tất cả các lô của thuốc này trong kho
                $.ajax({
                    url: "{{ route('phieu-nhap.get-ton-kho') }}",
                    type: "GET",
                    data: {
                        thuoc_id: thuocId,
                        kho_id: khoId
                    },
                    success: function(response) {
                        const allLots = response.tonKho;
                        const thuoc = response.thuoc;
                        
                        // Tính tổng tồn kho từ tất cả các lô
                        currentTotalStock = allLots.reduce((sum, item) => sum + parseFloat(item.ton_kho_hien_tai || 0), 0);
                        const soLuong = parseFloat($('#modal_so_luong').val()) || 0;
                        
                        // Cập nhật thông tin tồn tổng
                        updateStockInfo(soLuong);

                        // Tạo container cho danh sách lô với tiêu đề có thể click
                        const detailContainer = `
                            <div class="lot-list-container">
                                <div class="d-flex align-items-center mb-2" id="toggleLotList" style="cursor: pointer;">
                                    <i class="bi bi-caret-right-fill me-2"></i>
                                    <span class="fw-bold">Danh sách lô (${allLots.length})</span>
                                </div>
                                <div id="lotListDetail" style="display: none; padding-left: 20px;">
                                    ${allLots.map(item => 
                                        `<div class="lot-item">Lô ${item.ma_lo}: ${item.ton_kho_hien_tai} ${thuoc.don_vi_goc}</div>`
                                    ).join('')}
                                </div>
                            </div>
                        `;

                        // Cập nhật giao diện
                        $('#modal_ton_kho_detail').html(detailContainer);
                        
                        // Thêm sự kiện click để toggle danh sách
                        $('#toggleLotList').off('click').on('click', function() {
                            const icon = $(this).find('i.bi');
                            const lotList = $('#lotListDetail');
                            
                            if (lotList.is(':visible')) {
                                icon.removeClass('bi-caret-down-fill').addClass('bi-caret-right-fill');
                                lotList.slideUp();
                            } else {
                                icon.removeClass('bi-caret-right-fill').addClass('bi-caret-down-fill');
                                lotList.slideDown();
                            }
                        });
                    },
                    error: function() {
                        currentTotalStock = parseFloat(tonKho);
                        const soLuong = parseFloat($('#modal_so_luong').val()) || 0;
                        updateStockInfo(soLuong);
                        $('#modal_ton_kho_detail').html(`Lô ${maLo}: ${tonKho} ${donVi}`);
                    }
                });

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
                        console.log('Lịch sử lô:', response);
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

                // Vô hiệu hóa trường thuốc, kho và hạn sử dụng vì đã được điền từ lô hiện có
                $('#modal_thuoc_id').prop('disabled', true);
                $('#modal_kho_id').prop('disabled', true);
                $('#modal_han_su_dung').prop('disabled', true);
            } else {
                // Nếu không chọn lô nào, kích hoạt lại các trường
                $('#modal_thuoc_id').prop('disabled', false);
                $('#modal_kho_id').prop('disabled', false);
                $('#modal_han_su_dung').prop('disabled', false);
                $('#selected_lot_info').html('');

                // Reset các giá trị
                $('#modal_han_su_dung').val('');
                $('#modal_gia_nhap').val('');
            }
        });

        // Chuyển đổi giữa lô mới và lô hiện có
        $('input[name="lot_option"]').change(function() {
            if ($(this).val() === 'new') {
                $('#new_lot_container').show();
                $('#existing_lot_container').hide();
                // Kích hoạt lại các trường chọn thuốc, kho và hạn sử dụng
                $('#modal_thuoc_id').prop('disabled', false);
                $('#modal_kho_id').prop('disabled', false);
                $('#modal_han_su_dung').prop('disabled', false);
                // Reset giá trị lô đã chọn
                $('#modal_existing_lot_id').val('');
                $('#selected_lot_info').html('');
                // Reset hạn sử dụng và giá nhập
                $('#modal_han_su_dung').val('');
                $('#modal_gia_nhap').val('');
                // Reset thông tin tồn kho
                $('#modal_ton_kho_info').html('<small>Tồn: 0 | Sau: 0</small>');
                $('#modal_ton_kho_detail').text('');
                currentTotalStock = 0;
                // Cho phép checkInventory khi chọn thuốc và kho
                $('#modal_thuoc_id, #modal_kho_id').off('change').on('change', function() {
                    checkInventory();
                });
            } else {
                $('#new_lot_container').hide();
                $('#existing_lot_container').show();
                // Nếu đã chọn lô, vô hiệu hóa trường thuốc, kho và hạn sử dụng
                if ($('#modal_existing_lot_id').val()) {
                    $('#modal_thuoc_id').prop('disabled', true);
                    $('#modal_kho_id').prop('disabled', true);
                    $('#modal_han_su_dung').prop('disabled', true);
                }
                // Tắt sự kiện checkInventory khi chọn thuốc và kho
                $('#modal_thuoc_id, #modal_kho_id').off('change');
            }
        });

        let currentTotalStock = 0; // Biến lưu tổng tồn kho hiện tại
        
        // Hàm cập nhật thông tin tồn kho
        function updateStockInfo(soLuong) {
            const afterStock = currentTotalStock + soLuong;
            const donVi = $('#modal_don_vi').val() || '';
            console.log(`Cập nhật tồn kho: Tồn hiện tại = ${currentTotalStock}, Nhập thêm = ${soLuong}, Tồn sau = ${afterStock} ${donVi}`);
            $('#modal_ton_kho_info').html(
                `<small>Tồn: ${currentTotalStock} | Sau: ${afterStock} ${donVi}</small>`
            );
        }

        // Tính tiền và cập nhật thông tin tồn kho khi thay đổi số lượng hoặc giá
        $('#modal_so_luong, #modal_gia_nhap, #modal_thue_suat').on('input', function() {
            const soLuong = parseFloat($('#modal_so_luong').val()) || 0;
            const giaNhap = parseFloat($('#modal_gia_nhap').val()) || 0;
            const thueSuat = parseFloat($('#modal_thue_suat').val()) || 0;

            const tienHang = soLuong * giaNhap;
            const tienThue = tienHang * (thueSuat / 100);
            const thanhTien = tienHang + tienThue;

            $('#modal_tien_thue').val(tienThue);
            $('#modal_thanh_tien').val(thanhTien.toFixed(0));

            // Cập nhật thông tin tồn kho khi thay đổi số lượng
            if ($(this).attr('id') === 'modal_so_luong') {
                updateStockInfo(soLuong);
            }
        });

        // Mở modal thêm thuốc
        $('#addItemBtn').click(function() {
            console.log('Opening add item modal');
            
            // Reset form
            $('#modal_thuoc_id').val('').prop('disabled', false);
            $('#modal_kho_id').val('').prop('disabled', true)
                .html('<option value="">-- Chọn thuốc trước --</option>');
            
            $('#modal_so_lo').val('');
            $('#modal_so_lo_nha_san_xuat').val('');
            $('#modal_ngay_san_xuat').val('');
            $('#modal_han_su_dung').val('').prop('disabled', false);
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

            // Reset thông tin tồn kho
            $('#modal_ton_kho_info').html('<small>Tồn: 0 | Sau: 0</small>');
            $('#modal_ton_kho_detail').text('');
            currentTotalStock = 0;

            // Ẩn thông tin tồn kho
            $('#inventory-container').hide();

            // Hiển thị modal
            $('#addItemModal').modal('show');
            $('#modal_so_lo').val('');
            $('#modal_so_lo_nha_san_xuat').val('');
            $('#modal_ngay_san_xuat').val('');
            $('#modal_han_su_dung').val('').prop('disabled', false); // *** Reset và enable hạn sử dụng ***
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

            // Reset thông tin tồn kho
            $('#modal_ton_kho_info').html('<small>Tồn: 0 | Sau: 0</small>');
            $('#modal_ton_kho_detail').text('');
            currentTotalStock = 0;

            // Ẩn thông tin tồn kho
            $('#inventory-container').hide();

            // Event handler already set in document.ready

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

            // Luôn lấy số lượng từ người dùng nhập vào
            soLuong = $('#modal_so_luong').val();

            if (isNewLot) {
                // Lô mới - lấy thông tin từ các trường nhập
                thuocId = $('#modal_thuoc_id').val();
                const selectedThuocOption = $('#modal_thuoc_id option:selected');
                thuocText = selectedThuocOption.text();
                khoId = $('#modal_kho_id').val();
                khoText = $('#modal_kho_id option:selected').text();
                // Lấy đơn vị gốc từ data attribute của option đã chọn
                donVi = selectedThuocOption.data('don-vi-goc') || $('#modal_don_vi').val();
                giaNhap = $('#modal_gia_nhap').val();
                thueSuat = $('#modal_thue_suat').val() || '0';

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

                console.log('Selected existing lot:', selectedLot);

                loId = selectedLot;
                thuocId = $('#modal_existing_lot_thuoc_id').val();
                thuocText = $('#modal_existing_lot_thuoc_text').val();
                khoId = $('#modal_existing_lot_kho_id').val();
                khoText = $('#modal_existing_lot_kho_text').val();
                // Lấy đơn vị gốc từ thuốc đã chọn
                donVi = $('#modal_thuoc_id option:selected').data('don-vi-goc') || '';
                soLo = $('#modal_existing_lot_ma_lo').val();
                soLoNSX = $('#modal_existing_lot_so_lo_nsx').val();
                ngaySX = $('#modal_existing_lot_ngay_sx').val();
                hanSD = $('#modal_existing_lot_han_sd').val();
                ghiChu = $('#modal_existing_lot_ghi_chu').val();

                // *** QUAN TRỌNG: Lấy giá nhập và thuế suất từ form modal ***
                // Đối với lô hiện có, cho phép người dùng thay đổi giá nhập và thuế suất
                giaNhap = $('#modal_gia_nhap').val();
                thueSuat = $('#modal_thue_suat').val() || '0';

                console.log('Existing lot values:', {
                    giaNhap,
                    thueSuat,
                    donVi,
                    soLuong
                });

                // Nếu không có giá nhập được nhập, báo lỗi
                if (!giaNhap) {
                    showToast('Vui lòng nhập giá nhập cho lô hiện có', 'warning');
                    return;
                }
            }

            // Đảm bảo lấy đơn vị gốc từ thuốc đã chọn cho cả 2 trường hợp
            const selectedThuocOption = $('#modal_thuoc_id option:selected');
            donVi = selectedThuocOption.data('don-vi-goc');
            
            // Validate các trường bắt buộc chung
            console.log('Validating required fields:', {
                thuocId,
                khoId,
                soLuong,
                donVi,
                giaNhap,
                isNewLot
            });

            if (!thuocId) {
                showToast('Vui lòng chọn thuốc', 'warning');
                return;
            }
            if (!khoId) {
                showToast('Vui lòng chọn kho', 'warning');
                return;
            }
            if (!soLuong || soLuong <= 0) {
                showToast('Vui lòng nhập số lượng hợp lệ', 'warning');
                return;
            }
            if (!giaNhap || giaNhap <= 0) {
                showToast('Vui lòng nhập giá nhập hợp lệ', 'warning');
                return;
            }
            if (!donVi) {
                showToast('Không lấy được đơn vị gốc của thuốc. Vui lòng chọn lại thuốc', 'warning');
                return;
            }

            // *** QUAN TRỌNG: Validate hạn sử dụng cho cả hai trường hợp ***
            if (!hanSD) {
                showToast('Hạn sử dụng là thông tin bắt buộc', 'warning');
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

        // Xử lý submit form bình thường
        $('#createPhieuNhapForm').submit(function(e) {
            // Kiểm tra có thuốc nào được thêm chưa
            if ($('.detail-row').length === 0) {
                e.preventDefault();
                showToast('Vui lòng thêm ít nhất một thuốc vào phiếu nhập', 'warning');
                return false;
            }

            // Hiển thị loading trên nút submit
            $('#submitBtn').html('<span class="spinner-border spinner-border-sm me-1" role="status" aria-hidden="true"></span> Đang xử lý...');
            $('#submitBtn').prop('disabled', true);

            // Form sẽ được submit bình thường
            return true;
        });
    });
</script>
@endsection