@extends('layouts.app')

@section('title', 'Tạo đơn bán lẻ')

@section('styles')
<style>
    .thuoc-row {
        background-color: #f8f9fa;
        padding: 10px;
        margin-bottom: 10px;
        border-radius: 5px;
    }
    .item-row {
        margin-bottom: 15px;
    }
    #search_results {
        position: absolute;
        width: 100%;
        max-height: 300px;
        overflow-y: auto;
        z-index: 1000;
        background: white;
        border: 1px solid #ddd;
        border-radius: 4px;
        display: none;
    }
    .search-item {
        padding: 8px;
        border-bottom: 1px solid #eee;
        cursor: pointer;
    }
    .search-item:hover {
        background-color: #f5f5f5;
    }
    .error-msg {
        color: red;
        font-size: 12px;
    }
    .lo-select {
        margin-top: 5px;
    }
    .don-vi-select {
        margin-top: 5px;
    }
    .table-cart th, .table-cart td {
        vertical-align: middle;
    }
    .btn-remove {
        color: red;
        cursor: pointer;
    }
    .product-item {
        cursor: pointer;
        transition: background-color 0.2s;
    }
    .product-item:hover {
        background-color: #f0f0f0;
    }
    .editable-cell {
        cursor: pointer;
    }
    .editable-cell:hover {
        background-color: #f8f9fa;
    }
    .editable-cell input {
        width: 100%;
        border: 1px solid #007bff;
    }
    .cart-controls i {
        cursor: pointer;
        margin: 0 3px;
    }
    .cart-controls .bi-dash-circle {
        color: #dc3545;
    }
    .cart-controls .bi-plus-circle {
        color: #28a745;
    }
    .cart-controls .bi-trash {
        color: #dc3545;
    }
    .stock-warning {
        color: #dc3545;
        font-size: 12px;
        display: none;
    }
</style>
@endsection

