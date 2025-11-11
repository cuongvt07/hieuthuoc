@extends('layouts.app')

@section('title', 'Quản Lý Thuốc & Nhóm Thuốc - Hệ Thống Quản Lý Hiệu Thuốc')

@section('page-title', 'Quản Lý Thuốc & Nhóm Thuốc')

@section('content')
<div class="alert alert-info">
    <i class="bi bi-info-circle me-2"></i>
    Giao diện kết hợp quản lý Thuốc và Nhóm thuốc giúp bạn dễ dàng thêm, sửa, xóa thuốc và nhóm thuốc trong một màn hình. Chọn một nhóm thuốc từ danh sách bên trái để lọc thuốc theo nhóm.
</div>
<div class="row">
    <!-- Phần quản lý nhóm thuốc -->
    <div class="col-md-4" data-block-type="nhom">
        <div class="card shadow mb-4">
            <div class="card-header py-3 d-flex justify-content-between align-items-center">
                <h6 class="m-0 font-weight-bold">Danh Sách Nhóm Thuốc</h6>
                <button type="button" class="btn btn-primary btn-sm" data-bs-toggle="modal" data-bs-target="#addNhomThuocModal">
                    <i class="bi bi-plus-circle me-1"></i> Thêm Nhóm
                </button>
            </div>
            <div class="card-body">
                <div class="mb-3">
                    <div class="input-group">
                        <input type="text" id="search-nhom" class="form-control form-control-sm" placeholder="Tìm kiếm nhóm thuốc...">
                        <button class="btn btn-outline-secondary btn-sm" type="button" id="searchNhomBtn">
                            <i class="bi bi-search"></i>
                        </button>
                        <button class="btn btn-outline-danger btn-sm" type="button" id="resetNhomBtn" title="Xóa bộ lọc">
                            <i class="bi bi-x-circle"></i>
                        </button>
                    </div>
                </div>

                <div class="list-group nhom-thuoc-list">
                    @foreach ($nhomThuoc as $nhom)
                    <div class="list-group-item list-group-item-action d-flex justify-content-between align-items-center nhom-thuoc-item"
                        data-id="{{ $nhom->nhom_id }}" style="cursor: pointer;">
                        <div>
                            <span class="fw-bold">{{ $nhom->ma_nhom }}</span> - {{ $nhom->ten_nhom }}
                            @if($nhom->trang_thai == 0)
                            <span class="badge bg-danger ms-2">Đã đình chỉ</span>
                            @endif
                        </div>
                        <div>
                            <button type="button" class="btn btn-sm btn-info edit-nhom-btn" data-id="{{ $nhom->nhom_id }}">
                                <i class="bi bi-pencil"></i>
                            </button>
                            <button type="button" class="btn btn-sm btn-warning suspend-nhom-btn" data-id="{{ $nhom->nhom_id }}" data-status="{{ $nhom->trang_thai }}">
                                <i class="bi bi-ban"></i> {{ $nhom->trang_thai == 0 ? 'Bỏ đình chỉ' : 'Đình chỉ' }}
                            </button>
                        </div>
                    </div>
                    @endforeach
                </div>

                <div class="pagination-container" id="pagination-nhom">
                    {{ $nhomThuoc->onEachSide(1)->links('vendor.pagination.custom') }}
                </div>
            </div>
        </div>
    </div>

    <!-- Phần quản lý thuốc -->
    <div class="col-md-8" data-block-type="thuoc">
        <div class="card shadow mb-4">
            <div class="card-header py-3 d-flex justify-content-between align-items-center">
                <div>
                    <h6 class="m-0 font-weight-bold">Danh Sách Thuốc <span id="selected-nhom-name"></span></h6>
                    <small class="text-muted" id="filter-status">Đang hiển thị tất cả thuốc</small>
                </div>
                <button type="button" class="btn btn-primary btn-sm" data-bs-toggle="modal" data-bs-target="#addThuocModal">
                    <i class="bi bi-plus-circle me-1"></i> Thêm Thuốc
                </button>
            </div>
            <div class="card-body">
                <div class="row mb-3">
                    <div class="col-md-6">
                        <div class="input-group">
                            <input type="text" id="search-thuoc" class="form-control" placeholder="Tìm kiếm thuốc...">
                            <button class="btn btn-outline-secondary" type="button" id="searchThuocBtn">
                                <i class="bi bi-search"></i>
                            </button>
                            <button class="btn btn-outline-danger" type="button" id="resetThuocBtn" title="Xóa bộ lọc">
                                <i class="bi bi-x-circle"></i>
                            </button>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="input-group">
                            <select id="filter-kho" class="form-select">
                                <option value="">-- Tất cả kho --</option>
                                @foreach ($kho as $k)
                                <option value="{{ $k->kho_id }}">{{ $k->ten_kho }}</option>
                                @endforeach
                            </select>
                            <button class="btn btn-outline-danger" type="button" id="resetKhoBtn" title="Xóa bộ lọc kho">
                                <i class="bi bi-x-circle"></i>
                            </button>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="input-group">
                            <select id="filter-nhom" class="form-select">
                                <option value="">-- Tất cả nhóm --</option>
                                @foreach ($nhomThuocData as $nhom)
                                <option value="{{ $nhom->nhom_id }}">{{ $nhom->ten_nhom }}</option>
                                @endforeach
                            </select>
                            <button class="btn btn-outline-danger" type="button" id="resetFilterBtn" title="Xóa bộ lọc nhóm">
                                <i class="bi bi-x-circle"></i>
                            </button>
                        </div>
                    </div>

                    <div class="table-responsive">
                        <table class="table table-bordered table-striped" id="thuoc-table" width="100%" cellspacing="0">
                            <thead>
                                <tr>
                                    <th>Mã Thuốc</th>
                                    <th>Tên sản phẩm</th>
                                    <th>Nhóm Thuốc</th>
                                    <th>Kho</th>
                                    <th>Đơn Vị Gốc</th>
                                    <th>Đơn Vị Bán</th>
                                    <th>Tỉ Lệ</th>
                                    <th>Trạng Thái</th>
                                    <th>Thao Tác</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse ($thuoc as $item)
                                <tr>
                                    <td>{{ $item->ma_thuoc }}</td>
                                    <td>{{ $item->ten_thuoc }}
                                        @if($item->trang_thai == 0)
                                        <span class="badge bg-danger ms-2">Đã đình chỉ</span>
                                        @endif
                                    </td>
                                    <td>{{ $item->nhomThuoc->ten_nhom }}</td>
                                    <td>{{ $item->kho->ten_kho ?? 'Không xác định' }}</td>
                                    <td>{{ $item->don_vi_goc }}</td>
                                    <td>{{ $item->don_vi_ban }}</td>
                                    <td>{{ $item->ti_le_quy_doi }}</td>
                                    <td>
                                        @if($item->trang_thai == 0)
                                        <span class="badge bg-danger">Đã đình chỉ</span>
                                        @else
                                        <span class="badge bg-success">Đang hoạt động</span>
                                        @endif
                                    </td>
                                    <td>
                                        <button type="button" class="btn btn-sm btn-info edit-thuoc-btn" data-id="{{ $item->thuoc_id }}">
                                            <i class="bi bi-pencil"></i>
                                        </button>
                                        <button type="button" class="btn btn-sm btn-warning suspend-thuoc-btn" data-id="{{ $item->thuoc_id }}" data-status="{{ $item->trang_thai }}">
                                            <i class="bi bi-ban"></i> {{ $item->trang_thai == 0 ? 'Bỏ đình chỉ' : 'Đình chỉ' }}
                                        </button>
                                    </td>
                                </tr>
                                @empty
                                <tr>
                                    <td colspan="6" class="text-center">Không có dữ liệu</td>
                                </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>

                    <div class="pagination-container" id="pagination-thuoc">
                        {{ $thuoc->onEachSide(1)->links('vendor.pagination.custom') }}
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Modal thêm nhóm thuốc -->
<!-- Modal thêm nhóm thuốc -->
<div class="modal fade" id="addNhomThuocModal" tabindex="-1" aria-labelledby="addNhomThuocModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <form id="addNhomForm">
                <div class="modal-header">
                    <h5 class="modal-title" id="addNhomThuocModalLabel">Thêm Nhóm Thuốc</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <div class="mb-3">
                        <label for="ma_nhom" class="form-label">Mã Nhóm <span class="text-danger">*</span></label>
                        <input type="text" class="form-control" id="ma_nhom" name="ma_nhom" required>
                        <div class="invalid-feedback" id="ma_nhom_error"></div>
                    </div>
                    <div class="mb-3">
                        <label for="ten_nhom" class="form-label">Tên Nhóm <span class="text-danger">*</span></label>
                        <input type="text" class="form-control" id="ten_nhom" name="ten_nhom" required>
                        <div class="invalid-feedback" id="ten_nhom_error"></div>
                    </div>
                    <div class="mb-3">
                        <label for="mo_ta" class="form-label">Mô Tả</label>
                        <textarea class="form-control" id="mo_ta" name="mo_ta" rows="3"></textarea>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Đóng</button>
                    <button type="submit" class="btn btn-primary">Lưu</button>
                </div>
            </form>
        </div>
    </div>
