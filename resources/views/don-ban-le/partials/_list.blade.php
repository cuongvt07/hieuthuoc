
<table class="table table-bordered table-hover" id="dataTable" width="100%" cellspacing="0">
    <thead>
        <tr>
            <th>STT</th>
            <th>Mã đơn</th>
            <th>Khách hàng</th>
            <th>Ngày bán</th>
            <th>Nhân viên</th>
            <th>Tổng tiền</th>
            <th>Trạng thái</th>
            <th width="120">Thao tác</th>
        </tr>
    </thead>
    <tbody>
        @forelse($donBanLes as $index => $don)
        <tr>
            <td>{{ ($donBanLes->currentPage() - 1) * $donBanLes->perPage() + $index + 1 }}</td>
            <td>
                <strong>{{ $don->ma_don }}</strong>
            </td>
            <td>
                @if($don->khachHang)
                    <div>{{ $don->khachHang->ho_ten }}</div>
                    <small class="text-muted">{{ $don->khachHang->sdt }}</small>
                @else
                    <span class="text-muted">Khách lẻ</span>
                @endif
            </td>
            <td>{{ \Carbon\Carbon::parse($don->ngay_ban)->format('d/m/Y H:i') }}</td>
            <td>{{ $don->nguoiDung->ho_ten ?? 'N/A' }}</td>
            <td class="text-right">
                <strong>{{ number_format($don->tong_cong, 0, ',', '.') }} đ</strong>
            </td>
            <td>
                @switch($don->trang_thai)
                    @case('hoan_tat')
                    @case('hoan_thanh')
                        <span class="badge badge-success">Hoàn tất</span>
                        @break
                    @case('cho_xu_ly')
                        <span class="badge badge-warning">Chờ xử lý</span>
                        @break
                    @case('huy')
                    @case('da_huy')
                        <span class="badge badge-danger">Đã hủy</span>
                        @break
                    @default
                        <span class="badge badge-secondary">{{ $don->trang_thai }}</span>
                @endswitch
            </td>
            <td>
                <div class="dropdown">
                    <button class="btn btn-sm btn-outline-primary dropdown-toggle" type="button" 
                            id="dropdownMenuButton{{ $don->don_id }}" data-bs-toggle="dropdown" 
                            aria-expanded="false">
                        <i class="fas fa-cog"></i> Thao tác
                    </button>
                    <ul class="dropdown-menu dropdown-menu-end" aria-labelledby="dropdownMenuButton{{ $don->don_id }}">
                        <!-- Xem chi tiết - luôn hiển thị -->
                        <li>
                            <a class="dropdown-item view-order-btn" href="javascript:void(0)" 
                               data-id="{{ $don->don_id }}">
                                <i class="fas fa-eye text-info me-2"></i> Xem chi tiết
                            </a>
                        </li>
                        @if(Auth::user() && Auth::user()->vai_tro === 'duoc_si')
                            @if($don->trang_thai == 'cho_xu_ly')
                                <!-- Hoàn tất đơn - chỉ hiển thị khi chờ xử lý -->
                                <li>
                                    <a class="dropdown-item complete-order-btn" href="javascript:void(0)" 
                                       data-id="{{ $don->don_id }}" data-ma-don="{{ $don->ma_don }}">
                                        <i class="fas fa-check-circle text-success me-2"></i> Hoàn tất đơn
                                    </a>
                                </li>
                                <!-- Hủy đơn - chỉ hiển thị khi chờ xử lý -->
                                <li>
                                    <a class="dropdown-item cancel-order-btn" href="javascript:void(0)" 
                                       data-id="{{ $don->don_id }}" data-ma-don="{{ $don->ma_don }}">
                                        <i class="fas fa-ban text-danger me-2"></i> Hủy đơn
                                    </a>
                                </li>
                            @endif
                            @if(in_array($don->trang_thai, ['hoan_tat', 'hoan_thanh']))
                                <!-- In hóa đơn - chỉ hiển thị khi đã hoàn tất -->
                                <li><hr class="dropdown-divider"></li>
                                <li>
                                    <a class="dropdown-item" href="{{ route('don-ban-le.print', $don->don_id) }}" 
                                       target="_blank">
                                        <i class="fas fa-print text-primary me-2"></i> In hóa đơn
                                    </a>
                                </li>
                            @endif
                        @endif
                    </ul>
                </div>
            </td>
        </tr>
        @empty
        <tr>
            <td colspan="8" class="text-center">Không có dữ liệu</td>
        </tr>
        @endforelse
    </tbody>
</table>

@if($donBanLes->hasPages())
<div class="d-flex justify-content-between align-items-center mt-3">
    <div>
        Hiển thị {{ $donBanLes->firstItem() }}-{{ $donBanLes->lastItem() }} 
        trong tổng số {{ $donBanLes->total() }} kết quả
    </div>
    {{ $donBanLes->onEachSide(1)->links('vendor.pagination.custom') }}
</div>
@endif

<div class="d-flex justify-content-end mt-4" id="pagination-container">
    {{ $donBanLes->onEachSide(1)->links('vendor.pagination.custom') }}
</div>
