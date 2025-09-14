@extends('layouts.app')

@section('title', 'Danh Sách Phiếu Nhập - Hệ Thống Quản Lý Hiệu Thuốc')

@section('page-title', 'Quản Lý Phiếu Nhập')

@section('styles')
<style>
    .status-badge {
        font-size: 0.85rem;
        font-weight: 500;
    }
    .status-draft {
        background-color: #6c757d;
    }
    .status-completed {
        background-color: #28a745;
    }
    .status-draft {
        background-color: #6c757d;
    }
    .status-cancelled {
        background-color: #dc3545;
    }
    .tree-container {
        background-color: #f8f9fa;
        border-radius: 0.25rem;
        height: 100%;
    }
    .tree-item {
        padding: 8px 15px;
        cursor: pointer;
        border-bottom: 1px solid #e9ecef;
    }
    .tree-item:hover {
        background-color: #e9ecef;
    }
    .tree-item.active {
        background-color: #0d6efd;
        color: #fff;
    }
    .month-header, .year-header {
        font-weight: 600;
        display: flex;
        align-items: center;
    }
    .month-header i, .year-header i {
        margin-right: 8px;
        transition: transform 0.2s;
    }
    .month-header.collapsed i, .year-header.collapsed i {
        transform: rotate(-90deg);
    }
    .year-header {
        background-color: #f0f0f0;
        padding: 8px 10px;
        border-radius: 4px;
    }
    .month-receipts {
        padding-left: 20px;
    }
    .receipt-item {
        padding: 5px 8px;
        border-radius: 4px;
        margin: 3px 0;
        display: flex;
        align-items: center;
    }
    .receipt-item:hover {
        background-color: rgba(13, 110, 253, 0.1);
    }
    .receipt-item.active {
        background-color: rgba(13, 110, 253, 0.2);
    }
    .receipt-item-checkbox {
        margin-right: 8px;
    }
    .receipt-details-container {
        background-color: #fff;
        border: 1px solid #dee2e6;
        border-radius: 0.25rem;
        padding: 15px;
        margin-top: 15px;
    }
    .search-container {
        margin-bottom: 15px;
    }
    .scrollable-container {
        max-height: calc(100vh - 250px);
        overflow-y: auto;
        overflow-x: hidden;
    }

    /* Fix dropdown menu positioning */
    .table-responsive {
        overflow: visible !important;
    }
    .table td {
        position: relative;
    }
    .dropdown-menu {
        position: absolute;
        z-index: 1000;
    }
</style>
@endsection

@section('content')
<div class="row mb-3">
    <div class="col-md-12 text-end">
        <a href="{{ route('phieu-nhap.create') }}" class="btn btn-primary">
            <i class="bi bi-plus-circle me-1"></i> Tạo Phiếu Nhập Mới
        </a>
    </div>
</div>