</div>


    <!-- Modal sửa nhóm thuốc -->
    <div class="modal fade" id="editNhomThuocModal" tabindex="-1" aria-labelledby="editNhomThuocModalLabel" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <form id="editNhomForm">
                    <div class="modal-header">
                        <h5 class="modal-title" id="editNhomThuocModalLabel">Chỉnh Sửa Nhóm Thuốc</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                    <div class="modal-body">
                        <input type="hidden" id="edit_nhom_id" name="nhom_id">
                        <div class="mb-3">
                            <label for="edit_ma_nhom" class="form-label">Mã Nhóm <span class="text-danger">*</span></label>
                            <input type="text" class="form-control" id="edit_ma_nhom" name="ma_nhom" requiredmsg="Trường này yêu cầu bắt buộc">
                            <div class="invalid-feedback" id="edit_ma_nhom_error"></div>
                        </div>
                        <div class="mb-3">
                            <label for="edit_ten_nhom" class="form-label">Tên Nhóm <span class="text-danger">*</span></label>
                            <input type="text" class="form-control" id="edit_ten_nhom" name="ten_nhom" requiredmsg="Trường này yêu cầu bắt buộc">
                            <div class="invalid-feedback" id="edit_ten_nhom_error"></div>
                        </div>
                        <div class="mb-3">
                            <label for="edit_mo_ta" class="form-label">Mô Tả</label>
                            <textarea class="form-control" id="edit_mo_ta" name="mo_ta" rows="3"></textarea>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Đóng</button>
                        <button type="submit" class="btn btn-primary">Lưu thay đổi</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- Modal xác nhận xóa nhóm thuốc -->
    <div class="modal fade" id="deleteNhomThuocModal" tabindex="-1" aria-labelledby="deleteNhomThuocModalLabel" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="deleteNhomThuocModalLabel">Xác nhận xóa</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <p>Bạn có chắc chắn muốn xóa nhóm thuốc <span id="delete_nhom_name" class="fw-bold"></span>?</p>
                    <p class="text-danger mb-0">Lưu ý: Hành động này không thể hoàn tác và sẽ xóa tất cả thuốc trong nhóm.</p>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Hủy</button>
                    <button type="button" class="btn btn-danger" id="confirmDeleteNhom">Xóa</button>
                </div>
            </div>
        </div>
    </div>

    <!-- Modal thêm thuốc -->
    <div class="modal fade" id="addThuocModal" tabindex="-1" aria-labelledby="addThuocModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <form id="addThuocForm">
                    <div class="modal-header">
                        <h5 class="modal-title" id="addThuocModalLabel">Thêm Thuốc</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                    <div class="modal-body">
                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label for="ma_thuoc" class="form-label">Mã Thuốc <span class="text-danger">*</span></label>
                                <input type="text" class="form-control" id="ma_thuoc" name="ma_thuoc" requiredmsg="Trường này yêu cầu bắt buộc">
                                <div class="invalid-feedback" id="ma_thuoc_error"></div>
                            </div>
                            <div class="col-md-6 mb-3">
                                <label for="nhom_id" class="form-label">Nhóm Thuốc <span class="text-danger">*</span></label>
                                <select class="form-select" id="nhom_id" name="nhom_id" requiredmsg="Trường này yêu cầu bắt buộc">
                                    <option value="">-- Chọn nhóm thuốc --</option>
                                    @foreach ($nhomThuocData as $nhom)
                                    <option value="{{ $nhom->nhom_id }}">{{ $nhom->ten_nhom }}</option>
                                    @endforeach
                                </select>
                                <div class="invalid-feedback" id="nhom_id_error"></div>
                            </div>
                            <div class="col-md-6 mb-3">
                                <label for="kho_id" class="form-label">Kho <span class="text-danger">*</span></label>
                                <select class="form-select" id="kho_id" name="kho_id" requiredmsg="Trường này yêu cầu bắt buộc">
                                    <option value="">-- Chọn kho --</option>
                                    @foreach ($kho as $k)
                                    <option value="{{ $k->kho_id }}">{{ $k->ten_kho }}</option>
                                    @endforeach
                                </select>
                                <div class="invalid-feedback" id="kho_id_error"></div>
                            </div>
                        </div>
                        <div class="mb-3">
                            <label for="ten_thuoc" class="form-label">Tên sản phẩm <span class="text-danger">*</span></label>
                            <input type="text" class="form-control" id="ten_thuoc" name="ten_thuoc" requiredmsg="Trường này yêu cầu bắt buộc">
                            <div class="invalid-feedback" id="ten_thuoc_error"></div>
                        </div>
                        <div class="row">
                            <div class="col-md-4 mb-3">
                                <label for="don_vi_goc" class="form-label">Đơn Vị Gốc <span class="text-danger">*</span></label>
                                <input type="text" class="form-control" id="don_vi_goc" name="don_vi_goc" requiredmsg="Trường này yêu cầu bắt buộc">
                                <div class="invalid-feedback" id="don_vi_goc_error"></div>
                            </div>
                            <div class="col-md-4 mb-3">
                                <label for="don_vi_ban" class="form-label">Đơn Vị Bán <span class="text-danger">*</span></label>
                                <input type="text" class="form-control" id="don_vi_ban" name="don_vi_ban" requiredmsg="Trường này yêu cầu bắt buộc">
                                <div class="invalid-feedback" id="don_vi_ban_error"></div>
                            </div>
                            <div class="col-md-4 mb-3">
                                <label for="ti_le_quy_doi" class="form-label">Tỉ Lệ Quy Đổi <span class="text-danger">*</span></label>
                                <input type="number" step="0.01" class="form-control" id="ti_le_quy_doi" name="ti_le_quy_doi" requiredmsg="Trường này yêu cầu bắt buộc">
                                <div class="invalid-feedback" id="ti_le_quy_doi_error"></div>
                            </div>
                        </div>
                        <div class="mb-3">
                            <label for="mo_ta_thuoc" class="form-label">Mô Tả</label>
                            <textarea class="form-control" id="mo_ta_thuoc" name="mo_ta" rows="3"></textarea>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Đóng</button>
                        <button type="submit" class="btn btn-primary">Lưu</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- Modal sửa thuốc -->
    <div class="modal fade" id="editThuocModal" tabindex="-1" aria-labelledby="editThuocModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <form id="editThuocForm">
                    <div class="modal-header">
                        <h5 class="modal-title" id="editThuocModalLabel">Chỉnh Sửa Thuốc</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                    <div class="modal-body">
                        <input type="hidden" id="edit_thuoc_id" name="thuoc_id">
                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label for="edit_ma_thuoc" class="form-label">Mã Thuốc <span class="text-danger">*</span></label>
                                <input type="text" class="form-control" id="edit_ma_thuoc" name="ma_thuoc" requiredmsg="Trường này yêu cầu bắt buộc">
                                <div class="invalid-feedback" id="edit_ma_thuoc_error"></div>
                            </div>
                            <div class="col-md-6 mb-3">
                                <label for="edit_nhom_id" class="form-label">Nhóm Thuốc <span class="text-danger">*</span></label>
                                <select class="form-select" id="edit_nhom_id" name="nhom_id" requiredmsg="Trường này yêu cầu bắt buộc">
                                    <option value="">-- Chọn nhóm thuốc --</option>
                                    @foreach ($nhomThuocAll as $nhom)
                                    <option value="{{ $nhom->nhom_id }}">{{ $nhom->ten_nhom }}</option>
                                    @endforeach
                                </select>
                                <div class="invalid-feedback" id="edit_nhom_id_error"></div>
                            </div>
                            <div class="col-md-6 mb-3">
                                <label for="edit_kho_id" class="form-label">Kho <span class="text-danger">*</span></label>
                                <select class="form-select" id="edit_kho_id" name="kho_id" requiredmsg="Trường này yêu cầu bắt buộc">
                                    <option value="">-- Chọn kho --</option>
                                    @foreach ($kho as $k)
                                    <option value="{{ $k->kho_id }}">{{ $k->ten_kho }}</option>
                                    @endforeach
                                </select>
                                <div class="invalid-feedback" id="edit_kho_id_error"></div>
                            </div>
                        </div>
                        <div class="mb-3">
                            <label for="edit_ten_thuoc" class="form-label">Tên sản phẩm <span class="text-danger">*</span></label>
                            <input type="text" class="form-control" id="edit_ten_thuoc" name="ten_thuoc" requiredmsg="Trường này yêu cầu bắt buộc">
                            <div class="invalid-feedback" id="edit_ten_thuoc_error"></div>
                        </div>
                        <div class="row">
                            <div class="col-md-4 mb-3">
                                <label for="edit_don_vi_goc" class="form-label">Đơn Vị Gốc <span class="text-danger">*</span></label>
                                <input type="text" class="form-control" id="edit_don_vi_goc" name="don_vi_goc" requiredmsg="Trường này yêu cầu bắt buộc">
                                <div class="invalid-feedback" id="edit_don_vi_goc_error"></div>
                            </div>
                            <div class="col-md-4 mb-3">
                                <label for="edit_don_vi_ban" class="form-label">Đơn Vị Bán <span class="text-danger">*</span></label>
                                <input type="text" class="form-control" id="edit_don_vi_ban" name="don_vi_ban" requiredmsg="Trường này yêu cầu bắt buộc">
                                <div class="invalid-feedback" id="edit_don_vi_ban_error"></div>
                            </div>
                            <div class="col-md-4 mb-3">
                                <label for="edit_ti_le_quy_doi" class="form-label">Tỉ Lệ Quy Đổi <span class="text-danger">*</span></label>
                                <input type="number" step="0.01" class="form-control" id="edit_ti_le_quy_doi" name="ti_le_quy_doi" requiredmsg="Trường này yêu cầu bắt buộc">
                                <div class="invalid-feedback" id="edit_ti_le_quy_doi_error"></div>
                            </div>
                        </div>
                        <div class="mb-3">
                            <label for="edit_mo_ta_thuoc" class="form-label">Mô Tả</label>
                            <textarea class="form-control" id="edit_mo_ta_thuoc" name="mo_ta" rows="3"></textarea>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Đóng</button>
                        <button type="submit" class="btn btn-primary">Lưu thay đổi</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- Modal xác nhận xóa thuốc -->
    <div class="modal fade" id="deleteThuocModal" tabindex="-1" aria-labelledby="deleteThuocModalLabel" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="deleteThuocModalLabel">Xác nhận xóa</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <p>Bạn có chắc chắn muốn xóa thuốc <span id="delete_thuoc_name" class="fw-bold"></span>?</p>
                    <p class="text-danger mb-0">Lưu ý: Hành động này không thể hoàn tác.</p>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Hủy</button>
                    <button type="button" class="btn btn-danger" id="confirmDeleteThuoc">Xóa</button>
                </div>
            </div>
        </div>
    </div>
    @endsection

