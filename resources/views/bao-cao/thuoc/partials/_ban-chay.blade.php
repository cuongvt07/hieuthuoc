<!-- Bảng danh sách top thuốc bán chạy/bán ế -->
<div class="card">
    <div class="card-body">
        <div class="table-responsive">
            <table class="table table-bordered table-hover">
                <thead>
                    <tr>
                        <th>STT</th>
                        <th>Tên sản phẩm</th>
                        <th>Số đơn hàng</th>
                        <th>Tổng số lượng</th>
                        <th>Doanh số</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($thuocs as $index => $thuoc)
                        <tr>
                            <td>{{ $index + 1 }}</td>
                            <td>{{ $thuoc->ten_thuoc }}</td>
                            <td class="text-end">{{ number_format($thuoc->so_don) }}</td>
                            <td class="text-end">{{ number_format($thuoc->tong_so_luong) }}</td>
                            <td class="text-end">{{ number_format($thuoc->doanh_so) }} VNĐ</td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="5" class="text-center">Không có dữ liệu</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>