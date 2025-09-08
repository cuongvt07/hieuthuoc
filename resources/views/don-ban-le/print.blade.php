<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>In đơn bán lẻ - {{ $donBanLe->ma_don }}</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            line-height: 1.6;
            margin: 0;
            padding: 20px;
        }
        .container {
            max-width: 800px;
            margin: 0 auto;
        }
        .header {
            text-align: center;
            margin-bottom: 20px;
            border-bottom: 1px solid #ddd;
            padding-bottom: 10px;
        }
        .shop-name {
            font-size: 24px;
            font-weight: bold;
            margin-bottom: 5px;
        }
        .shop-address, .shop-contact {
            margin: 5px 0;
        }
        .order-title {
            text-align: center;
            font-size: 18px;
            font-weight: bold;
            margin: 20px 0;
        }
        .info-section {
            display: flex;
            justify-content: space-between;
            margin-bottom: 20px;
        }
        .info-block {
            flex: 1;
        }
        .info-label {
            font-weight: bold;
        }
        table {
            width: 100%;
            border-collapse: collapse;
            margin: 20px 0;
        }
        th, td {
            border: 1px solid #ddd;
            padding: 8px;
            text-align: left;
        }
        th {
            background-color: #f2f2f2;
        }
        .text-right {
            text-align: right;
        }
        .total-section {
            margin-top: 20px;
            text-align: right;
        }
        .signatures {
            display: flex;
            justify-content: space-between;
            margin-top: 50px;
        }
        .signature-block {
            flex: 1;
            text-align: center;
        }
        .signature-name {
            margin-top: 50px;
        }
        .footer {
            margin-top: 30px;
            text-align: center;
            font-style: italic;
            border-top: 1px solid #ddd;
            padding-top: 10px;
        }
        @media print {
            .no-print {
                display: none;
            }
            body {
                padding: 0;
            }
            .container {
                width: 100%;
                max-width: none;
            }
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <div class="shop-name">HIỆU THUỐC TÂY</div>
            <div class="shop-address">Địa chỉ: 123 Đường ABC, Quận XYZ, TP. HCM</div>
            <div class="shop-contact">Điện thoại: 0123 456 789 - Email: hieuthuoc@example.com</div>
        </div>

        <div class="order-title">
            PHIẾU BÁN THUỐC
        </div>

        <div class="info-section">
            <div class="info-block">
                <div><span class="info-label">Mã đơn:</span> {{ $donBanLe->ma_don }}</div>
                <div><span class="info-label">Ngày bán:</span> {{ \Carbon\Carbon::parse($donBanLe->ngay_ban)->format('d/m/Y H:i') }}</div>
                <div><span class="info-label">Nhân viên:</span> {{ $donBanLe->nguoiDung->ho_ten }}</div>
            </div>
            <div class="info-block">
                <div><span class="info-label">Khách hàng:</span> {{ $donBanLe->khachHang ? $donBanLe->khachHang->ho_ten : 'Khách lẻ' }}</div>
                <div><span class="info-label">Số điện thoại:</span> {{ $donBanLe->khachHang ? $donBanLe->khachHang->sdt : 'N/A' }}</div>
            </div>
        </div>

        <table>
            <thead>
                <tr>
                    <th>STT</th>
                    <th>Tên thuốc</th>
                    <th>Đơn vị</th>
                    <th>Số lượng</th>
                    <th>Đơn giá</th>
                    <th>Thành tiền</th>
                </tr>
            </thead>
            <tbody>
                @foreach($donBanLe->chiTietDonBanLe as $index => $chiTiet)
                <tr>
                    <td>{{ $index + 1 }}</td>
                    <td>{{ $chiTiet->loThuoc->thuoc->ten_thuoc }}</td>
                    <td>{{ $chiTiet->don_vi }}</td>
                    <td>{{ $chiTiet->so_luong }}</td>
                    <td class="text-right">{{ number_format($chiTiet->gia_ban, 0, ',', '.') }} đ</td>
                    <td class="text-right">{{ number_format($chiTiet->thanh_tien, 0, ',', '.') }} đ</td>
                </tr>
                @endforeach
            </tbody>
            <tfoot>
                <tr>
                    <th colspan="5" class="text-right">Tổng tiền hàng:</th>
                    <td class="text-right">{{ number_format($donBanLe->tong_tien, 0, ',', '.') }} đ</td>
                </tr>
                <tr>
                    <th colspan="5" class="text-right">VAT:</th>
                    <td class="text-right">{{ number_format($donBanLe->vat, 0, ',', '.') }} đ</td>
                </tr>
                <tr>
                    <th colspan="5" class="text-right">Tổng cộng:</th>
                    <td class="text-right">{{ number_format($donBanLe->tong_cong, 0, ',', '.') }} đ</td>
                </tr>
            </tfoot>
        </table>

        <div class="signatures">
            <div class="signature-block">
                <div>Người mua hàng</div>
                <div class="signature-name">(Ký, họ tên)</div>
            </div>
            <div class="signature-block">
                <div>Người bán hàng</div>
                <div class="signature-name">(Ký, họ tên)</div>
            </div>
        </div>

        <div class="footer">
            <p>Cảm ơn Quý khách đã sử dụng dịch vụ của chúng tôi!</p>
        </div>

        <div class="no-print" style="margin-top: 20px; text-align: center;">
            <button onclick="window.print();" style="padding: 8px 16px; background-color: #4CAF50; color: white; border: none; cursor: pointer; border-radius: 4px;">
                In đơn hàng
            </button>
            <button onclick="window.close();" style="padding: 8px 16px; background-color: #f44336; color: white; border: none; cursor: pointer; border-radius: 4px; margin-left: 10px;">
                Đóng
            </button>
        </div>
    </div>
</body>
</html>