@section('scripts')
<script>
    $(document).ready(function() {
        // Biến lưu ID nhóm thuốc đang được chọn
        let selectedNhomId = '';
        let currentThuocPage = 1;
        let currentNhomPage = 1;

        // Lấy vai trò của người dùng từ session hoặc meta tag (giả định vai trò đã được truyền qua view)
        const userRole = '{{ Auth::user()->vai_tro }}'; // Lấy vai trò từ PHP

        // Hàm kiểm tra quyền chỉnh sửa
        function hasEditPermission() {
            return userRole === 'admin';
        }

        // Vô hiệu hóa các nút thao tác nếu không phải admin
        if (!hasEditPermission()) {
            // Ẩn hoặc vô hiệu hóa nút "Thêm Nhóm" và "Thêm Thuốc"
            $('#addNhomThuocModal').parent().find('.btn-primary').prop('disabled', true).addClass('disabled');
            $('#addThuocModal').parent().find('.btn-primary').prop('disabled', true).addClass('disabled');

            // Vô hiệu hóa các nút chỉnh sửa và đình chỉ trong danh sách
            $('.edit-nhom-btn, .suspend-nhom-btn, .edit-thuoc-btn, .suspend-thuoc-btn').prop('disabled', true).addClass('disabled');
        }

        // ===== PHẦN XỬ LÝ NHÓM THUỐC =====

        // Search nhóm thuốc
        $('#searchNhomBtn').click(function() {
            currentNhomPage = 1;
            loadNhomThuoc();
        });
        $('#search-nhom').keypress(function(e) {
            if (e.which == 13) {
                currentNhomPage = 1;
                loadNhomThuoc();
                return false;
            }
        });
        $('#resetNhomBtn').click(function() {
            $('#search-nhom').val('');
            showToast('Đã hiển thị lại tất cả nhóm thuốc', 'info');
            currentNhomPage = 1;
            loadNhomThuoc();
        });
        let nhomThuocSearchTimeout;
        $('#search-nhom').keyup(function() {
            clearTimeout(nhomThuocSearchTimeout);
            if ($(this).val().trim() === '') {
                currentNhomPage = 1;
                loadNhomThuoc();
            } else {
                nhomThuocSearchTimeout = setTimeout(function() {
                    currentNhomPage = 1;
                    loadNhomThuoc();
                }, 500);
            }
        });

        function loadNhomThuoc(page = currentNhomPage) {
            const search = $('#search-nhom').val();
            console.log('Loading Nhom Thuoc with search:', search, 'and page:', page);
            const data = {
                page: page
            };
            if (search && search.trim() !== '') data.search = search.trim();

            return $.ajax({
                url: "{{ route('nhom-thuoc.list') }}",
                type: "GET",
                data: data,
                dataType: "json",
                success: function(response) {
                    let html = '';
                    if (response.nhomThuoc.data.length > 0) {
                        $.each(response.nhomThuoc.data, function(index, nhom) {
                            html += `
                        <div class="list-group-item list-group-item-action d-flex justify-content-between align-items-center nhom-thuoc-item ${selectedNhomId == nhom.nhom_id ? 'active' : ''}" data-id="${nhom.nhom_id}" style="cursor: pointer;">
                            <div>
                                <span class="fw-bold">${nhom.ma_nhom}</span> - ${nhom.ten_nhom}
                                ${nhom.trang_thai == 0 ? '<span class="badge bg-danger ms-2">Đã đình chỉ</span>' : ''}
                            </div>
                            <div>
                                <button type="button" class="btn btn-sm btn-info edit-nhom-btn" data-id="${nhom.nhom_id}" ${!hasEditPermission() ? 'disabled' : ''}><i class="bi bi-pencil"></i></button>
                                <button type="button" class="btn btn-sm btn-warning suspend-nhom-btn" data-id="${nhom.nhom_id}" data-status="${nhom.trang_thai}" ${!hasEditPermission() ? 'disabled' : ''}><i class="bi bi-ban"></i> ${nhom.trang_thai == 0 ? 'Bỏ đình chỉ' : 'Đình chỉ'}</button>
                            </div>
                        </div>`;
                        });
                    } else {
                        html = '<div class="list-group-item">Không có dữ liệu</div>';
                    }
                    $('.nhom-thuoc-list').html(html);
                    $('#pagination-nhom').html(response.links);
                    currentNhomPage = page;

                    console.log('Nhom Thuoc loaded:', response.nhomThuoc);
                },
                error: function() {
                    showToast('Có lỗi xảy ra khi tải danh sách nhóm thuốc', 'danger');
                }
            });
        }

    function updateNhomThuocDropdowns() {
        // Cập nhật dropdown filter (chỉ lấy nhóm active)
        $.ajax({
            url: "/nhom-thuoc/filter-data",
            type: "GET",
            dataType: "json",
            success: function(response) {
                const filterOptions = response.nhomThuocData.map(function(nhom) {
                    return `<option value="${nhom.nhom_id}">${nhom.ten_nhom}</option>`;
                }).join('');

                const currentFilterValue = $('#filter-nhom').val();
                $('#filter-nhom').html(`<option value="">-- Tất cả nhóm --</option>${filterOptions}`);
                $('#filter-nhom').val(currentFilterValue);
            },
            error: function() {
                console.log('Không thể cập nhật dropdown filter nhóm thuốc');
            }
        });

        // Cập nhật dropdown trong modal (lấy tất cả nhóm)
        $.ajax({
            url: "/nhom-thuoc/all",
            type: "GET",
            dataType: "json",
            success: function(response) {
                const currentEditNhomVal = $('#edit_nhom_id').val();

                const activeNhomOptions = response.nhomThuoc.filter(nhom => nhom.trang_thai == 1)
                    .map(function(nhom) {
                        return `<option value="${nhom.nhom_id}">${nhom.ten_nhom}</option>`;
                    }).join('');
                $('#nhom_id').html(`<option value="">-- Chọn nhóm thuốc --</option>${activeNhomOptions}`);

                const allNhomOptions = response.nhomThuoc.map(function(nhom) {
                    return `<option value="${nhom.nhom_id}">${nhom.ten_nhom}</option>`;
                }).join('');
                $('#edit_nhom_id').html(`<option value="">-- Chọn nhóm thuốc --</option>${allNhomOptions}`);
                if (currentEditNhomVal) {
                    $('#edit_nhom_id').val(currentEditNhomVal).trigger('change');
                }
            },
            error: function() {
                console.log('Không thể cập nhật dropdown nhóm thuốc');
            }
        });
    }
 // Tìm đoạn code này trong section('scripts')
    $(document).on('click', '.nhom-thuoc-item', function(e) {
        if ($(e.target).closest('button').length) {
            return;
        }
        e.preventDefault();
        const clickedId = $(this).data('id');
        const isAlreadyActive = $(this).hasClass('active');
        $('.nhom-thuoc-item').removeClass('active');

        if (isAlreadyActive) {
            selectedNhomId = '';
            $('#filter-nhom').val('');
            $('#selected-nhom-name').text('');
            $('#filter-status').text('Đang hiển thị tất cả thuốc');
        } else {
            $(this).addClass('active');
            selectedNhomId = clickedId;
            $('#filter-nhom').val(selectedNhomId);
            const nhomName = $(this).find('div:first').text().trim();
            $('#selected-nhom-name').text(' - ' + nhomName);
            $('#filter-status').text('Đang lọc theo nhóm thuốc');
        }
        currentThuocPage = 1;
        loadThuoc();
    });

    $(document).on('click', '.edit-nhom-btn', function(e) {
        e.preventDefault();
        e.stopPropagation();
        
        if (!hasEditPermission()) {
            showToast('Bạn không có quyền chỉnh sửa nhóm thuốc', 'warning');
            return;
        }
        
        const id = $(this).data('id');
        console.log('Edit nhom clicked:', id);
        
        $.ajax({
            url: "/nhom-thuoc/" + id,
            type: "GET",
            dataType: "json",
            success: function(response) {
                console.log('Nhom data loaded:', response);
                $('#edit_nhom_id').val(response.nhomThuoc.nhom_id);
                $('#edit_ma_nhom').val(response.nhomThuoc.ma_nhom);
                $('#edit_ten_nhom').val(response.nhomThuoc.ten_nhom);
                $('#edit_mo_ta').val(response.nhomThuoc.mo_ta);
                $('#editNhomThuocModal').modal('show');
            },
            error: function(xhr) {
                console.log('Error loading nhom:', xhr);
                showToast('Có lỗi xảy ra khi lấy thông tin nhóm thuốc', 'danger');
            }
        });
    });

    $(document).on('click', '.suspend-nhom-btn', function(e) {
        e.preventDefault();
        e.stopPropagation();
        
        if (!hasEditPermission()) {
            showToast('Bạn không có quyền đình chỉ nhóm thuốc', 'warning');
            return;
        }
        
        const id = $(this).data('id');
        const currentStatus = $(this).data('status');
        const newStatus = currentStatus == 1 ? 0 : 1;
        
        console.log('Suspend clicked - ID:', id, 'Current:', currentStatus, 'New:', newStatus);
        
        $.ajax({
            url: "/nhom-thuoc/" + id + "/suspend",
            type: "POST",
            data: {
                trang_thai: newStatus,
                _token: $('meta[name="csrf-token"]').attr('content')
            },
            success: function(response) {
                console.log('Suspend success:', response);
                showToast(response.message);
                loadNhomThuoc(currentNhomPage);
                loadThuoc(currentThuocPage);
                updateNhomThuocDropdowns(); // Cập nhật dropdown sau khi đình chỉ/bỏ đình chỉ
            },
            error: function(xhr) {
                console.log('Suspend error:', xhr.responseJSON);
                showToast(xhr.responseJSON?.message || 'Có lỗi xảy ra khi thực hiện thao tác', 'danger');
            }
        });
    });


        // ===== PHẦN XỬ LÝ THUỐC =====

        $('#searchThuocBtn').click(function() {
            currentThuocPage = 1;
            loadThuoc();
        });
        $('#search-thuoc').keypress(function(e) {
            if (e.which == 13) {
                currentThuocPage = 1;
                loadThuoc();
                return false;
            }
        });
        let thuocSearchTimeout;
        $('#search-thuoc').keyup(function() {
            clearTimeout(thuocSearchTimeout);
            if ($(this).val().trim() === '') {
                currentThuocPage = 1;
                loadThuoc();
            } else {
                thuocSearchTimeout = setTimeout(function() {
                    currentThuocPage = 1;
                    loadThuoc();
                }, 500);
            }
        });
        $('#resetThuocBtn').click(function() {
            $('#search-thuoc').val('');
            showToast('Đã hiển thị lại tất cả thuốc', 'info');
            currentThuocPage = 1;
            loadThuoc();
        });
        $('#resetFilterBtn').click(function() {
            $('#filter-nhom').val('');
            $('#filter-kho').val('');
            selectedNhomId = '';
            $('#selected-nhom-name').text('');
            $('.nhom-thuoc-item').removeClass('active');
            $('#filter-status').text('Đang hiển thị tất cả thuốc');
            currentThuocPage = 1;
            loadThuoc();
        });

        $('#filter-kho').change(function() {
            currentThuocPage = 1;
            loadThuoc();
        });
        
        $('#resetKhoBtn').click(function() {
            $('#filter-kho').val('');
            currentThuocPage = 1;
            loadThuoc();
        });
        $('#filter-nhom').change(function() {
            const nhomId = $(this).val();
            $('.nhom-thuoc-item').removeClass('active');
            if (nhomId) {
                $(`.nhom-thuoc-item[data-id="${nhomId}"]`).addClass('active');
                const nhomName = $(this).find('option:selected').text();
                $('#selected-nhom-name').text(' - ' + nhomName);
                $('#filter-status').text('Đang lọc theo nhóm thuốc');
            } else {
                selectedNhomId = '';
                $('#selected-nhom-name').text('');
                $('#filter-status').text('Đang hiển thị tất cả thuốc');
            }
            currentThuocPage = 1;
            loadThuoc();
        });

        $(document).on('click', '.suspend-thuoc-btn', function(e) {
            e.preventDefault();
            e.stopPropagation();
            if (!hasEditPermission()) {
                showToast('Bạn không có quyền đình chỉ thuốc', 'warning');
                return;
            }
            const id = $(this).data('id');
            const currentStatus = $(this).data('status');
            const newStatus = currentStatus == 1 ? 0 : 1;
            $.ajax({
                url: "{{ url('thuoc') }}/" + id + "/suspend",
                type: "POST",
                data: {
                    trang_thai: newStatus,
                    _token: $('meta[name="csrf-token"]').attr('content')
                },
                success: function(response) {
                    showToast(response.message);
                    loadThuoc(currentThuocPage);
                },
                error: function() {
                    showToast('Có lỗi xảy ra khi thực hiện thao tác', 'danger');
                }
            });
        });

        $(document).on('click', '.edit-thuoc-btn', function (e) {
            e.preventDefault();

            if (typeof hasEditPermission === 'function' && !hasEditPermission()) {
                showToast('Bạn không có quyền chỉnh sửa thuốc', 'warning');
                return;
            }

            const id = $(this).data('id');

            $.ajax({
                url: `/thuoc/${id}`,
                type: "GET",
                dataType: "json",
                success: function (response) {
                    const thuoc = response.thuoc;
                    console.log('Thuoc data loaded:', thuoc);

                    // Gán dữ liệu cơ bản
                    $('#edit_thuoc_id').val(thuoc.thuoc_id);
                    $('#edit_ma_thuoc').val(thuoc.ma_thuoc);
                    $('#edit_ten_thuoc').val(thuoc.ten_thuoc);
                    $('#edit_don_vi_goc').val(thuoc.don_vi_goc);
                    $('#edit_don_vi_ban').val(thuoc.don_vi_ban);
                    $('#edit_ti_le_quy_doi').val(thuoc.ti_le_quy_doi);
                    $('#edit_mo_ta_thuoc').val(thuoc.mo_ta ?? '');
                    // Delay setting kho select until modal is shown (so option lists are populated)
                    $('#edit_kho_id').data('selected-kho', thuoc.kho_id);

                    // Hiển thị modal
                    const modal = $('#editThuocModal');
                    modal.modal('show');

                    modal.one('shown.bs.modal', function () {
                        const nhomVal = String(thuoc.nhom_id);
                        // scope the select lookup to the modal to avoid collisions
                        const nhomSelect = modal.find('#edit_nhom_id');

                        console.log('Setting nhom_id to:', nhomVal);

                        // Nếu chưa có option, thêm vào
                        if (nhomSelect.find(`option[value="${nhomVal}"]`).length === 0) {
                            const nhomText = thuoc?.nhom_thuoc?.ten_nhom ?? `Nhóm ${nhomVal}`;
                            nhomSelect.append(`<option value="${nhomVal}">${nhomText}</option>`);
                        }

                        // Set giá trị selected
                        nhomSelect.val(nhomVal);

                        // Set kho select similarly (ensure option exists and set value)
                        const khoSelect = modal.find('#edit_kho_id');
                        const selectedKho = String(khoSelect.data('selected-kho') ?? thuoc.kho_id ?? '');
                        if (selectedKho !== '') {
                            if (khoSelect.find(`option[value="${selectedKho}"]`).length === 0) {
                                const khoText = thuoc?.kho?.ten_kho ?? `Kho ${selectedKho}`;
                                khoSelect.append(`<option value="${selectedKho}">${khoText}</option>`);
                            }
                            khoSelect.val(selectedKho);
                        }

                        // keep kho disabled for editing (business decision); ensure it's readable
                        khoSelect.prop('disabled', true);

                        // Trigger update cho Select2 hoặc select thường
                        if (nhomSelect.hasClass('select2-hidden-accessible')) {
                            nhomSelect.trigger('change.select2');
                        } else {
                            nhomSelect.trigger('change');
                        }

                        console.log('✅ Selected value after update:', nhomSelect.val());
                    });


                },
                error: function () {
                    showToast('Có lỗi xảy ra khi lấy thông tin thuốc', 'danger');
                }
            });
        });


        $(document).on('click', '.delete-thuoc-btn', function(e) {
            e.preventDefault();
            if (!hasEditPermission()) {
                showToast('Bạn không có quyền xóa thuốc', 'warning');
                return;
            }
            const id = $(this).data('id');
            const name = $(this).data('name');
            $('#delete_thuoc_name').text(name);
            $('#deleteThuocModal').modal('show');

            $('#confirmDeleteThuoc').off('click').on('click', function() {
                $.ajax({
                    url: "/thuoc/" + id,
                    type: "DELETE",
                    data: {
                        _token: $('meta[name="csrf-token"]').attr('content')
                    },
                    dataType: "json",
                    success: function(response) {
                        $('#deleteThuocModal').modal('hide');
                        showToast(response.message);
                        loadThuoc(currentThuocPage);
                    },
                    error: function(xhr) {
                        $('#deleteThuocModal').modal('hide');
                        showToast(xhr.responseJSON.message, 'danger');
                    }
                });
            });
        });

        function loadThuoc(page = currentThuocPage) {
            const search = $('#search-thuoc').val();
            const nhomId = $('#filter-nhom').val() || selectedNhomId;
            const khoId = $('#filter-kho').val();
            const data = {
                page: page
            };
            if (search && search.trim() !== '') data.search = search.trim();
            if (nhomId && nhomId !== '') data.nhom_id = nhomId;
            if (khoId && khoId !== '') data.kho_id = khoId;

            console.log('Loading Thuoc with data:', data);

            $.ajax({
                url: "{{ route('thuoc.list') }}",
                type: "GET",
                data: data,
                dataType: "json",
                success: function(response) {
                    let html = '';
                    if (response.thuoc.data.length > 0) {
                        response.thuoc.data.forEach(function(item) {
                            html += `
                        <tr>
                            <td>${item.ma_thuoc}</td>
                            <td>${item.ten_thuoc} ${item.trang_thai == 0 ? '<span class="badge bg-danger ms-2">Đã đình chỉ</span>' : ''}</td>
                            <td>${item.nhom_thuoc.ten_nhom}</td>
                            <td>${item.kho.ten_kho}</td>
                            <td>${item.don_vi_goc}</td>
                            <td>${item.don_vi_ban}</td>
                            <td>${item.ti_le_quy_doi}</td>
                            <td>${item.trang_thai == 0 ? '<span class="badge bg-danger">Đã đình chỉ</span>' : '<span class="badge bg-success">Đang hoạt động</span>'}</td>
                            <td>
                                <button type="button" class="btn btn-sm btn-info edit-thuoc-btn" data-id="${item.thuoc_id}" ${!hasEditPermission() ? 'disabled' : ''}><i class="bi bi-pencil"></i></button>
                                <button type="button" class="btn btn-sm btn-warning suspend-thuoc-btn" data-id="${item.thuoc_id}" data-status="${item.trang_thai}" ${!hasEditPermission() ? 'disabled' : ''}><i class="bi bi-ban"></i> ${item.trang_thai == 0 ? 'Bỏ đình chỉ' : 'Đình chỉ'}</button>
                            </td>
                        </tr>`;
                        });
                    } else {
                        html = '<tr><td colspan="9" class="text-center">Không có dữ liệu</td></tr>';
                    }
                    $('#thuoc-table tbody').html(html);
                    $('#pagination-thuoc').html(response.links);
                    currentThuocPage = page;
                },
                error: function() {
                    showToast('Có lỗi xảy ra khi tải danh sách thuốc', 'danger');
                }
            });
        }

        function prependNhomThuocItem(nhom) {
            if (!hasEditPermission()) return; // Không thêm nếu không phải admin
            const html = `
        <div class="list-group-item list-group-item-action d-flex justify-content-between align-items-center nhom-thuoc-item" data-id="${nhom.nhom_id}" style="cursor: pointer;">
            <div>
                <span class="fw-bold">${nhom.ma_nhom}</span> - ${nhom.ten_nhom}
                ${nhom.trang_thai == 0 ? '<span class="badge bg-danger ms-2">Đã đình chỉ</span>' : ''}
            </div>
            <div>
                <button type="button" class="btn btn-sm btn-info edit-nhom-btn" data-id="${nhom.nhom_id}"><i class="bi bi-pencil"></i></button>
                <button type="button" class="btn btn-sm btn-warning suspend-nhom-btn" data-id="${nhom.nhom_id}" data-status="${nhom.trang_thai}"><i class="bi bi-ban"></i> ${nhom.trang_thai == 0 ? 'Bỏ đình chỉ' : 'Đình chỉ'}</button>
            </div>
        </div>`;

            if ($('.nhom-thuoc-list').children().first().hasClass('list-group-item') &&
                $('.nhom-thuoc-list').children().first().text().includes('Không có dữ liệu')) {
                $('.nhom-thuoc-list').html(html);
            } else {
                $('.nhom-thuoc-list').prepend(html);
            }
            bindNhomThuocEvents();
        }

        function prependThuocItem(thuoc) {
            if (!hasEditPermission()) return; // Không thêm nếu không phải admin
            const html = `
                <tr>
                    <td>${thuoc.ma_thuoc}</td>
                    <td>${thuoc.ten_thuoc} ${thuoc.trang_thai == 0 ? '<span class="badge bg-danger ms-2">Đã đình chỉ</span>' : ''}</td>
                    <td>${thuoc.nhom_thuoc.ten_nhom}</td>
                    <td>${thuoc.kho.ten_kho}</td>
                    <td>${thuoc.don_vi_goc}</td>
                    <td>${thuoc.don_vi_ban}</td>
                    <td>${thuoc.ti_le_quy_doi}</td>
                    <td>${thuoc.trang_thai == 0 ? '<span class="badge bg-danger">Đã đình chỉ</span>' : '<span class="badge bg-success">Đang hoạt động</span>'}</td>
                    <td>
                        <button type="button" class="btn btn-sm btn-info edit-thuoc-btn" data-id="${thuoc.thuoc_id}"><i class="bi bi-pencil"></i></button>
                        <button type="button" class="btn btn-sm btn-warning suspend-thuoc-btn" data-id="${thuoc.thuoc_id}" data-status="${thuoc.trang_thai}">
                            <i class="bi bi-ban"></i> ${thuoc.trang_thai == 0 ? 'Bỏ đình chỉ' : 'Đình chỉ'}
                        </button>
                    </td>
                </tr>`;

            if ($('#thuoc-table tbody tr').length === 1 &&
                $('#thuoc-table tbody tr td').text().includes('Không có dữ liệu')) {
                $('#thuoc-table tbody').html(html);
            } else {
                $('#thuoc-table tbody').prepend(html);
            }
        }

        function updateNhomThuocItem(nhom) {
            if (!hasEditPermission()) return; // Không cập nhật nếu không phải admin
            const item = $(`.nhom-thuoc-item[data-id="${nhom.nhom_id}"]`);
            if (item.length) {
                const html = `
            <div>
                <span class="fw-bold">${nhom.ma_nhom}</span> - ${nhom.ten_nhom}
                ${nhom.trang_thai == 0 ? '<span class="badge bg-danger ms-2">Đã đình chỉ</span>' : ''}
            </div>
            <div>
                <button type="button" class="btn btn-sm btn-info edit-nhom-btn" data-id="${nhom.nhom_id}"><i class="bi bi-pencil"></i></button>
                <button type="button" class="btn btn-sm btn-warning suspend-nhom-btn" data-id="${nhom.nhom_id}" data-status="${nhom.trang_thai}"><i class="bi bi-ban"></i> ${nhom.trang_thai == 0 ? 'Bỏ đình chỉ' : 'Đình chỉ'}</button>
            </div>`;
                item.html(html);
                bindNhomThuocEvents();
            }
        }

        $('#addNhomForm').submit(function(e) {
            e.preventDefault();
            if (!hasEditPermission()) {
                showToast('Bạn không có quyền thêm nhóm thuốc', 'warning');
                return;
            }

            const formData = {
                ma_nhom: $('#ma_nhom').val(),
                ten_nhom: $('#ten_nhom').val(),
                mo_ta: $('#mo_ta').val(),
                _token: $('meta[name="csrf-token"]').attr('content')
            };

            $.ajax({
                url: "/nhom-thuoc",
                type: "POST",
                data: formData,
                dataType: "json",
                success: function(response) {
                    $('#addNhomThuocModal').modal('hide');
                    $('#addNhomForm')[0].reset();
                    $('.is-invalid').removeClass('is-invalid');
                    showToast(response.message);
                    prependNhomThuocItem(response.nhomThuoc);
                    updateNhomThuocDropdowns();
                },
                error: function(xhr) {
                    const resp = xhr.responseJSON || {};
                    const errors = resp.errors || {};
                    $('#addNhomForm .is-invalid').removeClass('is-invalid');
                    if (errors.ma_nhom) {
                        $('#ma_nhom').addClass('is-invalid');
                        $('#ma_nhom_error').text(errors.ma_nhom[0]);
                    }
                    if (errors.ten_nhom) {
                        $('#ten_nhom').addClass('is-invalid');
                        $('#ten_nhom_error').text(errors.ten_nhom[0]);
                    }
                    // If there is a server message, show it; otherwise log full xhr for debugging
                    if (resp.message) {
                        showToast(resp.message, 'danger');
                    } else if (Object.keys(errors).length === 0) {
                        console.error('Add Nhom error:', xhr);
                        showToast('Có lỗi xảy ra khi thêm nhóm thuốc', 'danger');
                    }
                }
            });
        });

        $('#editNhomForm').submit(function(e) {
            e.preventDefault();
            if (!hasEditPermission()) {
                showToast('Bạn không có quyền chỉnh sửa nhóm thuốc', 'warning');
                return;
            }

            const id = $('#edit_nhom_id').val();
            const formData = {
                ma_nhom: $('#edit_ma_nhom').val(),
                ten_nhom: $('#edit_ten_nhom').val(),
                mo_ta: $('#edit_mo_ta').val(),
                _token: $('meta[name="csrf-token"]').attr('content')
            };

            $.ajax({
                url: "/nhom-thuoc/" + id,
                type: "PUT",
                data: formData,
                dataType: "json",
                success: function(response) {
                    $('#editNhomThuocModal').modal('hide');
                    $('.is-invalid').removeClass('is-invalid');
                    showToast(response.message);
                    updateNhomThuocItem(response.nhomThuoc);
                    updateNhomThuocDropdowns();
                    if (selectedNhomId == id) {
                        loadThuoc(currentThuocPage);
                    }
                },
                error: function(xhr) {
                    const resp = xhr.responseJSON || {};
                    const errors = resp.errors || {};
                    $('#editNhomForm .is-invalid').removeClass('is-invalid');
                    if (errors.ma_nhom) {
                        $('#edit_ma_nhom').addClass('is-invalid');
                        $('#edit_ma_nhom_error').text(errors.ma_nhom[0]);
                    }
                    if (errors.ten_nhom) {
                        $('#edit_ten_nhom').addClass('is-invalid');
                        $('#edit_ten_nhom_error').text(errors.ten_nhom[0]);
                    }
                    if (resp.message) {
                        showToast(resp.message, 'danger');
                    } else if (Object.keys(errors).length === 0) {
                        console.error('Edit Nhom error:', xhr);
                        showToast('Có lỗi xảy ra khi cập nhật nhóm thuốc', 'danger');
                    }
                }
            });
        });

        $('#addThuocForm').submit(function(e) {
            e.preventDefault();
            if (!hasEditPermission()) {
                showToast('Bạn không có quyền thêm thuốc', 'warning');
                return;
            }

            const formData = {
                ma_thuoc: $('#ma_thuoc').val(),
                nhom_id: $('#nhom_id').val(),
                kho_id: $('#kho_id').val(),
                ten_thuoc: $('#ten_thuoc').val(),
                don_vi_goc: $('#don_vi_goc').val(),
                don_vi_ban: $('#don_vi_ban').val(),
                ti_le_quy_doi: $('#ti_le_quy_doi').val(),
                mo_ta: $('#mo_ta_thuoc').val(),
                _token: $('meta[name="csrf-token"]').attr('content')
            };

            $.ajax({
                url: "/thuoc",
                type: "POST",
                data: formData,
                dataType: "json",
                success: function(response) {
                    $('#addThuocModal').modal('hide');
                    $('#addThuocForm')[0].reset();
                    $('.is-invalid').removeClass('is-invalid');
                    showToast(response.message);
                    const shouldShowInList = !selectedNhomId || selectedNhomId == response.thuoc.nhom_id;
                    const hasSearch = $('#search-thuoc').val().trim() !== '';
                    if (shouldShowInList && !hasSearch) {
                        prependThuocItem(response.thuoc);
                    } else {
                        loadThuoc(currentThuocPage);
                    }
                },
                error: function(xhr) {
                    const errors = xhr.responseJSON.errors;
                    $('#addThuocForm .is-invalid').removeClass('is-invalid');
                    if (errors) {
                        Object.keys(errors).forEach(function(key) {
                            $(`#${key}`).addClass('is-invalid');
                            $(`#${key}_error`).text(errors[key][0]);
                        });
                    }
                }
            });
        });

$('#editThuocForm').submit(function(e) {
    e.preventDefault();

    if (!hasEditPermission()) {
        showToast('Bạn không có quyền chỉnh sửa thuốc', 'warning');
        return;
    }

    const modal = $('#editThuocModal');
    const id = modal.find('#edit_thuoc_id').val();
    console.log('👉 Editing thuoc ID:', id);

    // Read values from inside the modal to avoid collision with other elements
    const nhomVal = modal.find('#edit_nhom_id').val();
    const khoVal = modal.find('#edit_kho_id').val();

    console.log('👉 Nhóm thuốc select value (from modal):', nhomVal);
    console.log('👉 Kho select value (from modal):', khoVal);

    const formData = {
        ma_thuoc: modal.find('#edit_ma_thuoc').val(),
        nhom_id: nhomVal,
        kho_id: khoVal,
        ten_thuoc: modal.find('#edit_ten_thuoc').val(),
        don_vi_goc: modal.find('#edit_don_vi_goc').val(),
        don_vi_ban: modal.find('#edit_don_vi_ban').val(),
        ti_le_quy_doi: modal.find('#edit_ti_le_quy_doi').val(),
        mo_ta: modal.find('#edit_mo_ta_thuoc').val(),
        _token: $('meta[name="csrf-token"]').attr('content')
    };

    console.log('Submitting editThuocForm payload:', formData);

    $.ajax({
        url: `/thuoc/${id}`,
        type: "PUT",
        data: formData,
        dataType: "json",
        success: function(response) {
            $('#editThuocModal').modal('hide');
            $('.is-invalid').removeClass('is-invalid');
            showToast(response.message);
            updateThuocItemInTable(response.thuoc);
        },
        error: function(xhr) {
            const errors = xhr.responseJSON?.errors;
            $('#editThuocForm .is-invalid').removeClass('is-invalid');
            if (errors) {
                Object.keys(errors).forEach(function(key) {
                    $(`#edit_${key}`).addClass('is-invalid');
                    $(`#edit_${key}_error`).text(errors[key][0]);
                });
            }
        }
    });
});


        function updateThuocItemInTable(thuoc) {
            if (!hasEditPermission()) return; // Không cập nhật nếu không phải admin
            const row = $(`#thuoc-table tbody tr`).filter(function() {
                return $(this).find('.edit-thuoc-btn').data('id') == thuoc.thuoc_id;
            });
            if (row.length) {
                const html = `
            <td>${thuoc.ma_thuoc}</td>
            <td>${thuoc.ten_thuoc} ${thuoc.trang_thai == 0 ? '<span class="badge bg-danger ms-2">Đã đình chỉ</span>' : ''}</td>
            <td>${thuoc.nhom_thuoc.ten_nhom}</td>
            <td>${thuoc.kho.ten_kho}</td>
            <td>${thuoc.don_vi_goc}</td>
            <td>${thuoc.don_vi_ban}</td>
            <td>${thuoc.ti_le_quy_doi}</td>
            <td>${thuoc.trang_thai == 0 ? '<span class="badge bg-danger">Đã đình chỉ</span>' : '<span class="badge bg-success">Đang hoạt động</span>'}</td>
            <td>
                <button type="button" class="btn btn-sm btn-info edit-thuoc-btn" data-id="${thuoc.thuoc_id}"><i class="bi bi-pencil"></i></button>
                <button type="button" class="btn btn-sm btn-warning suspend-thuoc-btn" data-id="${thuoc.thuoc_id}" data-status="${thuoc.trang_thai}"><i class="bi bi-ban"></i> ${thuoc.trang_thai == 0 ? 'Bỏ đình chỉ' : 'Đình chỉ'}</button>
            </td>`;
                row.html(html);
            }
        }

        $('#addNhomThuocModal').on('hidden.bs.modal', function() {
            $('#addNhomForm')[0].reset();
            $('.is-invalid').removeClass('is-invalid');
        });

        $('#editNhomThuocModal').on('hidden.bs.modal', function() {
            $('.is-invalid').removeClass('is-invalid');
        });

        $('#addThuocModal').on('hidden.bs.modal', function() {
            $('#addThuocForm')[0].reset();
            $('.is-invalid').removeClass('is-invalid');
        });

        $('#editThuocModal').on('hidden.bs.modal', function() {
            $('.is-invalid').removeClass('is-invalid');
            // Re-enable kho select when modal is closed so it is editable next time when needed
            $('#edit_kho_id').prop('disabled', false);
        });

        function showToast(message, type = 'success') {
            if (typeof window.showToast === 'function') {
                window.showToast(message, type);
            } else {
                alert(message);
            }
        }

        // ===== XỬ LÝ PHÂN TRANG ĐỘC LẬP =====
        $(document).on('click', '.pagination-link', function(e) {
            e.preventDefault();
            
            const page = $(this).data('page');
            if (!page) return;

            // Xác định khối nào đang được click
            const paginationContainer = $(this).closest('.pagination-container');
            const blockType = paginationContainer.attr('id') === 'pagination-nhom' ? 'nhom' : 'thuoc';
            
            console.log('Pagination clicked - Block:', blockType, 'Page:', page);

            if (blockType === 'nhom') {
                currentNhomPage = page;
                loadNhomThuoc(page);
            } else {
                currentThuocPage = page;
                loadThuoc(page);
            }
        });

        // ===== KHỞI TẠO =====
        // Dropdown nhóm thuốc đã được render sẵn từ server
        // Chỉ cần update khi có thay đổi (thêm/sửa/xóa/đình chỉ nhóm)
    });
</script>
@endsection