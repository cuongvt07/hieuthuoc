@extends('layouts.app')

@section('title', 'Báo Cáo Tồn Kho - Hệ Thống Quản Lý Hiệu Thuốc')

@section('page-title', 'Báo Cáo Tồn Kho')

@section('styles')
<link rel="stylesheet" href="https://code.jquery.com/ui/1.13.2/themes/base/jquery-ui.css">
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css">
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/select2-bootstrap-5-theme@1.3.0/dist/select2-bootstrap-5-theme.min.css">
<style>
    .table-filter {
        background-color: #f8f9fa;
        padding: 1rem;
        border-radius: 0.25rem;
        margin-bottom: 1rem;
    }
    
    .status-badge {
        font-size: 0.85rem;
        font-weight: 500;
    }
    .expired {
        background-color: #dc3545;
    }
    .near-expiry {
        background-color: #ffc107;
    }
    .normal {
        background-color: #28a745;
    }
    .out-of-stock {
        background-color: #6c757d;
    }
    
    @media print {
        .no-print {
            display: none;
        }
        .page-title {
            text-align: center;
            font-size: 18pt;
            font-weight: bold;
            margin-bottom: 10px;
        }
        .print-header {
            text-align: center;
            margin-bottom: 20px;
        }
        .print-footer {
            text-align: center;
            margin-top: 30px;
        }
    }
</style>
@endsection

@section('content')
@if(isset($error))
<div class="alert alert-danger">
    {{ $error }}
</div>
@endif

