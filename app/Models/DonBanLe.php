<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class DonBanLe extends Model
{
    protected $table = 'don_ban_le';
    protected $primaryKey = 'don_id';
    public $timestamps = false;
    
    protected $fillable = [
        'ma_don',
        'nguoi_dung_id',
        'khach_hang_id',
        'ngay_ban',
        'trang_thai',
        'tong_tien',
        'vat',
        'tong_cong',
    ];

    /**
     * Get the user who created this đơn
     */
    public function nguoiDung()
    {
        return $this->belongsTo(NguoiDung::class, 'nguoi_dung_id', 'nguoi_dung_id');
    }

    /**
     * Get the khách hàng for this đơn
     */
    public function khachHang()
    {
        return $this->belongsTo(KhachHang::class, 'khach_hang_id', 'khach_hang_id');
    }

    /**
     * Get all chi tiết for this đơn
     */
    public function chiTietDonBanLe()
    {
        return $this->hasMany(ChiTietDonBanLe::class, 'don_id', 'don_id');
    }
}
