<table class="table table-bordered">
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
        @foreach ($thuocs as $index => $thuoc)
            @php
                $qty = $thuoc->tong_so_luong_quy_doi ?? 0;
                $qtyDisplay = (floor($qty) == $qty) ? number_format($qty, 0, ',', '.') : number_format($qty, 2, ',', '.');
            @endphp
            <tr>
                <td>{{ $index + 1 }}</td>
                <td>{{ $thuoc->ten_thuoc }}</td>
                <td>{{ $thuoc->so_don }}</td>
                <td>{{ $qtyDisplay }} ({{ $thuoc->don_vi_goc }})</td>
                <td>{{ number_format($thuoc->doanh_so, 0, ',', '.') }}</td>
            </tr>
        @endforeach
    </tbody>
</table>

{{ $thuocs->withQueryString()->links('pagination::bootstrap-4') }}
