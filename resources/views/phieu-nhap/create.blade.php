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
                    <button type="button" class="btn btn-sm btn-light" id="addItemBtn">
                        <i class="bi bi-plus-circle me-1"></i> Thêm Thuốc
                    </button>
                </div>
                <div class="card-body">
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
                                            Chưa có thuốc nào được thêm vào phiếu nhập.
                                            <br>Nhấn "Thêm Thuốc" để bắt đầu.
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
        
        // Format số thành định dạng tiền tệ
        function formatCurrency(value) {
            return new Intl.NumberFormat('vi-VN').format(value) + ' đ';
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
            $('#modal_thuoc_id').val('');
            $('#modal_kho_id').val('');
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
            
            // Ẩn thông tin tồn kho
            $('#inventory-container').hide();
            
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
            // Validate các trường bắt buộc
            const thuocId = $('#modal_thuoc_id').val();
            const thuocText = $('#modal_thuoc_id option:selected').text();
            const khoId = $('#modal_kho_id').val();
            const khoText = $('#modal_kho_id option:selected').text();
            const soLo = $('#modal_so_lo').val();
            const soLoNSX = $('#modal_so_lo_nha_san_xuat').val();
            const ngaySX = $('#modal_ngay_san_xuat').val();
            const hanSD = $('#modal_han_su_dung').val();
            const soLuong = $('#modal_so_luong').val();
            const donVi = $('#modal_don_vi').val();
            const giaNhap = $('#modal_gia_nhap').val();
            const thueSuat = $('#modal_thue_suat').val();
            const ghiChu = $('#modal_ghi_chu').val();
            
            if (!thuocId || !khoId || !hanSD || !soLuong || !donVi || !giaNhap) {
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
                            Lô: ${soLo || 'Tạo tự động'} ${soLoNSX ? '/ NSX: ' + soLoNSX : ''}
                        </div>
                        <div class="small text-muted">
                            ${ngaySX ? 'NSX: ' + ngaySX : ''} | HSD: ${hanSD}
                        </div>
                        <input type="hidden" name="thuoc_id[]" value="${thuocId}">
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
                            <i class="bi bi-trash"></i>
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
        
        // Xóa dòng chi tiết
        $(document).on('click', '.btn-remove', function() {
            $(this).closest('tr').remove();
            
            // Kiểm tra xem còn dòng nào không
            if ($('.detail-row').length === 0) {
                $('#detailsTable tbody').html(`
                    <tr id="empty-row">
                        <td colspan="7" class="text-center py-3">
                            <div class="alert alert-info mb-0">
                                Chưa có thuốc nào được thêm vào phiếu nhập.
                                <br>Nhấn "Thêm Thuốc" để bắt đầu.
                            </div>
                        </td>
                    </tr>
                `);
            }
            
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
