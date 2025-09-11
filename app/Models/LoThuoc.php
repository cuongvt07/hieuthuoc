<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class LoThuoc extends Model
{
    protected $table = 'lo_thuoc';
    protected $primaryKey = 'lo_id';
    public $timestamps = false;
    
    protected $fillable = [
        'ma_lo',
        'thuoc_id',
        'kho_id',
        'notificationsDropdown',
        'tong_so_luong',
        'ton_kho_hien_tai',
        'gia_nhap_tb',
        'so_lo_nha_san_xuat',
        'ngay_san_xuat',
        'ghi_chu',
    ];

    /**
     * Get the thuốc that this lô belongs to
     */
    public function thuoc()
    {
        return $this->belongsTo(Thuoc::class, 'thuoc_id', 'thuoc_id');
    }

    /**
     * Get the kho that this lô belongs to
     */
    public function kho()
    {
        return $this->belongsTo(Kho::class, 'kho_id', 'kho_id');
    }

    /**
     * Get the chi tiết đơn bán lẻ for this lô
     */
    public function chiTietDonBanLe()
    {
        return $this->hasMany(ChiTietDonBanLe::class, 'lo_id', 'lo_id');
    }

    /**
     * Get the chi tiết lô nhập for this lô
     */
    public function chiTietLoNhap()
    {
        return $this->hasMany(ChiTietLoNhap::class, 'lo_id', 'lo_id');
    }
    
    /**
     * Get the lịch sử tồn kho for this lô
     */
    public function lichSuTonKho()
    {
        return $this->hasMany(LichSuTonKho::class, 'lo_id', 'lo_id');
    }
}
