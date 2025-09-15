<table class="table table-hover" id="orders-table">
    <thead>
        <tr>
            <th>Mã đơn</th>
            <th>Khách hàng</th>
            <th>Ngày bán</th>
            <th>Nhân viên</th>
            <th>Tổng tiền</th>
            <th>Trạng thái</th>
            <th>Thao tác</th>
        </tr>
    </thead>
    <tbody>
        @forelse ($donBanLes as $don)
        <tr class="view-order-btn" data-id="{{ $don->don_id }}">
            <td><strong>{{ $don->ma_don }}</strong></td>
            <td>
                @if ($don->khachHang)
                    {{ $don->khachHang->ho_ten }}<br>
                    <small class="text-muted">{{ $don->khachHang->sdt }}</small>
                @else
                    <span class="text-muted">Khách lẻ</span>
                @endif
            </td>
            <td>{{ \Carbon\Carbon::parse($don->ngay_ban)->format('d/m/Y H:i') }}</td>
            <td>{{ $don->nguoiDung->ho_ten }}</td>
            <td class="text-right">{{ number_format($don->tong_cong, 0, ',', '.') }} đ</td>
            <td>
                @if(in_array($don->trang_thai, ['hoan_thanh', 'hoan_tat']))
                    <span class="badge badge-hoan-thanh">Hoàn thành</span>
                @elseif($don->trang_thai == 'cho_xu_ly')
                    <span class="badge badge-cho-xu-ly">Chờ xử lý</span>
                @else
                    <span class="badge badge-da-huy">Đã hủy</span>
                @endif
            </td>
            <td>
                <div class="btn-group">
                    <button type="button" class="btn btn-sm btn-primary view-order-btn" data-id="{{ $don->don_id }}">
                        <i class="bi bi-eye"></i>
                    </button>
                    @if(in_array($don->trang_thai, ['hoan_thanh', 'hoan_tat']))
                    <button type="button" class="btn btn-sm btn-danger cancel-order-btn" data-id="{{ $don->don_id }}">
                        <i class="bi bi-ban"></i>
                    </button>
                    @endif
                </div>
            </td>
        </tr>
        @empty
        <tr>
            <td colspan="7" class="text-center">Không có đơn hàng nào</td>
        </tr>
        @endforelse
    </tbody>
</table>

<div class="d-flex justify-content-end mt-4" id="pagination-container">
    {{ $donBanLes->links() }}
</div>