@section('content')
<div class="container-fluid">
    <div class="row mb-2">
        <div class="col-12">
            <a href="{{ route('don-ban-le.index') }}" class="btn btn-secondary">
                <i class="bi bi-arrow-left"></i> Quay lại
            </a>
        </div>
    </div>

    @if(session('error'))
    <div class="alert alert-danger alert-dismissible">
        <button type="button" class="close" data-dismiss="alert" aria-hidden="true">×</button>
        <h5><i class="bi bi-exclamation-triangle"></i> Lỗi!</h5>
        {{ session('error') }}
    </div>
    @endif

    <div class="card">
        <div class="card-header">
            <h3 class="card-title">Tạo đơn bán lẻ mới</h3>
        </div>
        <!-- /.card-header -->
        <div class="card-body">
            <form id="donBanLeForm" action="{{ route('don-ban-le.store') }}" method="POST">
                @csrf
                
                <div class="row">
                    <div class="col-md-6">
                        <div class="form-group">
                            <label for="ma_don">Mã đơn</label>
                            <input type="text" class="form-control" id="ma_don" name="ma_don" value="{{ $maDon }}" readonly>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="form-group">
                            <label for="ngay_ban">Ngày bán</label>
                            <input type="date" class="form-control" id="ngay_ban" name="ngay_ban" value="{{ date('Y-m-d') }}">
                        </div>
                    </div>
                </div>

                <div class="row">
                    <div class="col-md-12">
                        <div class="card">
                            <div class="card-header">
                                <h3 class="card-title">Thông tin khách hàng</h3>
                            </div>
                            <div class="card-body">
                                <div class="row">
                                    <div class="col-md-6">
                                        <div class="form-group">
                                            <label for="sdt_khach">Số điện thoại</label>
                                            <div class="input-group">
                                                <input type="text" class="form-control" id="sdt_khach" placeholder="Nhập số điện thoại khách hàng">
                                                <div class="input-group-append">
                                                    <button type="button" class="btn btn-primary" id="btnSearchCustomer">
                                                        <i class="bi bi-search"></i> Tìm
                                                    </button>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="col-md-6">
                                        <div class="form-group">
                                            <label for="ten_khach">Tên khách hàng</label>
                                            <input type="text" class="form-control" id="ten_khach" placeholder="Tên khách hàng">
                                            <input type="hidden" id="khach_hang_id" name="khach_hang_id">
                                        </div>
                                    </div>
                                </div>
                                <div class="row">
                                    <div class="col-md-12">
                                        <button type="button" id="btnSaveCustomer" class="btn btn-success" style="display: none;">
                                            <i class="bi bi-save"></i> Lưu thông tin khách hàng
                                        </button>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="row">
                    <div class="col-md-12">
                        <div class="card">
                            <div class="card-header">
                                <h3 class="card-title">Tìm kiếm thuốc</h3>
                            </div>
                            <div class="card-body">
                                <div class="row">
                                    <div class="col-md-12">
                                        <div class="form-group">
                                            <label>Chọn thuốc</label>
                                            <div class="position-relative">
                                                <select id="select_thuoc" class="form-control select2" style="width: 100%;">
                                                    <option value="">-- Chọn thuốc --</option>
                                                    @foreach($thuocs as $thuoc)
                                                        <option value="{{ $thuoc->thuoc_id }}" 
                                                            data-name="{{ $thuoc->ten_thuoc }}"
                                                            data-ma="{{ $thuoc->ma_thuoc }}">
                                                            {{ $thuoc->ma_thuoc }} - {{ $thuoc->ten_thuoc }}
                                                        </option>
                                                    @endforeach
                                                </select>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                
                                <div class="table-responsive mt-3">
                                    <table class="table table-bordered table-hover" id="thuoc_table">
                                        <thead class="thead-light">
                                            <tr>
                                                <th>Mã thuốc</th>
                                                <th>Tên sản phẩm</th>
                                                <th>Lô thuốc (HSD, tồn kho)</th>
                                                <th>Đơn vị</th>
                                                <th>Giá bán</th>
                                            </tr>
                                        </thead>
                                        <tbody id="thuoc_list">
                                            <!-- Products will be loaded here -->
                                            <tr>
                                                <td colspan="5" class="text-center">Chọn thuốc để xem thông tin chi tiết</td>
                                            </tr>
                                        </tbody>
                                    </table>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="row">
                    <div class="col-md-12">
                        <div class="card">
                            <div class="card-header">
                                <h3 class="card-title">Giỏ hàng</h3>
                            </div>
                            <div class="card-body">
                                <div class="table-responsive">
                                    <table class="table table-bordered table-cart">
                                        <thead>
                                            <tr>
                                                <th>STT</th>
                                                <th>Thuốc</th>
                                                <th>Lô</th>
                                                <th>Số lượng</th>
                                                <th>Đơn vị</th>
                                                <th>Đơn giá</th>
                                                <th>Thuế suất</th>
                                                <th>Tiền thuế</th>
                                                <th>Thành tiền</th>
                                                <th>Thao tác</th>
                                            </tr>
                                        </thead>
                                        <tbody id="cart_items">
                                            <tr id="cart_empty">
                                                <td colspan="10" class="text-center">Chưa có thuốc nào trong đơn</td>
                                            </tr>
                                        </tbody>
                                        <tfoot>
                                            <tr>
                                                <td colspan="8" class="text-right"><strong>Tổng tiền:</strong></td>
                                                <td colspan="2">
                                                    <span id="tong_tien_display">0</span> đ
                                                    <input type="hidden" name="tong_tien" id="tong_tien" value="0">
                                                </td>
                                            </tr>
                                            <tr>
                                                <td colspan="8" class="text-right"><strong>VAT:</strong></td>
                                                <td colspan="2">
                                                    <span id="vat_display">0</span> đ
                                                    <input type="hidden" name="vat" id="vat" value="0">
                                                </td>
                                            </tr>
                                            <tr>
                                                <td colspan="8" class="text-right"><strong>Tổng cộng:</strong></td>
                                                <td colspan="2">
                                                    <span id="tong_cong_display">0</span> đ
                                                    <input type="hidden" name="tong_cong" id="tong_cong" value="0">
                                                </td>
                                            </tr>
                                        </tfoot>
                                    </table>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="row">
                    <div class="col-md-12 text-right">
                        <button type="submit" class="btn btn-primary btn-lg" id="btnSubmit">
                            <i class="bi bi-save"></i> Lưu đơn bán lẻ
                        </button>
                    </div>
                </div>
            </form>
        </div>
        <!-- /.card-body -->
    </div>
