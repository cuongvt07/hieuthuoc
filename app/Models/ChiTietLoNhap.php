<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ChiTietLoNhap extends Model
{
    protected $table = 'chi_tiet_lo_nhap';
    protected $primaryKey = 'id';
    public $timestamps = false;
    
    protected $fillable = [
        'phieu_id',
        'don_vi',
        'so_luong',
        'gia_nhap',
        'thue_suat',
        'tien_thue',
        'thanh_tien',
        'han_su_dung',
        'lo_id',
    ];

    /**
     * Get the phiếu nhập that this detail belongs to
     */
    public function phieuNhap()
    {
        return $this->belongsTo(PhieuNhap::class, 'phieu_id', 'phieu_id');
    }

    /**
     * Get the lô thuốc that this detail uses
     */
    public function loThuoc()
    {
        return $this->belongsTo(LoThuoc::class, 'lo_id', 'lo_id');
    }
}
