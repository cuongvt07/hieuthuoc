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
    .status-cancelled {
        background-color: #dc3545;
    }
    .table-filter {
        background-color: #f8f9fa;
        padding: 1rem;
        border-radius: 0.25rem;
        margin-bottom: 1rem;
    }
</style>
@endsection

@section('content')
<div class="row mb-4">
    <div class="col-md-6">
        <form id="filterForm" class="table-filter">
            <div class="row g-2">
                <div class="col-md-6">
                    <label for="ncc_id" class="form-label small">Nhà cung cấp</label>
                    <select class="form-select form-select-sm" id="ncc_id" name="ncc_id">
                        <option value="">Tất cả</option>
                        @foreach($nhaCungCaps as $ncc)
                            <option value="{{ $ncc->ncc_id }}" @if(request('ncc_id') == $ncc->ncc_id) selected @endif>{{ $ncc->ten_ncc }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-6">
                    <label for="trang_thai" class="form-label small">Trạng thái</label>
                    <select class="form-select form-select-sm" id="trang_thai" name="trang_thai">
                        <option value="">Tất cả</option>
                        <option value="hoàn_thành" @if(request('trang_thai') == 'hoàn_thành') selected @endif>Hoàn thành</option>
                        <option value="nháp" @if(request('trang_thai') == 'nháp') selected @endif>Nháp</option>
                        <option value="đã_hủy" @if(request('trang_thai') == 'đã_hủy') selected @endif>Đã hủy</option>
                    </select>
                </div>
                <div class="col-md-6">
                    <label for="tu_ngay" class="form-label small">Từ ngày</label>
                    <input type="date" class="form-control form-control-sm" id="tu_ngay" name="tu_ngay" value="{{ request('tu_ngay') }}">
                </div>
                <div class="col-md-6">
                    <label for="den_ngay" class="form-label small">Đến ngày</label>
                    <input type="date" class="form-control form-control-sm" id="den_ngay" name="den_ngay" value="{{ request('den_ngay') }}">
                </div>
                <div class="col-12">
                    <label for="keyword" class="form-label small">Tìm kiếm</label>
                    <div class="input-group input-group-sm">
                        <input type="text" class="form-control" id="keyword" name="keyword" placeholder="Tìm theo mã phiếu, ghi chú..." value="{{ request('keyword') }}">
                        <button class="btn btn-primary" type="submit">
                            <i class="bi bi-search"></i>
                        </button>
                        <button class="btn btn-outline-secondary" type="button" id="resetFilter">
                            <i class="bi bi-x-circle"></i> Reset
                        </button>
                    </div>
                </div>
            </div>
        </form>
    </div>
    <div class="col-md-6 text-end">
        <a href="{{ route('phieu-nhap.create') }}" class="btn btn-primary mb-3">
            <i class="bi bi-plus-circle me-1"></i> Tạo Phiếu Nhập Mới
        </a>
    </div>
</div>

<div class="card">
    <div class="card-body">
        <div class="table-responsive">
            <table class="table table-hover table-striped">
                <thead class="table-light">
                    <tr>
                        <th>Mã Phiếu</th>
                        <th>Nhà Cung Cấp</th>
                        <th>Ngày Nhập</th>
                        <th>Tổng Tiền</th>
                        <th>VAT</th>
                        <th>Tổng Cộng</th>
                        <th>Người Tạo</th>
                        <th>Trạng Thái</th>
                        <th class="text-center">Thao Tác</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($phieuNhaps as $phieuNhap)
                    <tr>
                        <td>{{ $phieuNhap->ma_phieu }}</td>
                        <td>{{ $phieuNhap->nhaCungCap->ten_ncc }}</td>
                        <td>{{ \Carbon\Carbon::parse($phieuNhap->ngay_nhap)->format('d/m/Y') }}</td>
                        <td>{{ number_format($phieuNhap->tong_tien) }}</td>
                        <td>{{ number_format($phieuNhap->vat) }}</td>
                        <td><strong>{{ number_format($phieuNhap->tong_cong) }}</strong></td>
                        <td>{{ $phieuNhap->nguoiDung->ho_ten ?? 'N/A' }}</td>
                        <td>
                            @if($phieuNhap->trang_thai === 'hoàn_thành')
                                <span class="badge status-badge status-completed">Hoàn thành</span>
                            @elseif($phieuNhap->trang_thai === 'nháp')
                                <span class="badge status-badge status-draft">Nháp</span>
                            @else
                                <span class="badge status-badge status-cancelled">Đã hủy</span>
                            @endif
                        </td>
                        <td class="text-center">
                            <a href="{{ route('phieu-nhap.show', $phieuNhap->phieu_id) }}" class="btn btn-sm btn-info">
                                <i class="bi bi-eye"></i>
                            </a>
                            @if($phieuNhap->trang_thai === 'nháp')
                            <a href="{{ route('phieu-nhap.edit', $phieuNhap->phieu_id) }}" class="btn btn-sm btn-warning">
                                <i class="bi bi-pencil"></i>
                            </a>
                            <button type="button" class="btn btn-sm btn-danger delete-btn" 
                                    data-id="{{ $phieuNhap->phieu_id }}"
                                    data-ma="{{ $phieuNhap->ma_phieu }}">
                                <i class="bi bi-trash"></i>
                            </button>
                            @endif
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="9" class="text-center py-4">
                            <div class="alert alert-info mb-0">
                                <i class="bi bi-info-circle me-2"></i> Không tìm thấy phiếu nhập nào.
                            </div>
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <div class="d-flex justify-content-end mt-3">
            {{ $phieuNhaps->appends(request()->query())->links() }}
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
        // Xử lý form lọc
        $('#ncc_id, #trang_thai, #tu_ngay, #den_ngay').change(function() {
            $('#filterForm').submit();
        });
        
        // Reset filter
        $('#resetFilter').click(function() {
            $('#ncc_id').val('');
            $('#trang_thai').val('');
            $('#tu_ngay').val('');
            $('#den_ngay').val('');
            $('#keyword').val('');
            $('#filterForm').submit();
        });
        
        // Xử lý nút xóa
        $('.delete-btn').click(function() {
            const id = $(this).data('id');
            const maPhieu = $(this).data('ma');
            $('#delete-ma-phieu').text(maPhieu);
            $('#deleteForm').attr('action', `/phieu-nhap/${id}`);
            $('#deleteModal').modal('show');
        });
    });
</script>
@endsection