<div class="row">
    <!-- Left Column - Tree View -->
    <div class="col-md-3">
        <div class="card">
            <div class="card-header bg-primary text-white">
                <h5 class="card-title mb-0">Danh Sách Phiếu Nhập</h5>
            </div>
            <div class="card-body p-0">
                <div class="search-container p-3">
                    <div class="input-group input-group-sm">
                        <input type="text" class="form-control" id="searchReceipt" placeholder="Tìm mã phiếu...">
                        <button class="btn btn-outline-secondary" type="button" id="clearSearch">
                            <i class="bi bi-x"></i>
                        </button>
                    </div>
                </div>
                
                <div class="scrollable-container" id="receipt-tree">
                    <!-- Tree View with Months -->
                    @php
                        // Group receipts by year and month
                        $receiptsByYearMonth = $allPhieuNhaps->groupBy(function($item) {
                            return Carbon\Carbon::parse($item->ngay_nhap)->format('Y-m');
                        })->sortKeysDesc(); // Sort by year and month in descending order
                        
                        // Get unique years from the collection
                        $years = $allPhieuNhaps->map(function($item) {
                            return Carbon\Carbon::parse($item->ngay_nhap)->format('Y');
                        })->unique()->sort()->reverse()->values()->all();
                        
                        // If no data, include current year
                        if (empty($years)) {
                            $years = [now()->year];
                        }
                        
                        $monthNames = [
                            1 => 'Tháng 1 (Giêng)',
                            2 => 'Tháng 2',
                            3 => 'Tháng 3',
                            4 => 'Tháng 4',
                            5 => 'Tháng 5',
                            6 => 'Tháng 6',
                            7 => 'Tháng 7',
                            8 => 'Tháng 8',
                            9 => 'Tháng 9',
                            10 => 'Tháng 10',
                            11 => 'Tháng 11',
                            12 => 'Tháng 12'
                        ];
                    @endphp
                    
                    @foreach($years as $year)
                        <div class="tree-item year-header mb-2" data-bs-toggle="collapse" data-bs-target="#year{{ $year }}">
                            <i class="bi bi-calendar-year me-2"></i><strong>Năm {{ $year }}</strong>
                        </div>
                        
                        <div class="collapse show" id="year{{ $year }}">
                            @foreach(range(12, 1) as $month)
                                @php 
                                    $monthName = $monthNames[$month];
                                    $monthKey = sprintf("%d-%02d", $year, $month);
                                    $monthReceipts = $receiptsByYearMonth->get($monthKey, collect([]));
                                @endphp
                        
                        <div class="month-section" data-month="{{ $month }}">
                            <div class="tree-item month-header" data-bs-toggle="collapse" data-bs-target="#month{{ $month }}">
                                <i class="bi bi-caret-down-fill"></i> {{ $monthName }}
                                <span class="badge bg-secondary ms-2">{{ $monthReceipts->count() }}</span>
                            </div>
                            
                            <div class="collapse {{ $monthReceipts->count() > 0 ? 'show' : '' }}" id="month{{ $month }}">
                                <div class="month-receipts">
                                    @foreach($monthReceipts as $receipt)
                                    <div class="receipt-item" data-id="{{ $receipt->phieu_id }}">
                                        <input type="checkbox" class="receipt-item-checkbox" data-id="{{ $receipt->phieu_id }}">
                                        <span>{{ $receipt->ma_phieu }}</span>
                                    </div>
                                    @endforeach
                                </div>
                            </div>
                        </div>
                            @endforeach
                        </div>
                    @endforeach
                </div>
            </div>
        </div>
    </div>
    
    <!-- Right Column - Receipt List and Details -->
    <div class="col-md-9">
        <!-- Upper section - Receipt List Table -->
        <div class="card mb-3">
            <div class="card-header bg-light">
                <h5 class="card-title mb-0">Danh Sách Phiếu Đã Chọn</h5>
            </div>
            <div class="card-body p-0" style="overflow: visible">
                <div class="table-responsive" style="overflow: visible">
                    <table class="table table-hover table-striped mb-0">
                        <thead class="table-light">
                            <tr>
                                <th>Mã Phiếu</th>
                                <th>Nhà Cung Cấp</th>
                                <th>Ngày Nhập</th>
                                <th>Tổng Tiền</th>
                                <th>VAT</th>
                                <th>Tổng Cộng</th>
                                <th>Trạng Thái</th>
                                <th>Thao Tác</th>
                            </tr>
                        </thead>
                        <tbody id="selected-receipts-table">
                            <tr>
                                <td colspan="8" class="text-center py-3">Chọn phiếu nhập từ danh sách bên trái</td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
        
        <!-- Lower section - Receipt Details -->
        <div class="card">
            <div class="card-header bg-light">
                <h5 class="card-title mb-0">Chi Tiết Phiếu Nhập</h5>
            </div>
            <div class="card-body" id="receipt-details">
                <div class="alert alert-info">
                    <i class="bi bi-info-circle me-2"></i> Vui lòng chọn một phiếu nhập để xem chi tiết
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Modal lịch sử lô -->
<div class="modal fade" id="lotHistoryModal" tabindex="-1">
    <div class="modal-dialog modal-xl">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Lịch sử thay đổi lô <span id="lot-info"></span></h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <ul class="nav nav-tabs mb-3" id="lotHistoryTabs" role="tablist">
                    <li class="nav-item" role="presentation">
                        <button class="nav-link active" id="additions-tab" data-bs-toggle="tab" data-bs-target="#additions" type="button" role="tab">
                            <i class="bi bi-box-arrow-in-down me-1"></i>Lịch sử nhập kho
                        </button>
                    </li>
                    <li class="nav-item" role="presentation">
                        <button class="nav-link" id="adjustments-tab" data-bs-toggle="tab" data-bs-target="#adjustments" type="button" role="tab">
                            <i class="bi bi-pencil-square me-1"></i>Lịch sử điều chỉnh
                        </button>
                    </li>
                </ul>
                <div class="tab-content" id="lotHistoryContent">
                    <div class="tab-pane fade show active" id="additions" role="tabpanel">
                        <div class="table-responsive">
                            <table class="table table-striped table-hover" id="lotAdditionsTable">
                                <thead>
                                    <tr>
                                        <th>Thời Gian</th>
                                        <th>Mã Phiếu</th>
                                        <th>Số Lượng</th>
                                        <th>Đơn Vị</th>
                                        <th>Giá Nhập</th>
                                        <th>Thành Tiền</th>
                                        <th>Người Nhập</th>
                                    </tr>
                                </thead>
                                <tbody></tbody>
                            </table>
                        </div>
                    </div>
                    <div class="tab-pane fade" id="adjustments" role="tabpanel">
                        <div class="table-responsive">
                            <table class="table table-striped table-hover" id="lotAdjustmentsTable">
                                <thead>
                                    <tr>
                                        <th>Thời Gian</th>
                                        <th>Loại Thay Đổi</th>
                                        <th>Số Lượng Thay Đổi</th>
                                        <th>Tồn Kho Mới</th>
                                        <th>Người Thực Hiện</th>
                                        <th>Mô Tả</th>
                                    </tr>
                                </thead>
                                <tbody></tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Đóng</button>
            </div>
        </div>
    </div>
