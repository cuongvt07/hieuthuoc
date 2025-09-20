<table class="table table-bordered">
    <thead>
        <tr>
            <th>STT</th>
            <th>Tên thuốc</th>
            <th>Số đơn hàng</th>
            <th>Tổng số lượng</th>
            <th>Doanh số</th>
        </tr>
    </thead>
    <tbody>
        @foreach ($thuocs as $index => $thuoc)
            <tr>
                <td>{{ $index + 1 }}</td>
                <td>{{ $thuoc->ten_thuoc }}</td>
                <td>{{ $thuoc->so_don }}</td>
                <td>{{ $thuoc->tong_so_luong }}</td>
                <td>{{ number_format($thuoc->doanh_so, 0, ',', '.') }}</td>
            </tr>
        @endforeach
    </tbody>
</table>

{{ $thuocs->links() }}
