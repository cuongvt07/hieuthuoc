<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ChiTietDonBanLe extends Model
{
    protected $table = 'chi_tiet_don_ban_le';
    protected $primaryKey = 'chi_tiet_id';
    public $timestamps = false;
    
    protected $fillable = [
        'don_id',
        'don_vi',
        'so_luong',
        'gia_ban',
        'thue_suat',
        'tien_thue',
        'thanh_tien',
        'lo_id',
    ];

    /**
     * Get the đơn bán lẻ that this detail belongs to
     */
    public function donBanLe()
    {
        return $this->belongsTo(DonBanLe::class, 'don_id', 'don_id');
    }

    /**
     * Get the lô thuốc that this detail uses
     */
    public function loThuoc()
    {
        return $this->belongsTo(LoThuoc::class, 'lo_id', 'lo_id');
    }
}
