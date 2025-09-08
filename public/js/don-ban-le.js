$(function () {
    'use strict';

    let selectedProduct = null;
    let orderItems = [];
    let searchTimeout;

    // Handle real-time product search
    $('#product_search').on('input', function() {
        const keyword = $(this).val().trim();
        clearTimeout(searchTimeout);

        if (!keyword) {
            $('#product-search-results').html('');
            $('#quick_add_product_id').val('');
            resetProductForm();
            return;
        }

        searchTimeout = setTimeout(function() {
            $('#product-search-results').html('<div class="text-center"><i class="bi bi-hourglass-split"></i> Đang tìm kiếm...</div>');
            
            $.ajax({
                url: '/don-ban-le-search-thuoc',
                type: 'GET',
                data: { keyword: keyword },
                success: function(response) {
                    if (response.success && response.thuocs.length > 0) {
                        displayProductSearchResults(response.thuocs);
                    } else {
                        $('#product-search-results').html(`
                            <div class="alert alert-warning">
                                <i class="bi bi-exclamation-circle-fill"></i> Không tìm thấy sản phẩm
                            </div>
                        `);
                        resetProductForm();
                    }
                },
                error: function() {
                    $('#product-search-results').html(`
                        <div class="alert alert-danger">
                            <i class="bi bi-exclamation-triangle-fill"></i> Lỗi khi tìm kiếm sản phẩm
                        </div>
                    `);
                    resetProductForm();
                }
            });
        }, 300);
    });

    // Display product search results
    function displayProductSearchResults(thuocs) {
        let html = '<div class="list-group">';
        thuocs.forEach(thuoc => {
            html += `
                <a href="#" class="list-group-item list-group-item-action product-item" 
                   data-id="${thuoc.thuoc_id}" data-name="${thuoc.ten_thuoc}">
                    <div class="d-flex w-100 justify-content-between">
                        <h6 class="mb-1">${thuoc.ten_thuoc}</h6>
                        <small>Mã: ${thuoc.ma_thuoc}</small>
                    </div>
                    <p class="mb-0">Đơn vị: ${thuoc.don_vi_ban} | Tồn kho: ${thuoc.tong_ton_kho} | Giá: ${formatCurrency(thuoc.gia_ban)}</p>
                </a>
            `;
        });
        html += '</div>';
        $('#product-search-results').html(html);
    }

    // Handle product selection
    $(document).on('click', '.product-item', function(e) {
        e.preventDefault();
        const thuocId = $(this).data('id');
        const thuocName = $(this).data('name');
        $('#product_search').val(thuocName);
        $('#quick_add_product_id').val(thuocId);
        $('#product-search-results').html('');

        $.ajax({
            url: '/don-ban-le-thuoc-info',
            type: 'GET',
            data: { thuoc_id: thuocId },
            success: function(response) {
                if (response.success) {
                    selectedProduct = response.thuoc;
                    selectedProduct.lo_thuocs = response.lo_thuocs;
                    showProductInfo(response.thuoc, response.lo_thuocs);
                    $('#quick_add_quantity').focus();
                } else {
                    showToast('error', 'Không thể tải thông tin thuốc', 'bi bi-exclamation-triangle-fill');
                    resetProductForm();
                }
            },
            error: function() {
                showToast('error', 'Lỗi khi tải thông tin thuốc', 'bi bi-exclamation-triangle-fill');
                resetProductForm();
            }
        });
    });

    // Show product info
    function showProductInfo(product, loThuocs) {
        const unitSelect = $('#quick_add_unit');
        unitSelect.empty().append('<option value="" disabled selected>Chọn đơn vị</option>');
        
        unitSelect.append(`<option value="don_vi_goc" data-ti-le="1">${product.don_vi_goc}</option>`);
        if (product.don_vi_ban && product.ti_le_quy_doi) {
            unitSelect.append(`<option value="don_vi_ban" data-ti-le="${product.ti_le_quy_doi}">${product.don_vi_ban}</option>`);
        }

        const batchSelect = $('#quick_add_batch');
        batchSelect.empty().append('<option value="" disabled selected>Chọn lô</option>');
        loThuocs.forEach(lo => {
            if (lo.ton_kho_hien_tai > 0) {
                const ngayHetHan = new Date(lo.han_su_dung).toLocaleDateString('vi-VN');
                batchSelect.append(`<option value="${lo.lo_id}" data-ton-kho="${lo.ton_kho_hien_tai}">
                    ${lo.ma_lo} (HSD: ${ngayHetHan}, Tồn: ${lo.ton_kho_hien_tai})
                </option>`);
            }
        });

        updateStockInfo();
        updatePriceInfo();
        validateForm();
    }

    // Update stock info
    function updateStockInfo() {
        const batchSelect = $('#quick_add_batch');
        const selectedBatch = batchSelect.find('option:selected');
        const unitSelect = $('#quick_add_unit');
        const selectedUnit = unitSelect.find('option:selected');

        if (selectedBatch.length && selectedUnit.length && selectedBatch.val()) {
            const tonKhoGoc = parseFloat(selectedBatch.data('ton-kho')) || 0;
            const tiLe = parseFloat(selectedUnit.data('ti-le')) || 1;
            const donVi = selectedUnit.text().trim();
            const tonKhoTheoDonVi = unitSelect.val() === 'don_vi_ban' ? tonKhoGoc * tiLe : tonKhoGoc;

            $('#stock-info').html(`<i class="bi bi-boxes"></i> <strong>Tồn kho:</strong> ${tonKhoTheoDonVi} ${donVi}`);
            $('#quick_add_quantity').attr('max', tonKhoTheoDonVi);
        } else {
            $('#stock-info').html('<i class="bi bi-info-circle"></i> Vui lòng chọn lô thuốc');
        }
    }

    // Update price and VAT info
    function updatePriceInfo() {
        if (!selectedProduct) return;

        const unitSelect = $('#quick_add_unit');
        const selectedUnit = unitSelect.find('option:selected');
        const quantity = parseFloat($('#quick_add_quantity').val()) || 0;

        if (selectedUnit.length) {
            const tiLe = parseFloat(selectedUnit.data('ti-le')) || 1;
            const donVi = selectedUnit.text().trim();
            const giaTheoDonVi = unitSelect.val() === 'don_vi_ban' ? selectedProduct.gia_ban / tiLe : selectedProduct.gia_ban;

            $('#product-price').html(`<i class="bi bi-currency-dollar"></i> <strong>Đơn giá:</strong> ${formatCurrency(giaTheoDonVi)}/${donVi}`);
            $('#product-vat').html(`<i class="bi bi-percent"></i> <strong>VAT:</strong> ${selectedProduct.vat || 0}%`);
        }
    }

    // Handle unit change
    $('#quick_add_unit').on('change', function() {
        updateStockInfo();
        updatePriceInfo();
        validateForm();
    });

    // Handle batch change
    $('#quick_add_batch').on('change', function() {
        updateStockInfo();
        updatePriceInfo();
        validateForm();
    });

    // Handle quantity change
    $('#quick_add_quantity').on('input', function() {
        const quantity = parseFloat($(this).val()) || 0;
        const max = parseFloat($(this).attr('max')) || 0;

        if (quantity > max) {
            $(this).val(max);
            showToast('warning', 'Số lượng không được vượt quá tồn kho', 'bi bi-exclamation-circle-fill');
        } else if (quantity <= 0) {
            showToast('warning', 'Số lượng phải lớn hơn 0', 'bi bi-exclamation-circle-fill');
        }

        updatePriceInfo();
        validateForm();
    });

    // Validate form
    function validateForm() {
        const product = $('#quick_add_product_id').val();
        const batch = $('#quick_add_batch').val();
        const quantity = parseFloat($('#quick_add_quantity').val()) || 0;
        const max = parseFloat($('#quick_add_quantity').attr('max')) || 0;

        const isValid = product && batch && quantity > 0 && quantity <= max;
        $('#add-to-list-btn').prop('disabled', !isValid);
    }

    // Add product to table
    $('#add-to-list-btn').on('click', function() {
        const batch = $('#quick_add_batch option:selected');
        const unit = $('#quick_add_unit option:selected');
        const quantity = parseFloat($('#quick_add_quantity').val());

        if (!selectedProduct || !batch.val() || !quantity) {
            showToast('error', 'Vui lòng điền đầy đủ thông tin', 'bi bi-exclamation-triangle-fill');
            return;
        }

        const tiLe = parseFloat(unit.data('ti-le')) || 1;
        const donGia = unit.val() === 'don_vi_ban' ? selectedProduct.gia_ban / tiLe : selectedProduct.gia_ban;
        const vat = selectedProduct.vat || 0;
        const thanhTien = donGia * quantity;
        const tienThue = thanhTien * (vat / 100);

        const existingItemIndex = orderItems.findIndex(item => item.lo_id === batch.val());
        if (existingItemIndex !== -1) {
            orderItems[existingItemIndex].so_luong += quantity;
            orderItems[existingItemIndex].thanh_tien += thanhTien;
            orderItems[existingItemIndex].tien_thue += tienThue;
        } else {
            orderItems.push({
                thuoc_id: selectedProduct.thuoc_id,
                ten_thuoc: selectedProduct.ten_thuoc,
                lo_id: batch.val(),
                ma_lo: batch.text().split(' ')[0],
                don_vi: unit.text().trim(),
                so_luong: quantity,
                gia_ban: donGia,
                thue_suat: vat,
                tien_thue: tienThue,
                thanh_tien: thanhTien
            });
        }

        updateOrderTable();
        showToast('success', 'Đã thêm sản phẩm vào đơn hàng', 'bi bi-check-circle-fill');
        resetProductForm();
        $('#quick_add_quantity').focus();
    });

    // Update order table with inline editing
    function updateOrderTable() {
        const tbody = $('#products-table tbody');
        tbody.empty();

        if (orderItems.length === 0) {
            tbody.append('<tr id="no-products-row"><td colspan="9" class="text-center">Chưa có sản phẩm nào</td></tr>');
            $('#total-amount').text('0 đ');
            return;
        }

        let totalAmount = 0;
        orderItems.forEach((item, index) => {
            totalAmount += item.thanh_tien;
            tbody.append(`
                <tr data-index="${index}">
                    <td>${index + 1}</td>
                    <td>${item.ten_thuoc}</td>
                    <td>${item.don_vi}</td>
                    <td>${item.ma_lo}</td>
                    <td><input type="number" class="form-control form-control-sm quantity-input" value="${item.so_luong}" min="1"></td>
                    <td><input type="number" class="form-control form-control-sm price-input" value="${item.gia_ban}"></td>
                    <td><input type="number" class="form-control form-control-sm vat-input" value="${item.thue_suat}"></td>
                    <td class="text-right">${formatCurrency(item.thanh_tien)}</td>
                    <td>
                        <button type="button" class="btn btn-sm btn-danger remove-item-btn" data-index="${index}">
                            <i class="bi bi-trash"></i>
                        </button>
                    </td>
                </tr>
            `);
        });

        $('#total-amount').text(formatCurrency(totalAmount));
    }

    // Handle inline editing
    $(document).on('input', '.quantity-input, .price-input, .vat-input', function() {
        const row = $(this).closest('tr');
        const index = row.data('index');
        const item = orderItems[index];

        item.so_luong = parseFloat(row.find('.quantity-input').val()) || 1;
        item.gia_ban = parseFloat(row.find('.price-input').val()) || 0;
        item.thue_suat = parseFloat(row.find('.vat-input').val()) || 0;
        item.thanh_tien = item.so_luong * item.gia_ban;
        item.tien_thue = item.thanh_tien * (item.thue_suat / 100);

        row.find('td:eq(7)').text(formatCurrency(item.thanh_tien));
        updateOrderTotal();
    });

    // Remove item
    $(document).on('click', '.remove-item-btn', function() {
        const index = $(this).data('index');
        orderItems.splice(index, 1);
        updateOrderTable();
        showToast('info', 'Đã xóa sản phẩm khỏi đơn hàng', 'bi bi-info-circle-fill');
    });

    // Update total
    function updateOrderTotal() {
        let total = 0;
        orderItems.forEach(item => {
            total += item.thanh_tien;
        });
        $('#total-amount').text(formatCurrency(total));
    }

    // Reset product form
    function resetProductForm() {
        selectedProduct = null;
        $('#quick_add_unit').empty().append('<option value="" disabled selected>Chọn đơn vị</option>');
        $('#quick_add_batch').empty().append('<option value="" disabled selected>Chọn lô</option>');
        $('#quick_add_quantity').val('');
        $('#product_search').val('');
        $('#quick_add_product_id').val('');
        $('#stock-info, #product-price, #product-vat').empty();
        $('#add-to-list-btn').prop('disabled', true);
        $('#product-search-results').html('');
    }

    // Toast notification
    function showToast(type, message, icon) {
        const toast = `
            <div class="toast align-items-center text-white bg-${type} border-0" role="alert" aria-live="assertive" aria-atomic="true">
                <div class="d-flex">
                    <div class="toast-body">
                        <i class="${icon} me-2"></i> ${message}
                    </div>
                    <button type="button" class="btn-close btn-close-white me-2 m-auto" data-bs-dismiss="toast" aria-label="Close"></button>
                </div>
            </div>
        `;
        $('#toast-container').append(toast);
        $('.toast').toast({ delay: 3000 }).toast('show');
        setTimeout(() => $('.toast').remove(), 3500);
    }

    // Handle customer type
    $('input[name="customer_type"]').on('change', function() {
        const type = $(this).val();
        $('#new-customer-form').toggle(type === 'new');
        $('#existing-customer-form').toggle(type === 'existing');
        $('#selected_customer_id').val('');
    });

    // Search customer
    $('#search-customer-btn').on('click', function() {
        const phone = $('#search_customer').val().trim();
        if (!phone) {
            showToast('warning', 'Vui lòng nhập số điện thoại', 'bi bi-exclamation-circle-fill');
            return;
        }

        $.ajax({
            url: '/khach-hang-tim-sdt',
            type: 'GET',
            data: { sdt: phone },
            success: function(response) {
                if (response.success && response.khachHang) {
                    const customer = response.khachHang;
                    $('#customer-search-results').html(`
                        <div class="alert alert-success">
                            <div class="d-flex justify-content-between align-items-center">
                                <div>
                                    <strong>${customer.ho_ten}</strong><br>
                                    <small>SĐT: ${customer.sdt}</small>
                                </div>
                                <button type="button" class="btn btn-sm btn-primary select-customer" 
                                    data-id="${customer.khach_hang_id}" data-name="${customer.ho_ten}" data-phone="${customer.sdt}">
                                    <i class="bi bi-check"></i> Chọn
                                </button>
                            </div>
                        </div>
                    `);
                } else {
                    $('#customer-search-results').html(`
                        <div class="alert alert-warning">
                            <i class="bi bi-exclamation-circle-fill"></i> Không tìm thấy khách hàng
                        </div>
                    `);
                }
            },
            error: function() {
                showToast('error', 'Lỗi khi tìm kiếm khách hàng', 'bi bi-exclamation-triangle-fill');
            }
        });
    });

    // Select customer
    $(document).on('click', '.select-customer', function() {
        const id = $(this).data('id');
        const name = $(this).data('name');
        const phone = $(this).data('phone');

        $('#selected_customer_id').val(id);
        $('#customer-search-results').html(`
            <div class="alert alert-info">
                <i class="bi bi-person-check"></i> Đã chọn: <strong>${name}</strong> - ${phone}
                <button type="button" class="btn-close float-end" aria-label="Close" id="clear-selected-customer"></button>
            </div>
        `);
        showToast('success', 'Đã chọn khách hàng', 'bi bi-check-circle-fill');
    });

    // Clear selected customer
    $(document).on('click', '#clear-selected-customer', function() {
        $('#selected_customer_id').val('');
        $('#customer-search-results').html('');
        $('#search_customer').val('');
        showToast('info', 'Đã xóa lựa chọn khách hàng', 'bi bi-info-circle-fill');
    });

    // Save order
    $('#save-order-btn').on('click', function() {
        if (orderItems.length === 0) {
            showToast('error', 'Vui lòng thêm ít nhất một sản phẩm', 'bi bi-exclamation-triangle-fill');
            return;
        }

        let formData = {};
        const customerType = $('input[name="customer_type"]:checked').val();
        if (customerType === 'existing') {
            const customerId = $('#selected_customer_id').val();
            if (!customerId) {
                showToast('error', 'Vui lòng chọn khách hàng', 'bi bi-exclamation-triangle-fill');
                return;
            }
            formData.khach_hang_id = customerId;
        } else {
            const customerName = $('#customer_name').val().trim();
            const customerPhone = $('#customer_phone').val().trim();
            if (!customerName) {
                showToast('error', 'Vui lòng nhập tên khách hàng', 'bi bi-exclamation-triangle-fill');
                return;
            }
            formData.khach_hang_moi = { ho_ten: customerName, sdt: customerPhone };
        }

        formData.items = orderItems;

        $('#save-order-btn').prop('disabled', true).html('<i class="bi bi-hourglass-split"></i> Đang xử lý...');

        $.ajax({
            url: '/don-ban-le',
            type: 'POST',
            data: JSON.stringify(formData),
            contentType: 'application/json',
            headers: {
                'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
            },
            success: function(response) {
                if (response.success) {
                    showToast('success', 'Đơn hàng đã được tạo thành công', 'bi bi-check-circle-fill');
                    resetOrderForm();
                    setTimeout(() => $('#createOrderModal').modal('hide'), 1500);
                } else {
                    showToast('error', response.message || 'Lỗi khi tạo đơn hàng', 'bi bi-exclamation-triangle-fill');
                }
            },
            error: function(xhr) {
                const errorMessage = xhr.responseJSON?.message || 'Lỗi khi tạo đơn hàng';
                showToast('error', errorMessage, 'bi bi-exclamation-triangle-fill');
            },
            complete: function() {
                $('#save-order-btn').prop('disabled', false).html('<i class="bi bi-save"></i> Lưu đơn hàng');
            }
        });
    });

    // Reset order form on modal show/hide
    $('#createOrderModal').on('shown.bs.modal', resetOrderForm);
    $('#createOrderModal').on('hidden.bs.modal', resetOrderForm);

    // Reset order form
    function resetOrderForm() {
        $('input[name="customer_type"][value="new"]').prop('checked', true).trigger('change');
        $('#customer_name, #customer_phone, #search_customer').val('');
        $('#selected_customer_id').val('');
        $('#customer-search-results').html('');
        resetProductForm();
        orderItems = [];
        updateOrderTable();
    }

    // Format currency
    function formatCurrency(amount) {
        return new Intl.NumberFormat('vi-VN', { style: 'currency', currency: 'VND' }).format(amount);
    }
});