</div>
@endsection

@section('scripts')
<script>
$(function() {
    // Initialize select2
    $('.select2').select2();
    
    // Khách hàng
    $('#btnSearchCustomer').click(function() {
        let sdt = $('#sdt_khach').val().trim();
        if (sdt) {
            $.ajax({
                url: '{{ route('don-ban-le.get-khach-hang') }}',
                type: 'GET',
                data: { sdt: sdt },
                success: function(res) {
                    if (res.exists) {
                        $('#ten_khach').val(res.khachHang.ho_ten);
                        $('#khach_hang_id').val(res.khachHang.khach_hang_id);
                        $('#btnSaveCustomer').hide();
                    } else {
                        $('#ten_khach').val('');
                        $('#khach_hang_id').val('');
                        $('#btnSaveCustomer').show();
                    }
                },
                error: function() {
                    alert('Có lỗi xảy ra khi tìm kiếm khách hàng');
                }
            });
        }
    });
    
    $('#btnSaveCustomer').click(function() {
        let sdt = $('#sdt_khach').val().trim();
        let ten = $('#ten_khach').val().trim();
        
        if (!sdt || !ten) {
            alert('Vui lòng nhập đầy đủ thông tin khách hàng');
            return;
        }
        
        $.ajax({
            url: '{{ route('don-ban-le.create-khach-hang') }}',
            type: 'POST',
            data: { 
                _token: $('meta[name="csrf-token"]').attr('content'),
                sdt: sdt, 
                ho_ten: ten 
            },
            success: function(res) {
                if (res.success) {
                    $('#khach_hang_id').val(res.khachHang.khach_hang_id);
                    $('#btnSaveCustomer').hide();
                    alert('Đã lưu thông tin khách hàng mới');
                }
            },
            error: function(xhr) {
                if (xhr.responseJSON && xhr.responseJSON.errors) {
                    let errors = xhr.responseJSON.errors;
                    let errorMsg = '';
                    for (let key in errors) {
                        errorMsg += errors[key][0] + '\n';
                    }
                    alert(errorMsg);
                } else {
                    alert('Có lỗi xảy ra khi lưu thông tin khách hàng');
                }
            }
        });
    });
    
    // Thuốc
    $('#select_thuoc').change(function() {
        let thuocId = $(this).val();
        if (!thuocId) {
            $('#thuoc_list').html('<tr><td colspan="5" class="text-center">Chọn thuốc để xem thông tin chi tiết</td></tr>');
            return;
        }
        
        // Get thuốc info
        $.ajax({
            url: '{{ route('don-ban-le.get-thuoc-info') }}',
            type: 'GET',
            data: { thuoc_id: thuocId },
            success: function(res) {
                let thuocName = $('#select_thuoc option:selected').data('name');
                let thuocMa = $('#select_thuoc option:selected').data('ma');
                let html = '';
                
                // Check if lothuoc is available
                if (res.loThuoc && res.loThuoc.length > 0) {
                    // Display all available lots for this product
                    res.loThuoc.forEach(function(lo) {
                        let ngayHetHan = new Date(lo.han_su_dung).toLocaleDateString('vi-VN');
                        
                        // Display row for original unit
                        html += `<tr class="product-item" data-thuoc-id="${thuocId}" data-thuoc-name="${thuocName}" 
                                data-lo-id="${lo.lo_id}" data-lo-text="${lo.ma_lo} (HSD: ${ngayHetHan}, Tồn: ${lo.ton_kho_hien_tai})" 
                                data-don-vi="0" data-don-vi-text="${res.donViGoc}" data-gia="${res.giaBan}" 
                                data-ton-kho="${lo.ton_kho_hien_tai}" data-ti-le="${res.tiLeQuyDoi}">
                            <td>${thuocMa}</td>
                            <td>${thuocName}</td>
                            <td>${lo.ma_lo} (HSD: ${ngayHetHan}, Tồn: ${lo.ton_kho_hien_tai})</td>
                            <td>${res.donViGoc}</td>
                            <td>${formatNumber(res.giaBan)} đ</td>
                        </tr>`;
                        
                        // Display row for retail unit if available
                        if (res.donViBan) {
                            let giaBanLe = res.giaBan / res.tiLeQuyDoi;
                            html += `<tr class="product-item" data-thuoc-id="${thuocId}" data-thuoc-name="${thuocName}" 
                                    data-lo-id="${lo.lo_id}" data-lo-text="${lo.ma_lo} (HSD: ${ngayHetHan}, Tồn: ${lo.ton_kho_hien_tai})" 
                                    data-don-vi="1" data-don-vi-text="${res.donViBan}" data-gia="${giaBanLe}" 
                                    data-ton-kho="${lo.ton_kho_hien_tai * res.tiLeQuyDoi}" data-ti-le="${res.tiLeQuyDoi}">
                                <td>${thuocMa}</td>
                                <td>${thuocName}</td>
                                <td>${lo.ma_lo} (HSD: ${ngayHetHan}, Tồn: ${lo.ton_kho_hien_tai})</td>
                                <td>${res.donViBan}</td>
                                <td>${formatNumber(giaBanLe)} đ</td>
                            </tr>`;
                        }
                    });
                    
                    $('#thuoc_list').html(html);
                } else {
                    $('#thuoc_list').html('<tr><td colspan="5" class="text-center text-warning">Thuốc này hiện không có lô nào còn hàng và chưa hết hạn</td></tr>');
                }
            },
            error: function() {
                alert('Có lỗi xảy ra khi lấy thông tin thuốc');
                $('#thuoc_list').html('<tr><td colspan="5" class="text-center text-danger">Lỗi khi tải thông tin thuốc</td></tr>');
            }
        });
    });
    
    // Click on product to add directly to cart
    $(document).on('click', '.product-item', function() {
        let thuocId = $(this).data('thuoc-id');
        let thuocName = $(this).data('thuoc-name');
        let loId = $(this).data('lo-id');
        let loText = $(this).data('lo-text');
        let donVi = $(this).data('don-vi');
        let donViText = $(this).data('don-vi-text');
        let giaBan = $(this).data('gia');
        let tonKho = $(this).data('ton-kho');
        let tiLe = $(this).data('ti-le');
        
        // Default values
        let soLuong = 1;
        let thueSuat = 0;
        let tienThue = 0;
        let thanhTien = soLuong * giaBan + tienThue;
        
        // Check inventory availability
        if (donVi == 0 && soLuong > tonKho) {
            alert(`Không đủ hàng trong kho. Tồn kho hiện tại: ${tonKho} ${donViText}`);
            return;
        } else if (donVi == 1 && soLuong > (tonKho * tiLe)) {
            alert(`Không đủ hàng trong kho. Tồn kho hiện tại: ${tonKho * tiLe} ${donViText}`);
            return;
        }
        
        // Add to cart
        addToCart(thuocId, thuocName, loId, loText, soLuong, donVi, donViText, giaBan, thueSuat, tienThue, thanhTien, tonKho, tiLe);
    });
    
    // Editable cells for quantity, tax rate
    $(document).on('click', '.editable-qty', function() {
        const value = $(this).text();
        const rowId = $(this).closest('tr').attr('id');
        const tonKho = $(this).closest('tr').data('ton-kho');
        const donVi = $(this).closest('tr').data('don-vi');
        const tiLe = $(this).closest('tr').data('ti-le');
        const maxValue = donVi == 0 ? tonKho : tonKho * tiLe;
        
        $(this).html(`<input type="number" value="${value}" min="0.01" step="0.01" max="${maxValue}" 
            class="form-control form-control-sm qty-input" data-row-id="${rowId}">`);
        $(this).find('input').focus().select();
    });
    
    $(document).on('click', '.editable-tax', function() {
        const value = $(this).text().replace('%', '');
        const rowId = $(this).closest('tr').attr('id');
        
        $(this).html(`<input type="number" value="${value}" min="0" max="100" step="0.1"
            class="form-control form-control-sm tax-input" data-row-id="${rowId}">`);
        $(this).find('input').focus().select();
    });
    
    // Handle input blur for quantity
    $(document).on('blur change', '.qty-input', function() {
        const rowId = $(this).data('row-id');
        const newValue = parseFloat($(this).val()) || 0;
        const row = $('#' + rowId);
        const donVi = row.data('don-vi');
        const tonKho = row.data('ton-kho');
        const tiLe = row.data('ti-le');
        const maxValue = donVi == 0 ? tonKho : tonKho * tiLe;
        
        if (newValue <= 0) {
            $(this).val(0.01);
            row.find('.editable-qty').text(0.01);
        } else if (newValue > maxValue) {
            $(this).val(maxValue);
            row.find('.editable-qty').text(maxValue);
            alert(`Số lượng vượt quá tồn kho. Tối đa: ${maxValue}`);
        } else {
            row.find('.editable-qty').text(newValue);
        }
        
        // Update cart item
        updateCartItem(rowId);
    });
    
    // Handle input blur for tax
    $(document).on('blur change', '.tax-input', function() {
        const rowId = $(this).data('row-id');
        const newValue = parseFloat($(this).val()) || 0;
        
        if (newValue < 0) {
            $(this).val(0);
            $('#' + rowId).find('.editable-tax').text('0%');
        } else if (newValue > 100) {
            $(this).val(100);
            $('#' + rowId).find('.editable-tax').text('100%');
        } else {
            $('#' + rowId).find('.editable-tax').text(newValue + '%');
        }
        
        // Update cart item
        updateCartItem(rowId);
    });
    
    // Handle quantity controls
    $(document).on('click', '.btn-decrease', function() {
        const rowId = $(this).closest('tr').attr('id');
        const qtyCell = $('#' + rowId).find('.editable-qty');
        let currentQty = parseFloat(qtyCell.text()) || 0;
        
        if (currentQty > 0.01) {
            currentQty -= 0.01;
            qtyCell.text(currentQty.toFixed(2));
            updateCartItem(rowId);
        }
    });
    
    $(document).on('click', '.btn-increase', function() {
        const rowId = $(this).closest('tr').attr('id');
        const row = $('#' + rowId);
        const qtyCell = row.find('.editable-qty');
        let currentQty = parseFloat(qtyCell.text()) || 0;
        const donVi = row.data('don-vi');
        const tonKho = row.data('ton-kho');
        const tiLe = row.data('ti-le');
        const maxValue = donVi == 0 ? tonKho : tonKho * tiLe;
        
        if (currentQty < maxValue) {
            currentQty += 0.01;
            if (currentQty > maxValue) currentQty = maxValue;
            qtyCell.text(currentQty.toFixed(2));
            updateCartItem(rowId);
        } else {
            alert(`Số lượng đã đạt tối đa. Tồn kho: ${maxValue}`);
        }
    });
    
    // Function to add item to cart
    function addToCart(thuocId, thuocName, loId, loText, soLuong, donVi, donViText, giaBan, thueSuat, tienThue, thanhTien, tonKho, tiLe) {
        // Check if item already exists in cart
        let existingRow = null;
        $('#cart_items tr').each(function() {
            if ($(this).data('thuoc-id') == thuocId && 
                $(this).data('lo-id') == loId && 
                $(this).data('don-vi') == donVi) {
                existingRow = $(this);
                return false;
            }
        });
        
        if (existingRow) {
            // Update quantity of existing item
            let currentQty = parseFloat(existingRow.find('.editable-qty').text()) || 0;
            let newQty = currentQty + soLuong;
            let maxQty = donVi == 0 ? tonKho : tonKho * tiLe;
            
            if (newQty > maxQty) {
                newQty = maxQty;
                alert(`Số lượng đã được điều chỉnh theo tồn kho tối đa (${maxQty})`);
            }
            
            existingRow.find('.editable-qty').text(newQty);
            updateCartItem(existingRow.attr('id'));
        } else {
            // Add new item to cart
            let itemCount = $('#cart_items tr').length;
            if ($('#cart_empty').length) {
                itemCount = 0;
                $('#cart_empty').remove();
            }
            
            let rowId = 'item_' + Date.now();
            let newRow = `
            <tr id="${rowId}" data-thuoc-id="${thuocId}" data-lo-id="${loId}" data-don-vi="${donVi}" 
                data-ton-kho="${tonKho}" data-ti-le="${tiLe}">
                <td>${itemCount + 1}</td>
                <td>${thuocName}</td>
                <td>${loText}</td>
                <td class="editable-qty">${soLuong}</td>
                <td>${donViText}</td>
                <td>${formatNumber(giaBan)}</td>
                <td class="editable-tax">${thueSuat}%</td>
                <td class="tien-thue">${formatNumber(tienThue)}</td>
                <td class="item-total">${formatNumber(thanhTien)}</td>
                <td class="cart-controls">
                    <i class="bi bi-dash-circle btn-decrease"></i>
                    <i class="bi bi-plus-circle btn-increase"></i>
                    <i class="bi bi-trash btn-remove" onclick="removeCartItem('${rowId}')"></i>
                    <input type="hidden" name="thuoc_id[]" value="${thuocId}">
                    <input type="hidden" name="lo_id[]" value="${loId}">
                    <input type="hidden" name="so_luong[]" value="${soLuong}">
                    <input type="hidden" name="don_vi[]" value="${donVi}">
                    <input type="hidden" name="gia_ban[]" value="${giaBan}">
                    <input type="hidden" name="thue_suat[]" value="${thueSuat}">
                    <input type="hidden" name="tien_thue[]" value="${tienThue}">
                    <input type="hidden" name="thanh_tien[]" value="${thanhTien}">
                </td>
            </tr>
            `;
            
            $('#cart_items').append(newRow);
            updateTotals();
        }
    }
    
    // Update cart item values
    function updateCartItem(rowId) {
        const row = $('#' + rowId);
        const soLuong = parseFloat(row.find('.editable-qty').text()) || 0;
        const giaBan = parseFloat(row.find('td:eq(5)').text().replace(/,/g, '')) || 0;
        const thueSuat = parseFloat(row.find('.editable-tax').text().replace('%', '')) || 0;
        
        // Calculate values
        const thanhTien = soLuong * giaBan;
        const tienThue = thanhTien * (thueSuat / 100);
        const total = thanhTien + tienThue;
        
        // Update displayed values
        row.find('.tien-thue').text(formatNumber(tienThue));
        row.find('.item-total').text(formatNumber(total));
        
        // Update hidden inputs
        row.find('input[name="so_luong[]"]').val(soLuong);
        row.find('input[name="thue_suat[]"]').val(thueSuat);
        row.find('input[name="tien_thue[]"]').val(tienThue);
        row.find('input[name="thanh_tien[]"]').val(total);
        
        // Update order totals
        updateTotals();
    }
    
    // Submit form
    $('#donBanLeForm').submit(function(e) {
        // Kiểm tra có thuốc nào trong giỏ không
        if ($('#cart_items tr').length === 0 || $('#cart_empty').length) {
            e.preventDefault();
            alert('Vui lòng thêm ít nhất một thuốc vào đơn hàng');
        }
    });
});

