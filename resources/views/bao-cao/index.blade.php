@extends('layouts.app')

@section('title', 'Báo Cáo - Hệ Thống Quản Lý Hiệu Thuốc')

@section('page-title', 'Báo Cáo')

@section('content')
<div class="row">
    <div class="col-md-4 mb-4">
        <div class="card h-100">
            <div class="card-body">
                <h5 class="card-title">Báo Cáo Tồn Kho</h5>
                <p class="card-text">Xem báo cáo tồn kho theo kho, theo thuốc, theo thời điểm.</p>
                <a href="{{ route('bao-cao.ton-kho.index') }}" class="btn btn-primary">Xem Báo Cáo</a>
            </div>
        </div>
    </div>
    
    <div class="col-md-4 mb-4">
        <div class="card h-100">
            <div class="card-body">
                <h5 class="card-title">Báo Cáo Doanh Thu</h5>
                <p class="card-text">Xem báo cáo doanh thu theo thời gian, theo người bán.</p>
                <a href="#" class="btn btn-primary disabled">Đang phát triển</a>
            </div>
        </div>
    </div>
    
    <div class="col-md-4 mb-4">
        <div class="card h-100">
            <div class="card-body">
                <h5 class="card-title">Báo Cáo Thuốc Sắp Hết Hạn</h5>
                <p class="card-text">Xem danh sách các thuốc sắp hết hạn sử dụng.</p>
                <a href="#" class="btn btn-primary disabled">Đang phát triển</a>
            </div>
        </div>
    </div>
</div>
@endsection
