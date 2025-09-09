<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PhieuNhap extends Model
{
    protected $table = 'phieu_nhap';
    protected $primaryKey = 'phieu_id';
    public $timestamps = false;
    
    protected $fillable = [
        'ma_phieu',
        'ncc_id',
        'ngay_nhap',
        'ngay_chung_tu',
        'nguoi_dung_id',
        'tong_tien',
        'vat',
        'tong_cong',
        'ghi_chu',
        'trang_thai',
    ];

    /**
     * Get the user who created this phiếu
     */
    public function nguoiDung()
    {
        return $this->belongsTo(NguoiDung::class, 'nguoi_dung_id', 'nguoi_dung_id');
    }

    /**
     * Get the nhà cung cấp for this phiếu
     */
    public function nhaCungCap()
    {
        return $this->belongsTo(NhaCungCap::class, 'ncc_id', 'ncc_id');
    }

    /**
     * Get all chi tiết for this phiếu
     */
    public function chiTietLoNhaps()
    {
        return $this->hasMany(ChiTietLoNhap::class, 'phieu_id', 'phieu_id');
    }
}
