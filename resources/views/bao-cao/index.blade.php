@extends('layouts.app')

@section('title', 'Báo Cáo - Hệ Thống Quản Lý Hiệu Thuốc')

@section('page-title', 'Báo Cáo')

@section('content')
<div class="row">
    <div class="col-md-6 mb-4">
        <div class="card h-100">
            <div class="card-body">
                <h5 class="card-title">Báo Cáo Tồn Kho</h5>
                <p class="card-text">Xem báo cáo tồn kho theo kho, theo thuốc, theo thời điểm. Bao gồm tình trạng hàng tồn kho và hạn sử dụng.</p>
                <div class="btn-group">
                    <a href="{{ route('bao-cao.ton-kho.index') }}" class="btn btn-primary">
                        <i class="fas fa-box"></i> Xem Tồn Kho
                    </a>
                </div>
            </div>
        </div>
    </div>
    
    <div class="col-md-6 mb-4">
        <div class="card h-100">
            <div class="card-body">
                <h5 class="card-title">Báo Cáo Khách Hàng</h5>
                <p class="card-text">Xem báo cáo lịch sử mua hàng theo khách hàng, thống kê số lượng và giá trị mua.</p>
                <a href="{{ route('bao-cao.khach-hang.index') }}" class="btn btn-primary">
                    <i class="fas fa-users"></i> Xem Báo Cáo
                </a>
            </div>
        </div>
    </div>
</div>
@endsection
