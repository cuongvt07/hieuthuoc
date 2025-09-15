<!-- Bảng danh sách thuốc theo trạng thái HSD -->
<div class="card">
    <div class="card-body">
        <div class="table-responsive">
            <table class="table table-bordered table-hover">
                <thead>
                    <tr>
                        <th>STT</th>
                        <th>Tên thuốc</th>
                        <th>Tổng tồn kho</th>
                        <th>SL Hết hạn</th>
                        <th>SL Sắp hết hạn</th>
                        <th>SL Còn hạn</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($thuocs as $index => $thuoc)
                        <tr>
                            <td>{{ $thuocs->firstItem() + $index }}</td>
                            <td>{{ $thuoc->ten_thuoc }}</td>
                            <td class="text-end">{{ number_format($thuoc->tong_ton_kho) }}</td>
                            <td class="text-end {{ $thuoc->sl_het_han > 0 ? 'text-danger' : '' }}">
                                {{ number_format($thuoc->sl_het_han) }}
                            </td>
                            <td class="text-end {{ $thuoc->sl_sap_het_han > 0 ? 'text-warning' : '' }}">
                                {{ number_format($thuoc->sl_sap_het_han) }}
                            </td>
                            <td class="text-end {{ $thuoc->sl_con_han > 0 ? 'text-success' : '' }}">
                                {{ number_format($thuoc->sl_con_han) }}
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
        
        {{ $thuocs->withQueryString()->links() }}
    </div>
</div>