// Xóa thuốc khỏi giỏ hàng
function removeCartItem(rowId) {
    $('#' + rowId).remove();
    
    // Renumber the items
    $('#cart_items tr').each(function(index) {
        $(this).find('td:first').text(index + 1);
    });
    
    // If cart is empty, add empty row
    if ($('#cart_items tr').length === 0) {
        $('#cart_items').html('<tr id="cart_empty"><td colspan="10" class="text-center">Chưa có thuốc nào trong đơn</td></tr>');
    }
    
    // Update totals
    updateTotals();
}

// Update totals
function updateTotals() {
    let tongTien = 0;
    let tongThue = 0;
    
    $('.item-total').each(function() {
        tongTien += parseFloat($(this).text().replace(/,/g, '')) || 0;
    });
    
    $('.tien-thue').each(function() {
        tongThue += parseFloat($(this).text().replace(/,/g, '')) || 0;
    });
    
    let tongCong = tongTien;
    
    $('#tong_tien').val(tongTien - tongThue);
    $('#tong_tien_display').text(formatNumber(tongTien - tongThue));
    
    $('#vat').val(tongThue);
    $('#vat_display').text(formatNumber(tongThue));
    
    $('#tong_cong').val(tongCong);
    $('#tong_cong_display').text(formatNumber(tongCong));
}

// Format number with thousand separators
function formatNumber(num) {
    return num.toFixed(0).replace(/(\d)(?=(\d{3})+(?!\d))/g, '$1,');
}
</script>
@endsection
