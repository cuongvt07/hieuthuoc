<!-- Modal chi tiết đơn bán lẻ -->
<div class="modal fade" id="orderDetailModal" tabindex="-1" aria-labelledby="orderDetailModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="orderDetailModalLabel">Chi tiết đơn bán lẻ</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <div class="row mb-4">
                    <div class="col-md-6">
                        <h5 class="font-weight-bold">Thông tin đơn hàng</h5>
                        <p><strong>Mã đơn:</strong> <span id="detail-ma-don"></span></p>
                        <p><strong>Ngày bán:</strong> <span id="detail-ngay-ban"></span></p>
                        <p><strong>Trạng thái:</strong> <span id="detail-trang-thai"></span></p>
                    </div>
                    <div class="col-md-6">
                        <h5 class="font-weight-bold">Thông tin khách hàng</h5>
                        <p><strong>Tên khách hàng:</strong> <span id="detail-ten-khach"></span></p>
                        <p><strong>Số điện thoại:</strong> <span id="detail-sdt-khach"></span></p>
                        <p><strong>Nhân viên bán:</strong> <span id="detail-nhan-vien"></span></p>
                    </div>
                </div>

                <h5 class="font-weight-bold">Chi tiết sản phẩm</h5>
                <div class="table-responsive">
                    <table class="table table-bordered" id="detail-products-table">
                        <thead>
                            <tr>
                                <th>STT</th>
                                <th>Tên sản phẩm</th>
                                <th>Đơn vị</th>
                                <th>Số lô</th>
                                <th>Số lượng</th>
                                <th>Đơn giá</th>
                                <th>Thành tiền</th>
                            </tr>
                        </thead>
                        <tbody>
                            <!-- Chi tiết sản phẩm sẽ được thêm ở đây -->
                        </tbody>
                        <tfoot>
                            <tr>
                                <th colspan="6" class="text-right">Tổng cộng:</th>
                                <th class="text-right" id="detail-tong-cong"></th>
                            </tr>
                        </tfoot>
                    </table>
                </div>

                <div class="alert alert-danger" id="cancel-error-message" style="display: none;"></div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Đóng</button>
                <a href="#" class="btn btn-info" id="detail-print-btn" target="_blank">
                    <i class="fas fa-print"></i> In đơn hàng
                </a>
                <button type="button" class="btn btn-danger" id="detail-cancel-btn">
                    <i class="fas fa-ban"></i> Hủy đơn
                </button>
            </div>
        </div>
    </div>
</div>
