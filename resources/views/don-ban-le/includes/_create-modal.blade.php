<!-- Modal for creating retail order -->
<div class="modal fade" id="createOrderModal" data-bs-backdrop="static" tabindex="-1" aria-labelledby="createOrderModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-xl modal-dialog-scrollable">
        <div class="modal-content">
            <div class="modal-header bg-primary text-white">
                <h5 class="modal-title" id="createOrderModalLabel"><i class="bi bi-cart-plus"></i> Tạo đơn bán lẻ</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <form id="create-order-form">
                    <!-- Customer Information Section -->
                    <div class="card shadow mb-3">
                        <div class="card-header py-2 bg-primary text-white">
                            <h6 class="m-0 font-weight-bold"><i class="bi bi-person-fill"></i> Thông tin khách hàng</h6>
                        </div>
                        <div class="card-body py-2">
                            <div class="row align-items-center">
                                <div class="col-md-2">
                                    <div class="form-check form-check-inline">
                                        <input class="form-check-input" type="radio" name="customer_type" id="new_customer" value="new" checked>
                                        <label class="form-check-label" for="new_customer">Mới</label>
                                    </div>
                                    <div class="form-check form-check-inline">
                                        <input class="form-check-input" type="radio" name="customer_type" id="existing_customer" value="existing">
                                        <label class="form-check-label" for="existing_customer">Đã có</label>
                                    </div>
                                </div>
                                <!-- New Customer -->
                                <div id="new-customer-form" class="col-md-10">
                                    <div class="row g-2">
                                        <div class="col-md-5">
                                            <div class="input-group input-group-sm">
                                                <span class="input-group-text"><i class="bi bi-person"></i></span>
                                                <input type="text" class="form-control" id="customer_name" name="khach_hang_moi[ho_ten]" placeholder="Nhập tên khách hàng">
                                            </div>
                                        </div>
                                        <div class="col-md-5">
                                            <div class="input-group input-group-sm">
                                                <span class="input-group-text"><i class="bi bi-telephone"></i></span>
                                                <input type="text" class="form-control" id="customer_phone" name="khach_hang_moi[sdt]" placeholder="Nhập số điện thoại">
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                <!-- Existing Customer -->
                                <div id="existing-customer-form" class="col-md-10" style="display: none;">
                                    <div class="row g-2">
                                        <div class="col-md-5">
                                            <div class="input-group input-group-sm">
                                                <span class="input-group-text"><i class="bi bi-telephone"></i></span>
                                                <input type="text" class="form-control" id="search_customer" placeholder="Nhập SĐT khách hàng">
                                                <button class="btn btn-outline-primary btn-sm" type="button" id="search-customer-btn">
                                                    <i class="bi bi-search"></i>
                                                </button>
                                            </div>
                                        </div>
                                        <div class="col-md-7" id="customer-search-results"></div>
                                    </div>
                                    <input type="hidden" name="khach_hang_id" id="selected_customer_id">
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Quick Add Product -->
                    <div class="card shadow mb-3">
                        <div class="card-header py-2 bg-primary text-white">
                            <h6 class="m-0 font-weight-bold"><i class="bi bi-capsule"></i> Thêm sản phẩm nhanh</h6>
                        </div>
                        <div class="card-body py-2">
                            <div class="row g-2 mb-2">
                                <div class="col-md-4">
                                    <div class="input-group input-group-sm">
                                        <span class="input-group-text"><i class="bi bi-search"></i></span>
                                        <input type="text" class="form-control" id="product_search" placeholder="Tìm kiếm thuốc">
                                    </div>
                                    <div id="product-search-results" class="mt-2"></div>
                                    <input type="hidden" id="quick_add_product_id">
                                </div>
                                <div class="col-md-2">
                                    <div class="input-group input-group-sm">
                                        <span class="input-group-text"><i class="bi bi-box"></i></span>
                                        <select class="form-select" id="quick_add_unit">
                                            <option value="" disabled selected>Chọn đơn vị</option>
                                        </select>
                                    </div>
                                </div>
                                <div class="col-md-2">
                                    <div class="input-group input-group-sm">
                                        <span class="input-group-text"><i class="bi bi-tag"></i></span>
                                        <select class="form-select" id="quick_add_batch">
                                            <option value="" disabled selected>Chọn lô</option>
                                        </select>
                                    </div>
                                </div>
                                <div class="col-md-2">
                                    <div class="input-group input-group-sm">
                                        <span class="input-group-text"><i class="bi bi-123"></i></span>
                                        <input type="number" class="form-control" id="quick_add_quantity" placeholder="Số lượng" min="1">
                                    </div>
                                </div>
                                <div class="col-md-2">
                                    <button class="btn btn-sm btn-success w-100" id="add-to-list-btn" disabled>
                                        <i class="bi bi-plus-lg"></i> Thêm
                                    </button>
                                </div>
                            </div>
                            <div class="mt-2">
                                <div id="stock-info" class="text-muted"></div>
                                <div id="product-price" class="text-muted"></div>
                                <div id="product-vat" class="text-muted"></div>
                            </div>
                        </div>
                    </div>

                    <!-- Product List -->
                    <div class="card shadow mb-4">
                        <div class="card-header py-3 bg-primary text-white">
                            <h6 class="m-0 font-weight-bold"><i class="bi bi-list-ul"></i> Danh sách sản phẩm</h6>
                        </div>
                        <div class="card-body">
                            <div class="table-responsive">
                                <table class="table table-bordered" id="products-table">
                                    <thead>
                                        <tr>
                                            <th>STT</th>
                                            <th>Tên sản phẩm</th>
                                            <th>Đơn vị</th>
                                            <th>Lô</th>
                                            <th>Số lượng</th>
                                            <th>Đơn giá</th>
                                            <th>VAT (%)</th>
                                            <th>Thành tiền</th>
                                            <th>Thao tác</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <tr id="no-products-row">
                                            <td colspan="9" class="text-center">Chưa có sản phẩm nào</td>
                                        </tr>
                                    </tbody>
                                    <tfoot>
                                        <tr>
                                            <th colspan="7" class="text-right">Tổng tiền:</th>
                                            <th colspan="2" class="text-right" id="total-amount">0 đ</th>
                                        </tr>
                                    </tfoot>
                                </table>
                            </div>
                        </div>
                    </div>

                    <!-- Toast Container -->
                    <div class="position-fixed bottom-0 end-0 p-3" style="z-index: 11">
                        <div id="toast-container"></div>
                    </div>
                </form>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal"><i class="bi bi-x-lg"></i> Đóng</button>
                <button type="button" class="btn btn-primary" id="save-order-btn">
                    <i class="bi bi-save"></i> Lưu đơn hàng
                </button>
            </div>
        </div>
    </div>
</div>