<div class="row mb-4 no-print">
    <div class="col-md-12">
        <div class="card table-filter">
            <div class="card-body">
                <form id="filterForm" method="GET" action="{{ route('bao-cao.ton-kho.index') }}">
                    <div class="row g-3">
                        <div class="col-md-4">
                            <label for="loai_bao_cao" class="form-label small">Loại báo cáo</label>
                            <select class="form-select form-select-sm" id="loai_bao_cao" name="loai_bao_cao">
                                <option value="lo" {{ request('loai_bao_cao', 'lo') == 'lo' ? 'selected' : '' }}>Theo lô thuốc</option>
                                <option value="thuoc" {{ request('loai_bao_cao') == 'thuoc' ? 'selected' : '' }}>Theo thuốc</option>
                                <option value="kho" {{ request('loai_bao_cao') == 'kho' ? 'selected' : '' }}>Theo kho</option>
                                <option value="khach_hang" {{ request('loai_bao_cao') == 'khach_hang' ? 'selected' : '' }}>Theo khách hàng</option>
                            </select>
                        </div>
                        <div class="col-md-4">
                            <label for="ngay_bao_cao" class="form-label small">Đến ngày</label>
                            <input type="text" class="form-control form-control-sm datepicker" id="ngay_bao_cao" name="ngay_bao_cao" value="{{ request('ngay_bao_cao', now()->format('d/m/Y')) }}" placeholder="dd/mm/yyyy">
                        </div>
                        <div class="col-md-4">
                            <label for="kho_id" class="form-label small">Kho hàng</label>
                            <select class="form-select form-select-sm select2" id="kho_id" name="kho_id">
                                <option value="">-- Tất cả kho --</option>
                                @foreach($khos as $kho)
                                <option value="{{ $kho->kho_id }}" {{ request('kho_id') == $kho->kho_id ? 'selected' : '' }}>{{ $kho->ten_kho }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-md-4">
                            <label for="thuoc_id" class="form-label small">Thuốc</label>
                            <select class="form-select form-select-sm select2" id="thuoc_id" name="thuoc_id">
                                <option value="">-- Tất cả thuốc --</option>
                                @foreach($thuocs ?? [] as $thuoc)
                                <option value="{{ $thuoc->thuoc_id }}" {{ request('thuoc_id') == $thuoc->thuoc_id ? 'selected' : '' }}>{{ $thuoc->ten_thuoc }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-md-4">
                            <label for="trang_thai" class="form-label small">Trạng thái</label>
                            <select class="form-select form-select-sm" id="trang_thai" name="trang_thai">
                                <option value="">-- Tất cả trạng thái --</option>
                                <option value="het_han" {{ request('trang_thai') == 'het_han' ? 'selected' : '' }}>Đã hết hạn</option>
                                <option value="sap_het_han" {{ request('trang_thai') == 'sap_het_han' ? 'selected' : '' }}>Sắp hết hạn (< 3 tháng)</option>
                                <option value="con_han" {{ request('trang_thai') == 'con_han' ? 'selected' : '' }}>Còn hạn (> 3 tháng)</option>
                            </select>
                        </div>
                        <div class="col-md-4">
                            <label for="khach_hang_id" class="form-label small">Khách hàng</label>
                            <select class="form-select form-select-sm select2" id="khach_hang_id" name="khach_hang_id">
                                <option value="">-- Tất cả khách hàng --</option>
                                @foreach($khachHangs ?? [] as $khachHang)
                                <option value="{{ $khachHang->khach_hang_id }}" {{ request('khach_hang_id') == $khachHang->khach_hang_id ? 'selected' : '' }}>{{ $khachHang->ho_ten }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-md-4">
                            <div class="form-check mt-4">
                                <input class="form-check-input" type="checkbox" id="con_ton" name="con_ton" value="1" {{ request('con_ton') == '1' ? 'checked' : '' }}>
                                <label class="form-check-label" for="con_ton">
                                    Chỉ hiện các lô còn tồn kho
                                </label>
                            </div>
                        </div>
                        <div class="col-md-2 d-flex align-items-end">
                            <button type="submit" class="btn btn-primary me-2">Lọc</button>
                            <button type="button" id="resetFilterBtn" class="btn btn-secondary me-2">Reset</button>
                            <a href="{{ route('bao-cao.ton-kho.index', ['export' => 'excel'] + request()->all()) }}"
                               class="btn btn-success">
                                <i class="bi bi-file-earmark-excel me-1"></i> Xuất Excel
                            </a>
                                <button type="button" class="btn btn-secondary" id="resetFilter">
                                    <i class="bi bi-x-circle"></i> Reset
                                </button>
                            <button type="button" id="btnPrint" class="btn btn-sm btn-info text-white">
                                <i class="bi bi-printer me-1"></i> In báo cáo
                            </button>
                            <a href="{{ route('bao-cao.ton-kho.index') }}" class="btn btn-sm btn-secondary">
                                <i class="bi bi-arrow-counterclockwise me-1"></i> Làm mới
                            </a>
                        </div>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

<!-- Phần header cho in ấn -->
<div class="print-header d-none d-print-block">
    <h1 class="page-title">Báo cáo tồn kho</h1>
    <p>Ngày: {{ request('ngay_bao_cao', now()->format('d/m/Y')) }}</p>
    @if(request('kho_id') && $khos->where('kho_id', request('kho_id'))->first())
    <p>Kho: {{ $khos->where('kho_id', request('kho_id'))->first()->ten_kho }}</p>
    @endif
    @if(request('thuoc_id') && $thuocs->where('thuoc_id', request('thuoc_id'))->first())
    <p>Thuốc: {{ $thuocs->where('thuoc_id', request('thuoc_id'))->first()->ten_thuoc }}</p>
    @endif
    @if(request('khach_hang_id') && $khachHangs->where('khach_hang_id', request('khach_hang_id'))->first())
    <p>Khách hàng: {{ $khachHangs->where('khach_hang_id', request('khach_hang_id'))->first()->ho_ten }}</p>
    @endif
</div>

<!-- Phần nội dung báo cáo -->
<div class="card">
    <div class="card-body">
        <div class="table-responsive">
            <table class="table table-hover table-bordered table-striped">
                @php 
                    $loaiBaoCao = request('loai_bao_cao', 'lo');
                    $tongGiaTri = 0;
                    $tongSoLuong = 0;
                @endphp
                
                <!-- Báo cáo theo lô thuốc -->
                @if($loaiBaoCao == 'lo')
                <thead class="table-light">
                    <tr>
                        <th>Mã thuốc</th>
                        <th>Tên mặt hàng</th>
                        <th>Đvt</th>
                        <th>Kho</th>
                        <th>Lô</th>
                        <th class="text-end">Tồn kho</th>
                        <th class="text-end">Giá trị</th>
                        <th class="text-end">Thành tiền</th>
                    </tr>
                </thead>
                <tbody>
                    @php 
                        // Nhóm dữ liệu theo lô
                        $groupedData = $data->groupBy(function($item) {
                            return $item->thuoc_id . '_' . $item->lo_id;
                        });
                    @endphp
                    @forelse($groupedData as $group)
                        @php 
                            $item = $group->first(); // Lấy dữ liệu đầu tiên của mỗi nhóm
                            $tonKho = $item->ton_kho_moi;
                            $giaBan = $item->don_gia ?? 0;
                            $giaTriTon = $tonKho * $giaBan;
                            $tongGiaTri += $giaTriTon;
                            $tongSoLuong += $tonKho;
                        @endphp
                        <tr>
                            <td>{{ $item->ma_thuoc ?? 'N/A' }}</td>
                            <td>{{ $item->ten_thuoc ?? 'Không có dữ liệu' }}</td>
                            <td>{{ $item->don_vi_tinh ?? 'N/A' }}</td>
                            <td>{{ $item->ten_kho ?? 'N/A' }}</td>
                            <td>{{ $item->ma_lo ?? 'N/A' }}</td>
                            <td class="text-end">{{ number_format($tonKho ?? 0, 2, ',', '.') }}</td>
                            <td class="text-end">{{ number_format($giaBan ?? 0, 0, ',', '.') }}</td>
                            <td class="text-end">{{ number_format($giaTriTon ?? 0, 0, ',', '.') }}</td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="8" class="text-center">Không có dữ liệu tồn kho</td>
                        </tr>
                    @endforelse
                </tbody>
                <tfoot>
                    <tr>
                        <th colspan="5" class="text-end">TỔNG CỘNG</th>
                        <th class="text-end">{{ number_format($tongSoLuong ?? 0, 2, ',', '.') }}</th>
                        <th></th>
                        <th class="text-end">{{ number_format($tongGiaTri ?? 0, 0, ',', '.') }}</th>
                    </tr>
                </tfoot>
                
                <!-- Báo cáo theo thuốc -->
                @elseif($loaiBaoCao == 'thuoc')
                <thead class="table-light">
                    <tr>
                        <th>Mã thuốc</th>
                        <th>Tên sản phẩm</th>
                        <th>Đvt</th>
                        <th class="text-end">Tồn kho</th>
                        <th class="text-end">Giá trị TB</th>
                        <th class="text-end">Thành tiền</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($data as $item)
                        @php 
                            $tonKho = $item->ton_kho_moi;
                            $giaBan = $item->don_gia ?? 0;
                            $giaTriTon = $tonKho * $giaBan;
                            $tongGiaTri += $giaTriTon;
                            $tongSoLuong += $tonKho;
                        @endphp
                        <tr>
                            <td>{{ $item->ma_thuoc ?? 'N/A' }}</td>
                            <td>{{ $item->ten_thuoc ?? 'Không có dữ liệu' }}</td>
                            <td>{{ $item->don_vi_tinh ?? 'N/A' }}</td>
                            <td class="text-end">{{ number_format($tonKho ?? 0, 2, ',', '.') }}</td>
                            <td class="text-end">{{ number_format($giaBan ?? 0, 0, ',', '.') }}</td>
                            <td class="text-end">{{ number_format($giaTriTon ?? 0, 0, ',', '.') }}</td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="text-center">Không có dữ liệu tồn kho</td>
                        </tr>
                    @endforelse
                </tbody>
                <tfoot>
                    <tr>
                        <th colspan="3" class="text-end">TỔNG CỘNG</th>
                        <th class="text-end">{{ number_format($tongSoLuong ?? 0, 2, ',', '.') }}</th>
                        <th></th>
                        <th class="text-end">{{ number_format($tongGiaTri ?? 0, 0, ',', '.') }}</th>
                    </tr>
                </tfoot>
                
                <!-- Báo cáo theo kho -->
                @elseif($loaiBaoCao == 'kho')
                <thead class="table-light">
                    <tr>
                        <th>Mã kho</th>
                        <th>Tên kho</th>
                        <th class="text-end">Số loại thuốc</th>
                        <th class="text-end">Tổng SL tồn kho</th>
                        <th class="text-end">Giá trị tồn</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($data as $item)
                        @php 
                            $tonKho = $item->ton_kho_moi;
                            $giaTriTon = $item->gia_tri_ton ?? 0;
                            $tongGiaTri += $giaTriTon;
                            $tongSoLuong += $tonKho;
                        @endphp
                        <tr>
                            <td>{{ $item->ma_kho ?? 'N/A' }}</td>
                            <td>{{ $item->ten_kho ?? 'Không có dữ liệu' }}</td>
                            <td class="text-end">{{ number_format($item->so_loai_thuoc ?? 0, 0, ',', '.') }}</td>
                            <td class="text-end">{{ number_format($tonKho ?? 0, 2, ',', '.') }}</td>
                            <td class="text-end">{{ number_format($giaTriTon ?? 0, 0, ',', '.') }}</td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="5" class="text-center">Không có dữ liệu tồn kho</td>
                        </tr>
                    @endforelse
                </tbody>
                <tfoot>
                    <tr>
                        <th colspan="3" class="text-end">TỔNG CỘNG</th>
                        <th class="text-end">{{ number_format($tongSoLuong ?? 0, 2, ',', '.') }}</th>
                        <th class="text-end">{{ number_format($tongGiaTri ?? 0, 0, ',', '.') }}</th>
                    </tr>
                </tfoot>
                
                <!-- Báo cáo theo khách hàng -->
                @elseif($loaiBaoCao == 'khach_hang')
                <thead class="table-light">
                    <tr>
                        <th>Mã KH</th>
                        <th>Tên khách hàng</th>
                        <th class="text-end">Số đơn hàng</th>
                        <th class="text-end">Tổng SL mua</th>
                        <th class="text-end">Giá trị mua</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($data as $item)
                        @php 
                            $soLuongMua = $item->so_luong_mua ?? 0;
                            $giaTriMua = $item->gia_tri_mua ?? 0;
                            $tongGiaTri += $giaTriMua;
                            $tongSoLuong += $soLuongMua;
                        @endphp
                        <tr>
                            <td>{{ $item->ma_khach_hang ?? 'N/A' }}</td>
                            <td>{{ $item->ho_ten ?? 'Không có dữ liệu' }}</td>
                            <td class="text-end">{{ number_format($item->so_don ?? 0, 0, ',', '.') }}</td>
                            <td class="text-end">{{ number_format($soLuongMua ?? 0, 2, ',', '.') }}</td>
                            <td class="text-end">{{ number_format($giaTriMua ?? 0, 0, ',', '.') }}</td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="5" class="text-center">Không có dữ liệu khách hàng</td>
                        </tr>
                    @endforelse
                </tbody>
                <tfoot>
                    <tr>
                        <th colspan="3" class="text-end">TỔNG CỘNG</th>
                        <th class="text-end">{{ number_format($tongSoLuong ?? 0, 2, ',', '.') }}</th>
                        <th class="text-end">{{ number_format($tongGiaTri ?? 0, 0, ',', '.') }}</th>
                    </tr>
                </tfoot>
                @endif
            </table>
        </div>
    </div>
</div>

<!-- Phần footer cho in ấn -->
<div class="print-footer d-none d-print-block">
    <p>Ngày ...... tháng ...... năm ......</p>
    <p>Người lập</p>
    <p>(Ký, ghi rõ họ tên)</p>
</div>

@endsection

@section('scripts')
<script src="https://code.jquery.com/ui/1.13.2/jquery-ui.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>
<script>
    $(document).ready(function() {
        // Khởi tạo datepicker
        $('.datepicker').datepicker({
            dateFormat: 'dd/mm/yy',
            autoclose: true,
            closeText: 'Đóng',
            prevText: 'Trước',
            nextText: 'Sau',
            currentText: 'Hôm nay',
            monthNames: ['Tháng 1', 'Tháng 2', 'Tháng 3', 'Tháng 4', 'Tháng 5', 'Tháng 6', 'Tháng 7', 'Tháng 8', 'Tháng 9', 'Tháng 10', 'Tháng 11', 'Tháng 12'],
            monthNamesShort: ['Th.1', 'Th.2', 'Th.3', 'Th.4', 'Th.5', 'Th.6', 'Th.7', 'Th.8', 'Th.9', 'Th.10', 'Th.11', 'Th.12'],
            dayNames: ['Chủ Nhật', 'Thứ Hai', 'Thứ Ba', 'Thứ Tư', 'Thứ Năm', 'Thứ Sáu', 'Thứ Bảy'],
            dayNamesShort: ['CN', 'T2', 'T3', 'T4', 'T5', 'T6', 'T7'],
            dayNamesMin: ['CN', 'T2', 'T3', 'T4', 'T5', 'T6', 'T7']
        });
        
        // Khởi tạo select2
        $('.select2').select2({
            theme: 'bootstrap-5'
        });
        
        // Xử lý form lọc
        $('.form-select, #con_ton').change(function() {
            $('#filterForm').submit();
        });
        
        // Xử lý in báo cáo
        $('#btnPrint').click(function() {
            window.print();
        });
        
        $('#resetFilterBtn').click(function() {
            $('select[name="kho_id"]').val('').trigger('change');
            $('select[name="thuoc_id"]').val('').trigger('change');
            $('input[name="ngay_bao_cao"]').val('');
            $('#filterForm').submit();
        });
        document.getElementById('resetFilter').onclick = function() {
            var form = this.closest('form');
            Array.from(form.querySelectorAll('input, select')).forEach(function(el) {
                if (el.type === 'select-one' || el.type === 'text' || el.type === 'date') el.value = '';
            });
            form.submit();
        };
    });
</script>
@endsection
