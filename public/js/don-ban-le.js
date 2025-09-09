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
            // Chỉ reset form khi không có sản phẩm đã chọn
            if (!selectedProduct) {
                resetProductForm();
            }
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
        if (thuocs.length === 0) {
            $('#product-search-results').html(`
                <div class="alert alert-warning">
                    <i class="bi bi-exclamation-circle-fill"></i> Không tìm thấy sản phẩm phù hợp
                </div>
            `);
            return;
        }
        
        // Giới hạn số lượng hiển thị để tránh quá tải trang
        const maxDisplayItems = 10;
        const displayThuocs = thuocs.slice(0, maxDisplayItems);
        const hasMoreItems = thuocs.length > maxDisplayItems;
        
        let html = '<div class="list-group shadow-sm" style="max-height: 300px; overflow-y: auto;">';
        
        // Hiển thị các kết quả tìm kiếm
        displayThuocs.forEach(thuoc => {
            // Thêm active class nếu sản phẩm này đang được chọn
            const isSelected = selectedProduct && selectedProduct.thuoc_id === thuoc.thuoc_id;
            const activeClass = isSelected ? 'active bg-primary text-white' : '';
            
            // Không khóa thuốc không có tồn kho nữa, để có thể chọn mọi sản phẩm
            const coTonKho = thuoc.tong_ton_kho > 0;
            const disabledClass = ''; // Bỏ disabled class
            const clickAttribute = '';
            
            html += `
                <a href="#" class="list-group-item list-group-item-action product-item ${activeClass} ${disabledClass}" 
                   data-id="${thuoc.thuoc_id}" data-name="${thuoc.ten_thuoc}" ${clickAttribute}>
                    <div class="d-flex w-100 justify-content-between">
                        <h6 class="mb-1">${thuoc.ten_thuoc}</h6>
                        <small>Mã: ${thuoc.ma_thuoc}</small>
                    </div>
                    <p class="mb-0">
                        <span class="badge bg-secondary">${thuoc.don_vi_ban || thuoc.don_vi_goc}</span>
                        <span class="badge ${coTonKho ? 'bg-info' : 'bg-danger'}">Tồn: ${thuoc.tong_ton_kho}</span>
                        <span class="badge bg-success">${formatCurrency(thuoc.gia_ban)}</span>
                        ${!coTonKho ? '<span class="badge bg-warning text-dark">Chưa có hàng</span>' : ''}
                    </p>
                </a>
            `;
        });
        
        // Hiển thị thông báo nếu có thêm kết quả
        if (hasMoreItems) {
            const remainingCount = thuocs.length - maxDisplayItems;
            html += `
                <div class="list-group-item text-center text-muted">
                    <small>... và ${remainingCount} sản phẩm khác. Hãy thu hẹp tìm kiếm.</small>
                </div>
            `;
        }
        
        html += '</div>';
        $('#product-search-results').html(html);
    }

    // Handle product selection
    $(document).on('click', '.product-item', function(e) {
        e.preventDefault();
        const thuocId = $(this).data('id');
        const thuocName = $(this).data('name');
        
        // Lưu giá trị tìm kiếm hiện tại
        const currentSearchText = $('#product_search').val().trim();
        
        // Chỉ cập nhật ID của sản phẩm được chọn mà không thay đổi nội dung ô tìm kiếm
        $('#quick_add_product_id').val(thuocId);
        
        // Giữ kết quả tìm kiếm hiển thị để người dùng có thể chọn sản phẩm khác
        // $('#product-search-results').html('');

        $.ajax({
            url: '/don-ban-le-thuoc-info',
            type: 'GET',
            data: { thuoc_id: thuocId },
            success: function(response) {
                if (response.success) {
                    console.log('Thông tin thuốc nhận từ server:', response);
                    selectedProduct = response.thuoc;
                    selectedProduct.lo_thuocs = response.lo_thuocs;
                    
                    // Kiểm tra xem có lô thuốc hay không
                    if (!response.thuoc.lo_cu_nhat) {
                        console.log('CẢNH BÁO: Không có lô cũ nhất cho thuốc này');
                    }
                    
                    showProductInfo(response.thuoc, response.lo_thuocs, thuocName);
                    $('#quick_add_quantity').focus();
                } else {
                    showToast('error', 'Không thể tải thông tin thuốc', 'bi bi-exclamation-triangle-fill');
                }
            },
            error: function(xhr, status, error) {
                console.error('Lỗi Ajax:', {xhr, status, error});
                showToast('error', 'Lỗi khi tải thông tin thuốc', 'bi bi-exclamation-triangle-fill');
            }
        });
    });

    // Show product info
    function showProductInfo(product, loThuocs, productName) {
        console.log('Hiển thị thông tin sản phẩm:', product);
        
        // Hiển thị tên sản phẩm đã chọn với nút xóa
        $('#selected-product-name').html(`
            <strong>Đã chọn:</strong> ${productName || product.ten_thuoc}
            <button type="button" class="btn-close float-end" id="clear-selected-product" aria-label="Close"></button>
        `);
        $('#selected-product-container').show();
        
        const unitSelect = $('#quick_add_unit');
        unitSelect.empty().append('<option value="" disabled selected>Chọn đơn vị</option>');
        
        unitSelect.append(`<option value="don_vi_goc" data-ti-le="1" selected>${product.don_vi_goc}</option>`);
        if (product.don_vi_ban && product.ti_le_quy_doi) {
            unitSelect.append(`<option value="don_vi_ban" data-ti-le="${product.ti_le_quy_doi}">${product.don_vi_ban}</option>`);
        }

        // Tự động sử dụng lô cũ nhất thay vì hiển thị dropdown chọn lô
        const batchSelect = $('#quick_add_batch');
        batchSelect.empty();
        
        // Lấy thông tin lô cũ nhất từ response
        if (product.lo_cu_nhat) {
            const lo = product.lo_cu_nhat;
            const ngayHetHan = new Date(lo.han_su_dung).toLocaleDateString('vi-VN');
            const tonKho = parseFloat(lo.ton_kho_hien_tai) || 0;
            
            console.log('Thông tin lô cũ nhất:', {
                'Mã lô': lo.ma_lo,
                'Ngày hết hạn': lo.han_su_dung,
                'Tồn kho': tonKho
            });
            
            batchSelect.append(`<option value="${lo.lo_id}" data-ton-kho="${tonKho}" selected>
                ${lo.ma_lo} (HSD: ${ngayHetHan}, Tồn: ${tonKho})
            </option>`);
            
            // Hiển thị thông tin lô được chọn tự động
            $('#batch-info').html(`<i class="bi bi-box"></i> <strong>Lô thuốc:</strong> ${lo.ma_lo} (HSD: ${ngayHetHan})`);
        } else {
            // Nếu không có lô cũ nhất, kiểm tra xem có lô nào khác không
            console.log('Không có lô cũ nhất, kiểm tra lô thuốc khác');
            const tonKho = parseFloat(product.tong_ton_kho) || 0;
            
            // Kiểm tra xem có lô nào trong danh sách lo_thuocs không
            if (product.lo_thuocs && product.lo_thuocs.length > 0) {
                // Lấy lô đầu tiên từ danh sách
                const lo = product.lo_thuocs[0];
                const ngayHetHan = new Date(lo.han_su_dung).toLocaleDateString('vi-VN');
                
                batchSelect.append(`<option value="${lo.lo_id}" data-ton-kho="${lo.ton_kho_hien_tai}" selected>
                    ${lo.ma_lo} (HSD: ${ngayHetHan}, Tồn: ${lo.ton_kho_hien_tai})
                </option>`);
                $('#batch-info').html(`<i class="bi bi-box"></i> <strong>Lô thuốc:</strong> ${lo.ma_lo} (HSD: ${ngayHetHan})`);
                console.log('Sử dụng lô thay thế:', lo);
            } else if (tonKho > 0) {
                // Sử dụng giá trị "temporary" để server xử lý đặc biệt
                batchSelect.append(`<option value="temporary" data-ton-kho="${tonKho}" selected>
                    Lô mặc định (Tồn: ${tonKho})
                </option>`);
                $('#batch-info').html(`<i class="bi bi-box"></i> <strong>Lô thuốc:</strong> Lô mặc định`);
                showToast('info', 'Sử dụng lô mặc định vì không có thông tin chi tiết từng lô.', 'bi bi-info-circle-fill');
            } else {
                // Không có lô nào, thêm option trống
                batchSelect.append('<option value="" disabled selected>Không có lô khả dụng</option>');
                $('#batch-info').html('<i class="bi bi-exclamation-triangle"></i> <strong>Lưu ý:</strong> Không có lô thuốc khả dụng');
            }
        }

        updateStockInfo();
        updatePriceInfo();
        validateForm();
        
        // Hiển thị thông báo nếu cần cập nhật giá
        if (product.need_price_update) {
            showToast('warning', 'Thuốc này không có giá hiện tại hợp lệ, vui lòng cập nhật giá!', 'bi bi-exclamation-triangle-fill');
        }
    }

    // Update stock info
    function updateStockInfo() {
        if (!selectedProduct) {
            console.log('updateStockInfo: Không có sản phẩm nào được chọn');
            return;
        }
        
        const batchSelect = $('#quick_add_batch');
        const selectedBatch = batchSelect.find('option:selected');
        const unitSelect = $('#quick_add_unit');
        const selectedUnit = unitSelect.find('option:selected');

        console.log('Cập nhật thông tin tồn kho:', {
            'selectedProduct': selectedProduct.ten_thuoc,
            'tong_ton_kho': selectedProduct.tong_ton_kho,
            'selectedBatch exists': selectedBatch.length > 0,
            'selectedUnit exists': selectedUnit.length > 0,
            'batch value': selectedBatch.val()
        });

        if (selectedBatch.length && selectedUnit.length && (selectedBatch.val() || selectedBatch.val() === 'temporary')) {
            // Luôn sử dụng tổng tồn kho từ sản phẩm thay vì tồn kho của lô
            let tonKhoGoc = parseFloat(selectedProduct.tong_ton_kho) || 0;
            
            // Debug: Hiển thị cả tồn kho của lô và tổng tồn kho để so sánh
            let tonKhoLo = parseFloat(selectedBatch.data('ton-kho')) || 0;
            console.log('So sánh tồn kho:', {
                'Tồn kho của lô này': tonKhoLo,
                'Tổng tồn kho từ tất cả lô': tonKhoGoc
            });
            
            const tiLe = parseFloat(selectedUnit.data('ti-le')) || 1;
            const donVi = selectedUnit.text().trim();
            
            // Debug giá trị đọc được
            console.log('Dữ liệu ban đầu:', {
                'tonKhoGoc raw': selectedProduct.tong_ton_kho,
                'tonKhoGoc parsed': tonKhoGoc,
                'tiLe raw': selectedUnit.data('ti-le'),
                'tiLe parsed': tiLe,
                'donVi': donVi
            });
            
            // Tính toán tồn kho theo đơn vị đã chọn
            let tonKhoTheoDonVi = tonKhoGoc;
            
            // Áp dụng quy đổi nếu cần
            if (unitSelect.val() === 'don_vi_ban' && tiLe > 0) {
                // Nếu là đơn vị bán lẻ, cần quy đổi tồn kho từ đơn vị gốc sang đơn vị bán
                // Ví dụ: Nếu 1 hộp = 24 vỉ và tồn kho là 10 hộp
                // Thì tồn kho theo vỉ sẽ là 10 hộp x 24 vỉ/hộp = 240 vỉ
                tonKhoTheoDonVi = tonKhoGoc * tiLe;
                console.log('Quy đổi tồn kho sang đơn vị bán:', {
                    'Đơn vị gốc': selectedProduct.don_vi_goc,
                    'Đơn vị bán': donVi,
                    'Tồn kho gốc': tonKhoGoc,
                    'Tỉ lệ quy đổi': tiLe,
                    'Tồn kho quy đổi': tonKhoTheoDonVi
                });
            }
            
            // Đảm bảo tonKhoTheoDonVi là một số không âm
            tonKhoTheoDonVi = Math.max(0, tonKhoTheoDonVi);
            
            // Hiển thị với 2 số thập phân
            const formattedTonKho = tonKhoTheoDonVi.toFixed(2).replace(/\.00$/, '');
            
            // Tính tồn kho của lô hiện tại theo đơn vị đã chọn
            let tonKhoLoTheoDonVi = tonKhoLo;
            
            // Áp dụng quy đổi cho tồn kho của lô nếu cần
            if (unitSelect.val() === 'don_vi_ban' && tiLe > 0) {
                tonKhoLoTheoDonVi = tonKhoLo * tiLe;
            }
            
            const formattedTonKhoLo = tonKhoLoTheoDonVi.toFixed(2).replace(/\.00$/, '');
            
            $('#stock-info').html(`
                <i class="bi bi-boxes"></i> <strong>Tồn kho:</strong> ${formattedTonKho} ${donVi} 
                <span class="text-muted">(Lô hiện tại: ${formattedTonKhoLo} ${donVi})</span>
            `);
            
            // Lưu trữ cả giá trị gốc và đã quy đổi để tham chiếu
            $('#quick_add_quantity')
                .attr('max', tonKhoTheoDonVi)
                .attr('data-ton-kho-goc', tonKhoGoc)
                .attr('data-ton-kho-lo', tonKhoLo) // Thêm dữ liệu tồn kho của lô hiện tại
                .attr('data-ti-le-quy-doi', tiLe)
                .attr('placeholder', `Tối đa: ${formattedTonKho}`);
                
            console.log('Sau quy đổi:', {
                'Tồn kho gốc': tonKhoGoc, 
                'Tỉ lệ': tiLe, 
                'Tồn kho theo đơn vị': tonKhoTheoDonVi,
                'Max attribute set to': $('#quick_add_quantity').attr('max')
            });
        } else {
            $('#stock-info').html('<i class="bi bi-info-circle"></i> Không có tồn kho khả dụng');
            // Đảm bảo các thuộc tính vẫn được thiết lập
            $('#quick_add_quantity')
                .attr('max', 0)
                .attr('data-ton-kho-goc', 0)
                .attr('data-ti-le-quy-doi', 1)
                .attr('placeholder', 'Tối đa: 0');
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
            
            // Tính giá theo đơn vị đã chọn
            let giaTheoDonVi = selectedProduct.gia_ban; // Giá gốc
            
            // Nếu là đơn vị bán lẻ thì phải tính lại theo tỉ lệ quy đổi
            if (unitSelect.val() === 'don_vi_ban' && tiLe > 0) {
                giaTheoDonVi = selectedProduct.gia_ban / tiLe;
                console.log('Quy đổi giá theo đơn vị bán:', {
                    'Giá gốc': selectedProduct.gia_ban,
                    'Tỉ lệ quy đổi': tiLe,
                    'Giá theo đơn vị bán': giaTheoDonVi
                });
            }
            
            // Lưu giá theo đơn vị để dùng khi tính toán
            $('#quick_add_quantity').attr('data-gia-theo-don-vi', giaTheoDonVi);
            
            // Hiển thị giá đã tính toán
            $('#product-price').html(`<i class="bi bi-currency-dollar"></i> <strong>Đơn giá:</strong> ${formatCurrency(giaTheoDonVi)}/${donVi}`);
            $('#product-vat').html(`<i class="bi bi-percent"></i> <strong>VAT:</strong> ${selectedProduct.vat || 0}%`);
            
            if (quantity > 0) {
                const thanhTien = giaTheoDonVi * quantity;
                $('#product-total').html(`<i class="bi bi-cash"></i> <strong>Thành tiền:</strong> ${formatCurrency(thanhTien)}`);
            } else {
                $('#product-total').html('');
            }
        }
    }

    // Handle unit change
    $('#quick_add_unit').on('change', function() {
        updateStockInfo();
        updatePriceInfo();
        validateForm();
    });
    
    // Xử lý khi người dùng click vào nút xóa sản phẩm đã chọn
    $(document).on('click', '#clear-selected-product', function() {
        resetProductForm();
        // Giữ lại nội dung tìm kiếm
        const currentSearch = $('#product_search').val();
        if (currentSearch.trim().length > 0) {
            $('#product_search').trigger('input');
        }
    });
    
    // Ngăn chặn form submit mặc định để tránh đóng modal
    $('#create-order-form').on('submit', function(e) {
        e.preventDefault();
        return false;
    });

    // Không cần hàm xử lý thay đổi lô nữa vì chúng ta đã tự động chọn lô cũ nhất

    // Handle quantity change
    $('#quick_add_quantity').on('input', function() {
        const inputValue = $(this).val().trim();
        const quantity = parseFloat(inputValue) || 0;
        const max = parseFloat($(this).attr('max')) || 0;
        const tonKhoLo = parseFloat($(this).attr('data-ton-kho-lo')) || 0;
        const unitSelect = $('#quick_add_unit');
        const selectedUnit = unitSelect.find('option:selected');
        const tiLe = parseFloat($(this).attr('data-ti-le-quy-doi')) || 1;
        
        console.log('Quantity changed:', {
            'Input value': inputValue,
            'Parsed quantity': quantity,
            'Max value (tổng tồn kho)': max,
            'Tồn kho của lô hiện tại': tonKhoLo,
            'selectedProduct': selectedProduct ? selectedProduct.ten_thuoc : null
        });
        
        // Kiểm tra số lượng
        if (quantity < 0) {
            $(this).val(0);
            showToast('warning', 'Số lượng phải lớn hơn 0', 'bi bi-exclamation-circle-fill');
        } 
        // Cảnh báo khi vượt quá tồn kho của lô hiện tại
        else if (quantity > 0 && tonKhoLo > 0) {
            const donVi = selectedUnit.text().trim();
            // Tính tồn kho của lô theo đơn vị đã chọn
            let tonKhoLoTheoDonVi = tonKhoLo;
            if (unitSelect.val() === 'don_vi_ban' && tiLe > 0) {
                tonKhoLoTheoDonVi = tonKhoLo * tiLe;
            }
            
            if (quantity > tonKhoLoTheoDonVi) {
                showToast('info', `Lưu ý: Số lượng vượt quá tồn kho của lô hiện tại (${tonKhoLoTheoDonVi} ${donVi}). Hệ thống sẽ tự động lấy từ lô khác.`, 'bi bi-info-circle-fill');
            }
        }
        // Cảnh báo khi vượt quá tổng tồn kho
        else if (quantity > 0 && max > 0 && quantity > max) {
            const donVi = selectedUnit.text().trim();
            showToast('warning', `Lưu ý: Tổng tồn kho hiện tại chỉ có ${max} ${donVi}`, 'bi bi-exclamation-triangle-fill');
        }

        // Cập nhật thông tin giá và hiển thị thành tiền
        updatePriceInfo();
        validateForm();
    });

    // Validate form
    function validateForm() {
        const product = $('#quick_add_product_id').val();
        const batch = $('#quick_add_batch').val();
        const unit = $('#quick_add_unit').val();
        const quantity = parseFloat($('#quick_add_quantity').val()) || 0;
        const max = parseFloat($('#quick_add_quantity').attr('max')) || 0;
        
        // Debug giá trị để kiểm tra
        console.log('Kiểm tra form:', {
            'product': product,
            'batch': batch,
            'unit': unit,
            'quantity raw': $('#quick_add_quantity').val(),
            'quantity parsed': quantity,
            'max raw': $('#quick_add_quantity').attr('max'),
            'max parsed': max,
            'selectedProduct': selectedProduct ? selectedProduct.ten_thuoc : null,
            'tong_ton_kho': selectedProduct ? selectedProduct.tong_ton_kho : 'không có'
        });

        let isValid = false;
        
        // Kiểm tra các điều kiện cơ bản trước
        if (!product || !batch || !unit) {
            $('#validation-message').html('');
            $('#add-to-list-btn').prop('disabled', true);
            return;
        }

        // Kiểm tra batch có phải là giá trị rỗng
        if (batch === "" || batch === null || batch === undefined) {
            $('#validation-message').html('<div class="alert alert-danger mt-2">Không có lô thuốc khả dụng</div>');
            $('#add-to-list-btn').prop('disabled', true);
            return;
        }
        
        // Nếu là "temporary" thì vẫn hợp lệ (server sẽ xử lý bằng cách tự động chọn lô)
        
        // Lấy tồn kho gốc để kiểm tra
        const tonKhoGoc = parseFloat($('#quick_add_quantity').attr('data-ton-kho-goc')) || 0;
        
        // Kiểm tra số lượng
        if (quantity <= 0) {
            // Số lượng không hợp lệ
            isValid = false;
            $('#validation-message').html('<div class="alert alert-warning mt-2">Vui lòng nhập số lượng lớn hơn 0</div>');
        } else if (max > 0 && quantity > max) {
            // Số lượng vượt quá tồn kho nhưng vẫn cho phép thêm với cảnh báo
            isValid = true;
            $('#validation-message').html(`<div class="alert alert-warning mt-2">Cảnh báo: Số lượng vượt quá tồn kho (${max}). Vẫn có thể tiếp tục.</div>`);
        } else if (tonKhoGoc <= 0) {
            // Không có tồn kho nhưng vẫn cho phép thêm với cảnh báo
            isValid = true;
            $('#validation-message').html('<div class="alert alert-warning mt-2">Cảnh báo: Sản phẩm này không có tồn kho. Vẫn có thể tiếp tục.</div>');
        } else {
            // Mọi thứ hợp lệ
            isValid = true;
            $('#validation-message').html('');
        }
        
        // Luôn cho phép thêm khi đã chọn đủ thông tin cơ bản và nhập số lượng
        if (product && batch && unit && quantity > 0) {
            isValid = true;
        }
        
        console.log('Kết quả kiểm tra:', isValid);
        $('#add-to-list-btn').prop('disabled', !isValid);
    }

    // Add product to table
    $('#add-to-list-btn').on('click', function() {
        const batch = $('#quick_add_batch option:selected');
        const unit = $('#quick_add_unit option:selected');
        const unitSelect = $('#quick_add_unit'); // Thêm biến unitSelect để đảm bảo nhất quán với code khác
        const quantity = parseFloat($('#quick_add_quantity').val()) || 0;
        const max = parseFloat($('#quick_add_quantity').attr('max')) || 0;

        console.log('Thêm sản phẩm:', {
            'selectedProduct': selectedProduct ? selectedProduct.ten_thuoc : null,
            'batch': batch.val(),
            'unit': unit.val(),
            'unitSelect': unitSelect.val(),
            'quantity': quantity,
            'max': max
        });

        // Chắc chắn rằng chúng ta có sản phẩm và số lượng
        if (!selectedProduct) {
            showToast('error', 'Vui lòng chọn sản phẩm', 'bi bi-exclamation-triangle-fill');
            return;
        }
        
        if (!quantity || quantity <= 0) {
            showToast('error', 'Vui lòng nhập số lượng hợp lệ', 'bi bi-exclamation-triangle-fill');
            return;
        }
        
        // Kiểm tra thêm cho unit và batch
        if (!unit.val()) {
            // Tự động chọn đơn vị cơ bản nếu chưa chọn
            $('#quick_add_unit option:first').prop('selected', true);
            showToast('info', 'Đã tự động chọn đơn vị cơ bản', 'bi bi-info-circle-fill');
        }
        
        // Kiểm tra batch là rỗng (null, undefined hoặc chuỗi rỗng)
        if (!batch.val() || batch.val() === "") {
            showToast('error', 'Không có lô thuốc khả dụng', 'bi bi-exclamation-triangle-fill');
            return;
        }
        
        // Lưu trạng thái tìm kiếm hiện tại
        const currentSearch = $('#product_search').val();

        // Lấy tỉ lệ quy đổi từ đơn vị gốc sang đơn vị bán
        // Ví dụ: 1 hộp = 10 viên, thì tỉ lệ là 10
        const tiLe = parseFloat(unit.data('ti-le')) || 1;
        
        // Lấy đơn giá theo đơn vị đã được tính toán trước đó
        let donGia = parseFloat($('#quick_add_quantity').attr('data-gia-theo-don-vi')) || selectedProduct.gia_ban;
        
        // FIX: Lấy don_vi là value của option (don_vi_goc hoặc don_vi_ban)
        const donVi = unitSelect.val(); // "don_vi_goc" hoặc "don_vi_ban"
        
        // Log thông tin để debug
        console.log('Thông tin sản phẩm khi thêm (sau fix):', {
            ten_thuoc: selectedProduct.ten_thuoc,
            don_vi_goc: selectedProduct.don_vi_goc,
            don_vi_ban: selectedProduct.don_vi_ban,
            don_vi_duoc_chon: donVi, // Value của option
            ti_le_quy_doi: tiLe,
            gia_ban_goc: selectedProduct.gia_ban,
            gia_ban_theo_don_vi: donGia,
            ton_kho_goc: $('#quick_add_quantity').attr('data-ton-kho-goc'),
            ton_kho_theo_don_vi: $('#quick_add_quantity').attr('max')
        });
        
        // Tính toán giá bán theo đơn vị đã chọn
        const vat = selectedProduct.vat || 0;
        const thanhTien = donGia * quantity;
        const tienThue = thanhTien * (vat / 100);
        
        console.log('Thông tin giá bán:', {
            'Đơn giá': donGia,
            'Số lượng': quantity,
            'Thành tiền': thanhTien,
            'VAT': tienThue
        });

        const existingItemIndex = orderItems.findIndex(item => item.lo_id === batch.val());
        if (existingItemIndex !== -1) {
            orderItems[existingItemIndex].so_luong += quantity;
            orderItems[existingItemIndex].thanh_tien += thanhTien;
            orderItems[existingItemIndex].tien_thue += tienThue;
        } else {
            // FIX: Sử dụng don_vi là value của option
            orderItems.push({
                thuoc_id: selectedProduct.thuoc_id,
                ten_thuoc: selectedProduct.ten_thuoc,
                lo_id: batch.val(),
                ma_lo: batch.text().split(' ')[0],
                don_vi: donVi, // "don_vi_goc" hoặc "don_vi_ban"
                don_vi_goc: selectedProduct.don_vi_goc, // Lưu đơn vị gốc để hiển thị
                ti_le_quy_doi: tiLe, // Lưu tỉ lệ quy đổi để dùng khi xử lý tồn kho
                so_luong: quantity,
                gia_ban: donGia,
                thue_suat: vat,
                tien_thue: tienThue,
                thanh_tien: thanhTien
            });
        }

        updateOrderTable();
        showToast('success', 'Đã thêm sản phẩm vào đơn hàng', 'bi bi-check-circle-fill');
        
        // Reset form nhưng giữ lại nội dung tìm kiếm
        resetProductForm();
        
        // Phục hồi trạng thái tìm kiếm 
        $('#product_search').val(currentSearch);
        
        // Thực hiện lại tìm kiếm nếu có
        if (currentSearch.trim().length > 0) {
            $('#product_search').trigger('input');
        }
        
        $('#quick_add_quantity').focus();
    });

    // Update order table with inline editing
    function updateOrderTable() {
        const tbody = $('#products-table tbody');
        tbody.empty();

        if (orderItems.length === 0) {
            tbody.append('<tr id="no-products-row"><td colspan="8" class="text-center">Chưa có sản phẩm nào</td></tr>');
            $('#total-amount').text('0 đ');
            return;
        }

        let totalAmount = 0;
        orderItems.forEach((item, index) => {
            totalAmount += item.thanh_tien;
            
            // Hiển thị thông tin quy đổi đơn vị nếu sử dụng đơn vị bán
            let donViInfo = item.don_vi === 'don_vi_goc' ? selectedProduct.don_vi_goc : (item.don_vi === 'don_vi_ban' ? selectedProduct.don_vi_ban : '');
            if (item.don_vi === 'don_vi_ban' && item.ti_le_quy_doi > 1) {
                // Tính số lượng theo đơn vị gốc
                const soLuongDonViGoc = item.so_luong / item.ti_le_quy_doi;
                // Làm tròn đến 2 chữ số thập phân
                const formattedSoLuongGoc = soLuongDonViGoc.toFixed(2).replace(/\.00$/, '');
                const donViGoc = item.don_vi_goc || 'đơn vị gốc';
                donViInfo += ` (~ ${formattedSoLuongGoc} ${donViGoc})`;
            }
            
            // FIX: Hiển thị don_vi_info dựa trên don_vi (giá trị "don_vi_goc" hoặc "don_vi_ban")
            tbody.append(`
                <tr data-index="${index}" data-don-vi="${item.don_vi}">
                    <td>${index + 1}</td>
                    <td>${item.ten_thuoc}</td>
                    <td>${donViInfo}</td>
                    <td><input type="number" class="form-control form-control-sm quantity-input" 
                        value="${item.so_luong}" min="1" 
                        data-ti-le="${item.ti_le_quy_doi}" 
                        data-don-vi="${item.don_vi}"></td>
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
        const quantityInput = row.find('.quantity-input');
        
        // Lấy giá trị từ form
        item.so_luong = parseFloat(quantityInput.val()) || 1;
        item.gia_ban = parseFloat(row.find('.price-input').val()) || 0;
        item.thue_suat = parseFloat(row.find('.vat-input').val()) || 0;
        
        // Tính toán thành tiền dựa trên số lượng, giá bán và tỉ lệ quy đổi
        item.thanh_tien = item.so_luong * item.gia_ban;
        item.tien_thue = item.thanh_tien * (item.thue_suat / 100);

        // Nếu đây là đơn vị bán, hiển thị quy đổi
        if ($(this).hasClass('quantity-input')) {
            const tiLe = parseFloat(quantityInput.data('ti-le')) || 1;
            const donVi = quantityInput.data('don-vi'); // "don_vi_goc" hoặc "don_vi_ban"
            
            // FIX: Log debug để kiểm tra don_vi khi edit
            console.log('Edit inline - don_vi:', donVi);
            
            if (donVi === 'don_vi_ban' && tiLe > 1) {
                // Hiển thị cảnh báo cho người dùng về quy đổi
                const soLuongDonViGoc = item.so_luong / tiLe;
                const formattedSoLuongGoc = soLuongDonViGoc.toFixed(2).replace(/\.00$/, '');
                
                // Hiển thị thông báo quy đổi, sử dụng đơn vị gốc từ item
                showToast('info', `Quy đổi: ${item.so_luong} ${selectedProduct.don_vi_ban || 'đơn vị bán'} ≈ ${formattedSoLuongGoc} ${item.don_vi_goc || 'đơn vị gốc'}`, 'bi bi-info-circle-fill');
            }
        }

        row.find('td:eq(6)').text(formatCurrency(item.thanh_tien));
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
        // Không xóa nội dung tìm kiếm nữa để giữ trạng thái tìm kiếm
        // $('#product_search').val('');
        $('#quick_add_product_id').val('');
        $('#stock-info, #product-price, #product-vat').empty();
        $('#batch-info').html('Chưa chọn thuốc');
        $('#selected-product-name').empty();
        $('#selected-product-container').hide();
        $('#add-to-list-btn').prop('disabled', true);
        // Không xóa kết quả tìm kiếm để người dùng có thể tiếp tục chọn
        // $('#product-search-results').html('');
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
                    const customer = response.khachHang[0];
                    $('#customer-search-results').html(`
                        <div class="alert alert-success">
                            <div class="d-flex justify-content-between align-items-center">
                                <div>
                                    <strong>${customer.ho_ten}</strong>
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
                    location.reload();
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