</div>

<!-- Modal xác nhận xóa -->
<div class="modal fade" id="deleteModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Xác nhận hủy phiếu nhập</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <p>Bạn có chắc chắn muốn hủy phiếu nhập <span id="delete-ma-phieu" class="fw-bold"></span>?</p>
                <p class="text-danger mb-0">Lưu ý: Hành động này không thể hoàn tác.</p>
            </div>
            <div class="modal-footer">
                <form id="deleteForm" method="POST">
                    @csrf
                    @method('DELETE')
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Đóng</button>
                    <button type="submit" class="btn btn-danger">Xác nhận hủy</button>
                </form>
            </div>
        </div>
    </div>
</div>

@endsection

@section('scripts')
<script>
    $(document).ready(function() {
        // Store all receipts data for client-side operations
        const allReceipts = @json($allPhieuNhaps);
        let selectedReceipts = [];
        
        // Receipt item click handler
        $(document).on('click', '.receipt-item', function(e) {
            if ($(e.target).hasClass('receipt-item-checkbox')) return;
            
            const receiptId = $(this).data('id');
            toggleReceiptSelection(receiptId);
            
            // Only select in the list, don't load details
            // Highlight the selected receipt
            $('.receipt-item').removeClass('active');
            $(this).addClass('active');
        });
        
        // Receipt checkbox click handler
        $(document).on('change', '.receipt-item-checkbox', function(e) {
            e.stopPropagation();
            const receiptId = $(this).data('id');
            toggleReceiptSelection(receiptId);
        });
        
        // Toggle receipt selection and update the table
        function toggleReceiptSelection(receiptId) {
            const checkBox = $(`.receipt-item-checkbox[data-id="${receiptId}"]`);
            const isChecked = !checkBox.prop('checked');
            checkBox.prop('checked', isChecked);
            
            if (isChecked) {
                // Add to selection if not already there
                if (!selectedReceipts.some(r => r.phieu_id === receiptId)) {
                    const receipt = allReceipts.find(r => r.phieu_id === receiptId);
                    if (receipt) {
                        selectedReceipts.push(receipt);
                    }
                }
            } else {
                // Remove from selection
                selectedReceipts = selectedReceipts.filter(r => r.phieu_id !== receiptId);
            }
            
            updateSelectedReceiptsTable();
        }
        
        // Update the selected receipts table
        function updateSelectedReceiptsTable() {
            const tbody = $('#selected-receipts-table');
            tbody.empty();
            
            if (selectedReceipts.length === 0) {
                tbody.append(`
                    <tr>
                        <td colspan="8" class="text-center py-3">Chọn phiếu nhập từ danh sách bên trái</td>
                    </tr>
                `);
                return;
            }
            
            selectedReceipts.forEach(receipt => {
                const ngayNhap = new Date(receipt.ngay_nhap).toLocaleDateString('vi-VN');
                let trangThaiClass, trangThaiText;
                
                switch(receipt.trang_thai) {
                    case 'hoan_tat':
                    case 'nhap_kho':
                        trangThaiClass = 'status-completed';
                        trangThaiText = 'Hoàn thành';
                        break;
                    case 'cho_xu_ly':
                    case 'tao_moi':
                        trangThaiClass = 'status-draft';
                        trangThaiText = 'Chờ xử lý';
                        break;
                    case 'huy':
                        trangThaiClass = 'status-cancelled';
                        trangThaiText = 'Đã hủy';
                        break;
                    default:
                        trangThaiClass = 'status-draft';
                        trangThaiText = receipt.trang_thai || 'Không xác định';
                }
                
                tbody.append(`
                    <tr>
                        <td>${receipt.ma_phieu}</td>
                        <td>${receipt.nha_cung_cap?.ten_ncc || 'N/A'}</td>
                        <td>${ngayNhap}</td>
                        <td>${formatCurrency(receipt.tong_tien)}</td>
                        <td>${formatCurrency(receipt.vat)}</td>
                        <td><strong>${formatCurrency(receipt.tong_cong)}</strong></td>
                        <td><span class="badge status-badge ${trangThaiClass}">${trangThaiText}</span></td>
                        <td style="overflow: visible !important;">
                            <div class="dropdown">
                                <button class="btn btn-sm btn-secondary dropdown-toggle" type="button" data-bs-toggle="dropdown" aria-expanded="false">
                                    Hành động
                                </button>
                                <ul class="dropdown-menu">
                                    <li>
                                        <a class="dropdown-item view-details-btn" href="javascript:void(0)" data-id="${receipt.phieu_id}">
                                            <i class="bi bi-eye"></i> Chi tiết
                                        </a>
                                    </li>
                                    ${receipt.trang_thai !== 'hoan_tat' && receipt.trang_thai !== 'huy' ? `
                                    <li>
                                        <a class="dropdown-item complete-receipt-btn" href="javascript:void(0)" 
                                        data-id="${receipt.phieu_id}" data-ma-phieu="${receipt.ma_phieu}">
                                            <i class="bi bi-check-circle"></i> Xác nhận
                                        </a>
                                    </li>
                                    ` : ''}
                                    ${receipt.trang_thai !== 'hoan_tat' && receipt.trang_thai !== 'huy' ? `
                                    <li>
                                        <a class="dropdown-item edit-receipt-btn" href="javascript:void(0)" 
                                        data-id="${receipt.phieu_id}">
                                            <i class="bi bi-pencil"></i> Sửa thông tin
                                        </a>
                                    </li>
                                    ` : ''}
                                </ul>
                            </div>
                        </td>
                    </tr>
                `);
            });
        }
        
        // Load receipt details function
        function loadReceiptDetails(receiptId) {
            $.ajax({
                url: `/phieu-nhap/${receiptId}/ajax`,
                type: 'GET',
                dataType: 'json',
                success: function(response) {
                    console.log('Ajax Response:', response); // Debug log
                    if (response.success) {
                        displayReceiptDetails(response.phieuNhap);
                    } else {
                        $('#receipt-details').html(`
                            <div class="alert alert-danger">
                                <i class="bi bi-exclamation-triangle me-2"></i> ${response.message || 'Đã xảy ra lỗi khi tải dữ liệu'}
                            </div>
                        `);
                    }
                },
                error: function(xhr) {
                    $('#receipt-details').html(`
                        <div class="alert alert-danger">
                            <i class="bi bi-exclamation-triangle me-2"></i> Không thể tải chi tiết phiếu nhập
                        </div>
                    `);
                }
            });
        }
        
        // Display receipt details function
        function displayReceiptDetails(phieuNhap) {
            console.log('Phieu Nhap Data:', phieuNhap); // Debug log
            
            const ngayNhap = new Date(phieuNhap.ngay_nhap).toLocaleDateString('vi-VN');
            let trangThaiClass, trangThaiText;
            
            switch(phieuNhap.trang_thai) {
                case 'hoan_tat':
                case 'nhap_kho':
                    trangThaiClass = 'status-completed';
                    trangThaiText = 'Hoàn thành';
                    break;
                case 'cho_xu_ly':
                case 'tao_moi':
                    trangThaiClass = 'status-draft';
                    trangThaiText = 'Chờ xử lý';
                    break;
                case 'huy':
                    trangThaiClass = 'status-cancelled';
                    trangThaiText = 'Đã hủy';
                    break;
                default:
                    trangThaiClass = 'status-draft';
                    trangThaiText = phieuNhap.trang_thai || 'Không xác định';
            }
            
            let detailsHtml = `
                <div class="row mb-3">
                    <div class="col-md-6">
                        <h5 class="border-bottom pb-2">Thông tin phiếu nhập</h5>
                        <div class="mb-2"><strong>Mã phiếu:</strong> ${phieuNhap.ma_phieu}</div>
                        <div class="mb-2"><strong>Ngày nhập:</strong> ${ngayNhap}</div>
                        <div class="mb-2"><strong>Trạng thái:</strong> <span class="badge ${trangThaiClass}">${trangThaiText}</span></div>
                        <div class="mb-2"><strong>Người tạo:</strong> ${phieuNhap.nguoi_dung?.ho_ten || 'N/A'}</div>
                    </div>
                    <div class="col-md-6">
                        <h5 class="border-bottom pb-2">Thông tin nhà cung cấp</h5>
                        <div class="mb-2"><strong>Tên NCC:</strong> ${phieuNhap.nha_cung_cap?.ten_ncc || 'N/A'}</div>
                        <div class="mb-2"><strong>Điện thoại:</strong> ${phieuNhap.nha_cung_cap?.sdt || 'N/A'}</div>
                        <div class="mb-2"><strong>Địa chỉ:</strong> ${phieuNhap.nha_cung_cap?.dia_chi || 'N/A'}</div>
                    </div>
                </div>
                
                <h5 class="border-bottom pb-2 mb-3">Chi tiết phiếu nhập</h5>
                <div class="table-responsive">
                    <table class="table table-striped table-bordered">
                        <thead class="table-light">
                            <tr>
                                <th>STT</th>
                                <th>Thuốc</th>
                                <th>Số lô</th>
                                <th>Hạn dùng</th>
                                <th>Số lượng</th>
                                <th>Đơn giá</th>
                                <th>VAT</th>
                                <th>Thành tiền</th>
                            </tr>
                        </thead>
                        <tbody>
            `;
            
            // Check for both possible relationship names
            const chiTietList = phieuNhap.chiTietLoNhaps || phieuNhap.chi_tiet_lo_nhaps || [];
            
            if (chiTietList.length > 0) {
                chiTietList.forEach((item, index) => {
                    console.log('Chi Tiet Item:', item); // Debug log
                    const loThuoc = item.loThuoc || item.lo_thuoc;
                    const hanDung = loThuoc?.han_dung ? new Date(loThuoc.han_dung).toLocaleDateString('vi-VN') : 'N/A';
                    const thuoc = loThuoc?.thuoc;
                    const tenThuoc = thuoc?.ten_thuoc || 'N/A';
                    const loId = loThuoc?.lo_id;
                    const maLo = loThuoc?.ma_lo || loThuoc?.so_lo || 'N/A';
                    
                    detailsHtml += `
                        <tr>
                            <td>${index + 1}</td>
                            <td>${tenThuoc}</td>
                            <td>
                                ${loId ? 
                                `<div class="d-flex align-items-center">
                                    <a href="/lo-thuoc/${loId}" class="text-primary me-2">${maLo}</a>
                                    <button class="btn btn-sm btn-outline-info view-lot-history-btn" 
                                        data-lo-id="${loId}" 
                                        data-ten-thuoc="${tenThuoc}"
                                        data-ma-lo="${maLo}">
                                        <i class="bi bi-clock-history"></i>
                                    </button>
                                </div>` : 
                                maLo}
                            </td>
                            <td>${hanDung}</td>
                            <td>${formatNumber(item.so_luong)}</td>
                            <td>${formatCurrency(item.gia_nhap)}</td>
                            <td>${formatCurrency(item.tien_thue || 0)} (${item.thue_suat || 0}%)</td>
                            <td>${formatCurrency(item.thanh_tien)}</td>
                        </tr>
                    `;
                });
            } else {
                detailsHtml += `
                    <tr>
                        <td colspan="8" class="text-center">Không có dữ liệu chi tiết</td>
                    </tr>
                `;
            }
            
            detailsHtml += `
                        </tbody>
                        <tfoot>
                            <tr>
                                <th colspan="6" class="text-end">Tổng tiền:</th>
                                <td colspan="2" class="fw-bold">${formatCurrency(phieuNhap.tong_tien)}</td>
                            </tr>
                            <tr>
                                <th colspan="6" class="text-end">Tổng VAT:</th>
                                <td colspan="2" class="fw-bold">${formatCurrency(phieuNhap.vat)}</td>
                            </tr>
                            <tr>
                                <th colspan="6" class="text-end">Tổng cộng:</th>
                                <td colspan="2" class="fw-bold">${formatCurrency(phieuNhap.tong_cong)}</td>
                            </tr>
                        </tfoot>
                    </table>
                </div>
                <!-- Removed "Xem đầy đủ" button as requested -->
            `;
            
            $('#receipt-details').html(detailsHtml);
        }
        
        // Search functionality
        $('#searchReceipt').on('input', function() {
            const searchTerm = $(this).val().toLowerCase();
            
            if (searchTerm === '') {
                // Show all months and reset UI
                $('.month-section').show();
                $('.receipt-item').show();
                return;
            }
            
            // Hide all months first
            $('.month-section').hide();
            
            // Show only matching receipts
            $('.receipt-item').each(function() {
                const receiptText = $(this).text().toLowerCase();
                if (receiptText.includes(searchTerm)) {
                    $(this).show();
                    $(this).closest('.month-section').show();
                    $(this).closest('.collapse').addClass('show');
                } else {
                    $(this).hide();
                }
            });
        });
        
        // Clear search
        $('#clearSearch').click(function() {
            $('#searchReceipt').val('').trigger('input');
        });
        
        // View details button in the selected receipts table
        $(document).on('click', '.view-details-btn', function() {
            const receiptId = $(this).data('id');
            
            // Show loading message before fetching details
            $('#receipt-details').html(`
                <div class="d-flex justify-content-center">
                    <div class="spinner-border text-primary" role="status">
                        <span class="visually-hidden">Đang tải...</span>
                    </div>
                </div>
                <p class="text-center mt-2">Đang tải chi tiết phiếu nhập...</p>
            `);
            
            // Load receipt details
            loadReceiptDetails(receiptId);
            
            // Highlight the corresponding receipt in the tree
            $('.receipt-item').removeClass('active');
            $(`.receipt-item[data-id="${receiptId}"]`).addClass('active');
        });
        
        // Helper function to format currency
        function formatCurrency(value) {
            return new Intl.NumberFormat('vi-VN', {
                style: 'currency',
                currency: 'VND',
                maximumFractionDigits: 0
            }).format(value || 0).replace('₫', 'đ');
        }
        
        // Helper function to format number
        function formatNumber(value) {
            return new Intl.NumberFormat('vi-VN', {
                maximumFractionDigits: 2
            }).format(value || 0);
        }

        // Xử lý nút sửa thông tin phiếu nhập
        $(document).on('click', '.edit-receipt-btn', function() {
            const receiptId = $(this).data('id');
            // Chuyển hướng đến trang edit phiếu nhập
            window.location.href = `/phieu-nhap/${receiptId}/edit`;
        });

        // Xử lý nút xem lịch sử lô
        $(document).on('click', '.view-lot-history-btn', function() {
            const loId = $(this).data('lo-id');
            const tenThuoc = $(this).data('ten-thuoc');
            const maLo = $(this).data('ma-lo');

            // Hiển thị thông tin lô trong tiêu đề modal
            $('#lot-info').text(`${maLo} (${tenThuoc})`);

            // Xóa dữ liệu cũ và hiển thị trạng thái đang tải
            $('#lotAdditionsTable tbody').html('<tr><td colspan="7" class="text-center">Đang tải dữ liệu...</td></tr>');
            $('#lotAdjustmentsTable tbody').html('<tr><td colspan="6" class="text-center">Đang tải dữ liệu...</td></tr>');

            // Hiển thị modal
            $('#lotHistoryModal').modal('show');

            // Khi modal đã hiển thị xong, lấy dữ liệu
            $('#lotHistoryModal').on('shown.bs.modal', function () {
                // Lấy lịch sử nhập kho của lô
                $.ajax({
                    url: `/phieu-nhap/lot-additions/${loId}`,
                    method: 'GET',
                    success: function(response) {
                        const tbody = $('#lotAdditionsTable tbody');
                        tbody.empty();

                        if (response.additions && response.additions.length > 0) {
                            response.additions.forEach(item => {
                                tbody.append(`
                                    <tr>
                                        <td>${new Date(item.created_at).toLocaleString('vi-VN')}</td>
                                        <td>${item.ma_phieu}</td>
                                        <td>${item.so_luong}</td>
                                        <td>${item.don_vi}</td>
                                        <td>${item.gia_nhap}</td>
                                        <td>${item.thanh_tien}</td>
                                        <td>${item.nguoi_nhap}</td>
                                    </tr>
                                `);
                            });
                        } else {
                            tbody.append('<tr><td colspan="7" class="text-center">Không có dữ liệu</td></tr>');
                        }
                    },
                    error: function() {
                        $('#lotAdditionsTable tbody').html('<tr><td colspan="7" class="text-center text-danger">Lỗi khi tải dữ liệu</td></tr>');
                    }
                });

                // Lấy lịch sử điều chỉnh của lô
                $.ajax({
                    url: `/phieu-nhap/lot-history/${loId}`,
                    method: 'GET',
                    success: function(response) {
                        const tbody = $('#lotAdjustmentsTable tbody');
                        tbody.empty();

                        if (response.history && response.history.length > 0) {
                            response.history.forEach(item => {
                                tbody.append(`
                                    <tr>
                                        <td>${new Date(item.created_at).toLocaleString('vi-VN')}</td>
                                        <td>${
                                            item.loai_thay_doi === 'nhap' ? 'Nhập kho' :
                                            item.loai_thay_doi === 'ban' ? 'Bán hàng' :
                                            item.loai_thay_doi === 'dieu_chinh' ? 'Điều chỉnh' :
                                            item.loai_thay_doi === 'chuyen_kho' ? 'Chuyển kho' :
                                            item.loai_thay_doi === 'hoan_tra' ? 'Hoàn trả' :
                                            (item.loai_thay_doi ?? 'N/A')
                                        }</td>
                                        <td>${item.so_luong_thay_doi}</td>
                                        <td>${item.ton_kho_moi}</td>
                                        <td>${item.nguoi_dung}</td>
                                        <td>${item.mo_ta}</td>
                                    </tr>
                                `);
                            });
                        } else {
                            tbody.append('<tr><td colspan="6" class="text-center">Không có dữ liệu</td></tr>');
                        }
                    },
                    error: function() {
                        $('#lotAdjustmentsTable tbody').html('<tr><td colspan="6" class="text-center text-danger">Lỗi khi tải dữ liệu</td></tr>');
                    }
                });
            });
        });

        // Handle complete receipt button click
        $(document).on('click', '.complete-receipt-btn', function() {
            const receiptId = $(this).data('id');
            const maPhieu = $(this).data('ma-phieu');
            
            if (confirm(`Bạn có chắc chắn muốn xác nhận hoàn thành phiếu nhập ${maPhieu}?`)) {
                // Show loading state
                const $btn = $(this);
                const originalHtml = $btn.html();
                $btn.html('<span class="spinner-border spinner-border-sm" role="status" aria-hidden="true"></span> Đang xử lý...').prop('disabled', true);
                
                // Call API to complete the receipt
                $.ajax({
                    url: `/phieu-nhap/${receiptId}/complete`,
                    type: 'POST',
                    data: {
                        _token: '{{ csrf_token() }}'
                    },
                    success: function(response) {
                        if (response.success) {
                            // Show success message
                            alert('Xác nhận hoàn thành phiếu nhập thành công!');
                            // Reload the page to update the status
                            window.location.reload();
                        } else {
                            // Show error message
                            alert(response.message || 'Có lỗi xảy ra khi xác nhận hoàn thành phiếu nhập.');
                            // Reset button state
                            $btn.html(originalHtml).prop('disabled', false);
                        }
                    },
                    error: function(xhr) {
                        // Show error message
                        alert('Có lỗi xảy ra khi xác nhận hoàn thành phiếu nhập.');
                        // Reset button state
                        $btn.html(originalHtml).prop('disabled', false);
                    }
                });
            }
        });
        
        // Month header click handling
        $(document).on('click', '.month-header, .year-header', function() {
            const isCollapsed = $(this).hasClass('collapsed');
            if (isCollapsed) {
                $(this).removeClass('collapsed');
            } else {
                $(this).addClass('collapsed');
            }
        });
        
        // If there's a query parameter, select that receipt automatically
        $(document).ready(function() {
            const urlParams = new URLSearchParams(window.location.search);
            const receiptId = urlParams.get('receipt_id');
            if (receiptId) {
                // Make sure the receipt is visible in the tree and selected in the list
                const receiptItem = $(`.receipt-item[data-id="${receiptId}"]`);
                if (receiptItem.length) {
                    // Toggle selection to add to the selected receipts list
                    toggleReceiptSelection(receiptId);
                    
                    // Highlight it in the tree
                    receiptItem.addClass('active');
                    
                    // Expand its parent month if collapsed
                    receiptItem.closest('.collapse').addClass('show');
                    
                    // Expand its parent year if collapsed
                    receiptItem.closest('.collapse').parent().prev('.year-header').removeClass('collapsed');
                }
            }
        });
    });
</script>
@endsection
